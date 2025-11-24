<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\AttandanceRecap;
use App\Models\Employee;
use App\Models\Offrequest;
use App\Models\Overtime;
use App\Models\Payroll;
use App\Models\SalaryDeduction;
use App\Models\WorkdaySetting;
use App\Models\Event;
use App\Models\Division;
use App\Models\CashAdvance;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Log;
use Carbon\CarbonPeriod;

class PayrollController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:payroll.index')->only(['index', 'approve']);
        $this->middleware('permission:payroll.export')->only(['exportToCsv']);
    }

    public function index(Request $request)
    {
        $search = $request->query('search');
        $month = $request->query('month', now()->format('Y-m'));
        $divisionId = $request->query('division');

        $divisions = Division::all();

        $employees = Employee::with('division', 'attendanceLogs')
            ->where('status', 'Active')
            ->when($search, function ($query) use ($search) {
                $query->whereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%$search%"]);
            })
            ->when($divisionId, function ($query) use ($divisionId) {
                $query->where('division_id', $divisionId);
            })
            ->get();

        $workdaySetting = WorkdaySetting::first();
        if (!$workdaySetting) {
            return redirect()->route('settings.index')->with('error', 'Workday settings not found.');
        }

        $salaryDeduction = SalaryDeduction::first();
        $lateDeduction = $salaryDeduction->late_deduction ?? 0;
        $earlyDeduction = $salaryDeduction->early_deduction ?? 0;

        $payrolls = $employees->map(function ($employee) use ($month, $workdaySetting, $lateDeduction, $earlyDeduction) {
            $existingPayroll = Payroll::where('employee_id', $employee->employee_id)
                ->where('month', $month)
                ->first();
            if ($existingPayroll) {
                Log::info("⚙️ Updating existing payroll for {$employee->first_name} ({$employee->employee_id}) | Month: {$month}");

                // Ambil kasbon aktif
                $activeCashAdvance = CashAdvance::where('employee_id', $employee->employee_id)
                    ->whereIn('status', ['ongoing','completed'] )
                    ->whereRaw("LEFT(start_month, 7) = ?", [$month])
                    ->first();

                $cashAdvance = $activeCashAdvance ? $activeCashAdvance->installment_amount : 0;

                // Tetap lanjut ke perhitungan ulang payroll
                // tapi jangan ubah status existing payroll (tetap Pending)
            } else {
                // Belum ada payroll → tetap 0
                $cashAdvance = 0;
            }

            if ($employee->employee_type === 'Freelance') {
                return $this->calculateFreelancePayroll($employee, $month, $cashAdvance);
            } else {
                return $this->calculatePermanentPayroll($employee, $month, $workdaySetting, $lateDeduction, $earlyDeduction, $cashAdvance);
            }
        })->filter()->values()->all();

        return view('Superadmin.payroll.index', compact('payrolls', 'month', 'search', 'divisions'));
    }

    private function calculateFreelancePayroll($employee, $month, $cashAdvance)
    {
        try {
            $month = Carbon::parse($month)->format('Y-m');
            $hourlyRate = $employee->division->hourly_rate ?? 0;

            $workDays = [];
            if ($employee->division && $employee->division->work_days) {
                if (is_string($employee->division->work_days)) {
                    $decoded = json_decode($employee->division->work_days, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        $workDays = $decoded;
                    } else {
                        $workDays = explode(',', $employee->division->work_days);
                    }
                } elseif (is_array($employee->division->work_days)) {
                    $workDays = $employee->division->work_days;
                }
            }

            $plannedWorkDays = $this->calculateWorkdaysForMonth($workDays, $month, $employee);

            $logs = $employee->attendanceLogs()
                ->whereMonth('check_in', Carbon::parse($month)->month)
                ->whereYear('check_in', Carbon::parse($month)->year)
                ->get();

            $divisionIn  = $employee->division->check_in_time ?? '09:00:00';
            $divisionOut = $employee->division->check_out_time ?? '18:00:00';

            $standardIn  = Carbon::createFromFormat('H:i:s', $divisionIn)->format('H:i:s');
            $toleranceMinutes = 15;

            $totalNormalHours = 0;
            $totalOvertimeHours = 0;
            $lateCount = 0;
            $uniqueWorkDays = [];

            foreach ($logs as $log) {
                if (!$log->check_in || !$log->check_out) continue;

                $checkIn = Carbon::parse($log->check_in);
                $checkOut = Carbon::parse($log->check_out);
                $workDate = $checkIn->toDateString();
                $uniqueWorkDays[$workDate] = true;

                $isLate = $checkIn->gt($checkIn->copy()->setTimeFromTimeString($standardIn)->addMinutes($toleranceMinutes));
                if ($isLate) $lateCount++;

                $workDuration = $checkIn->diffInMinutes($checkOut);
                $workedHours = floor($workDuration / 60);
                $normalHours = min($workedHours, 8);

                if ($isLate) {
                    $normalHours = max(0, $normalHours - 1);
                }

                $totalNormalHours += $normalHours;
                Log::debug("[FREELANCE] {$employee->first_name} {$employee->last_name} | {$workDate} | In: {$checkIn->format('H:i')} | Out: {$checkOut->format('H:i')} | Worked: {$workedHours} jam | Normal: {$normalHours} jam | Late: " . ($isLate ? 'Yes' : 'No'));
            }

            $overtimeData = Overtime::where('employee_id', $employee->employee_id)
                ->where('status', 'approved')
                ->whereMonth('overtime_date', Carbon::parse($month)->month)
                ->whereYear('overtime_date', Carbon::parse($month)->year)
                ->get();

            $totalOvertimeHours = 0;

            foreach ($overtimeData as $ot) {
                $attendance = $employee->attendanceLogs()
                    ->whereDate('check_in', $ot->overtime_date)
                    ->orderByDesc('check_out')
                    ->first();

                if (!$attendance || !$attendance->check_out) {
                    Log::debug("[OT] {$employee->first_name} {$employee->last_name} | {$ot->overtime_date} | Skip (no checkout)");
                    continue;
                }

                $checkOut = Carbon::parse($attendance->check_out);
                $divisionOutTime = Carbon::createFromFormat('H:i:s', $divisionOut);
                $overtimeStart = Carbon::parse($attendance->check_out)
                    ->setTimeFromTimeString($divisionOutTime->format('H:i:s'))
                    ->addMinutes(30);
                // $overtimeStart = $divisionOutTime->copy()->addMinutes(30); // cutoff 18:30

                Log::debug("[OT] {$employee->first_name} {$employee->last_name} | {$ot->overtime_date} | Checkout: {$checkOut->format('Y-m-d H:i')} | Cutoff: {$overtimeStart->format('H:i')}");

                if ($checkOut->lte($overtimeStart)) {
                    Log::debug("[OT] {$employee->first_name} {$employee->last_name} | {$ot->overtime_date} | Checkout {$checkOut->format('H:i')} ≤ 18:30 → 0 jam");
                    continue;
                }

                $overtimeMinutes = $overtimeStart->diffInMinutes($checkOut);
                $actualHours = max(1, ceil($overtimeMinutes / 60));

                // ambil sesuai request employee (misalnya 1 jam atau 2 jam)
                $requestedHours = $ot->duration;

                // // overtime final = minimum dari actual & request
                $hours = min($actualHours, $requestedHours, 2); //max 2 jam per hari

                $totalOvertimeHours += $hours;

                \Log::debug("[OT] {$employee->first_name} {$employee->last_name} | {$ot->overtime_date} | Checkout {$checkOut->format('H:i')} | Overtime {$hours} jam");

                //simpan total overtime ke db
                AttandanceRecap::updateOrCreate(
                    [
                        'employee_id' => $employee->employee_id,
                        'month'       => $month,
                    ],
                    [
                        'total_overtime' => $totalOvertimeHours,
                    ]
                );                
            }

            // ⛔ Pastikan overtime minimal 0
            $overtimePay = max(0, $totalOvertimeHours * $hourlyRate);

            Log::info("[OT-SUMMARY] {$employee->first_name} {$employee->last_name} | Bulan: {$month} | Total OT Hours: {$totalOvertimeHours} | Overtime Pay: {$overtimePay}");


            $workedDays = count($uniqueWorkDays);
            // $totalAbsent = max(0, $plannedWorkDays - $workedDays);
            $totalAbsent = $this->calculateCustomAbsents($employee, $plannedWorkDays, $workedDays);

            $divisionName = strtolower(optional($employee->division)->name);

            //transport
            $existingPayroll = Payroll::where('employee_id', $employee->employee_id)
                ->where('month', $month)
                ->first();

            $employeeTransport = $employee->transport_allowance ?? 0;

            if ($divisionName === 'stock opname' && $existingPayroll) {
                $transportAllowance = $existingPayroll->transport_allowance ?? 0;
            } else {
                $transportAllowance = $employeeTransport;
            }

            $positionalAllowance = $employee->positional_allowance ?? 0;
            // $transportAllowance = $employee->transport_allowance ?? 0;
            $bonusAllowance = $employee->bonus_allowance ?? 0;
            $baseSalary = $totalNormalHours * $hourlyRate;
            $overtimePay = $totalOvertimeHours * $hourlyRate;
            $totalSalary = $baseSalary + $overtimePay + $positionalAllowance + $transportAllowance + $bonusAllowance - $cashAdvance;

            Log::info("Payroll Freelance Summary | {$employee->first_name} {$employee->last_name} | Month: {$month} | Planned WorkDays: {$plannedWorkDays} | Actual WorkDays: {$workedDays} | Absent: {$totalAbsent} | Total Normal Hours: {$totalNormalHours} | Overtime Hours: {$totalOvertimeHours} | Base Salary: {$baseSalary} | Overtime Pay: {$overtimePay} | Cash Advance: ($cashAdvance) | Total Salary: {$totalSalary}");

            // Cek apakah pegawai punya kasbon aktif
            $activeCashAdvance = CashAdvance::where('employee_id', $employee->employee_id)
                ->whereIn('status', ['ongoing','completed'] )
                ->whereRaw("LEFT(start_month, 7) = ?", [$month])
                ->first();

            $cashAdvance = $activeCashAdvance ? $activeCashAdvance->installment_amount : 0;

            return $this->storePayroll(
                $employee,
                $month,
                $totalSalary,
                $baseSalary,
                $overtimePay,
                $workedDays,
                $totalAbsent,
                0,
                $workedDays,
                $lateCount,
                $cashAdvance,
                $positionalAllowance,
                $transportAllowance,
                $bonusAllowance,
                0
            );
        } catch (\Exception $e) {
            Log::error("Error Payroll Freelance | Employee: {$employee->employee_id} | {$employee->first_name} {$employee->last_name} | Month: {$month} | Msg: {$e->getMessage()} | Trace: {$e->getTraceAsString()}");
            return null;
        }
    }

    private function calculatePermanentPayroll($employee, $month, $workdaySetting, $lateDeduction, $earlyDeduction, $cashAdvance)
    {
        try {
            $month = Carbon::parse($month)->format('Y-m');
            $recap = AttandanceRecap::where('employee_id', $employee->employee_id)
                ->where('month', $month)
                ->first();

            $totalDaysWorked   = $recap->total_present ?? 0;
            $totalLateCheckIn  = $recap->total_late ?? 0;
            $totalEarlyCheckOut = $recap->total_early ?? 0;
            $totalOvertimeHours = $recap->total_overtime ?? 0;

            // --- Ambil hari kerja ---
            $divisionWorkDays = [];
            if ($employee->division && $employee->division->work_days) {
                if (is_string($employee->division->work_days)) {
                    $decoded = json_decode($employee->division->work_days, true);
                    $divisionWorkDays = json_last_error() === JSON_ERROR_NONE ? $decoded : explode(',', $employee->division->work_days);
                } elseif (is_array($employee->division->work_days)) {
                    $divisionWorkDays = $employee->division->work_days;
                }
            }
            if (empty($divisionWorkDays)) {
                $divisionWorkDays = $workdaySetting->effective_days ?? [];
            }

            $monthlyWorkdays = $this->calculateWorkdaysForMonth($divisionWorkDays, $month, $employee);
            // $totalAbsent = max(0, $monthlyWorkdays - $totalDaysWorked);
            $totalAbsent = $this->calculateCustomAbsents($employee, $monthlyWorkdays, $totalDaysWorked);

            // --- Hitung daily salary & hourly rate ---
            $dailySalary = $monthlyWorkdays > 0 ? $employee->current_salary / $monthlyWorkdays : 0;

            $divisionIn  = $employee->division->check_in_time ?? '09:00:00';
            $divisionOut = $employee->division->check_out_time ?? '18:00:00';

            $in  = Carbon::createFromFormat('H:i:s', $divisionIn);
            $out = Carbon::createFromFormat('H:i:s', $divisionOut);

            $workDurationInHours = max(1, $out->diffInHours($in)); // ⛔ tidak boleh 0

            // --- Overtime ---
            $baseSalary = $employee->current_salary ?? 0;
            $hourlyRate = $baseSalary > 0 ? ($baseSalary / 173) : 0;

            $overtimeData = Overtime::where('employee_id', $employee->employee_id)
                ->where('status', 'approved')
                ->whereMonth('overtime_date', Carbon::parse($month)->month)
                ->whereYear('overtime_date', Carbon::parse($month)->year)
                ->get();

            $totalOvertimeHours = 0;
            $overtimeSummary = [];

            foreach ($overtimeData as $ot) {
                $attendance = $employee->attendanceLogs()
                    ->whereDate('check_in', $ot->overtime_date)
                    ->orderByDesc('check_out')
                    ->first();

                if (!$attendance || !$attendance->check_out) {
                    Log::debug("[OT] {$employee->first_name} {$employee->last_name} | {$ot->overtime_date} | Skip (no checkout)");
                    continue;
                }

                $checkOut = Carbon::parse($attendance->check_out);
                $divisionOutTime = Carbon::createFromFormat('H:i:s', $divisionOut);
                $overtimeStart = Carbon::parse($attendance->check_out)
                    ->setTimeFromTimeString($divisionOutTime->format('H:i:s'))
                    ->addMinutes(30);

                Log::debug("[OT] {$employee->first_name} {$employee->last_name} | {$ot->overtime_date} | Checkout: {$checkOut->format('Y-m-d H:i')} | Cutoff: {$overtimeStart->format('H:i')}");

                if ($checkOut->lte($overtimeStart)) {
                    Log::debug("[OT] {$employee->first_name} {$employee->last_name} | {$ot->overtime_date} | Checkout {$checkOut->format('H:i')} ≤ 18:30 → 0 jam");
                    continue;
                }

                $overtimeMinutes = $overtimeStart->diffInMinutes($checkOut);
                $actualHours = max(1, ceil($overtimeMinutes / 60));

                // Durasi overtime
                $requestedHours = $ot->duration;

                // maksimal 2 jam / hari
                $hours = min($actualHours, $requestedHours, 2);

                // simpan ke rekap per durasi
                if (!isset($overtimeSummary[$hours])) {
                    $overtimeSummary[$hours] = 0;
                }
                $overtimeSummary[$hours]++;

                $totalOvertimeHours += $hours;

                Log::debug("[OT] {$employee->first_name} {$employee->last_name} | {$ot->overtime_date} | Checkout {$checkOut->format('H:i')} | Overtime {$hours} jam");

                //simpan total overtime ke db
                AttandanceRecap::updateOrCreate(
                    [
                        'employee_id' => $employee->employee_id,
                        'month'       => $month,
                    ],
                    [
                        'total_overtime' => $totalOvertimeHours,
                    ]
                ); 
            }

            $totalOvertimePay = 0;
            foreach ($overtimeSummary as $hours => $count) {
                $pay = $hourlyRate * $hours * $count;
                $totalOvertimePay += $pay;
                Log::info("[OT-CALC] {$employee->first_name} {$employee->last_name} | {$month} | {$hours} jam × {$count} kali = {$pay}");
            }

            $overtimePay = round($totalOvertimePay, 2);

            Log::info("[OT-SUMMARY] {$employee->first_name} {$employee->last_name} | Bulan: {$month} | Total OT Hours: {$totalOvertimeHours} | Overtime Pay: {$overtimePay}");

            // --- Hitung gaji dasar & potongan ---
            $positionalAllowance = $employee->positional_allowance ?? 0;
            // $transportAllowance = $employee->transport_allowance ?? 0;
            $bonusAllowance = $employee->bonus_allowance ?? 0;

            $divisionName = strtolower(optional($employee->division)->name);

            //transport
            $existingPayroll = Payroll::where('employee_id', $employee->employee_id)
                ->where('month', $month)
                ->first();

            $employeeTransport = $employee->transport_allowance ?? 0;

            if ($divisionName === 'stock opname' && $existingPayroll) {
                $transportAllowance = $existingPayroll->transport_allowance ?? 0;
            } else {
                $transportAllowance = $employeeTransport;
            }

            $activeCashAdvance = CashAdvance::where('employee_id', $employee->employee_id)
                ->whereIn('status', ['ongoing','completed'] )
                ->whereRaw("LEFT(start_month, 7) = ?", [$month])
                ->first();

            $cashAdvance = $activeCashAdvance ? $activeCashAdvance->installment_amount : 0;

            $totalDeductions = ($totalLateCheckIn * $lateDeduction) + ($totalEarlyCheckOut * $earlyDeduction);
            $totalSalary = $baseSalary - $totalDeductions + $overtimePay + $positionalAllowance + $transportAllowance + $bonusAllowance - $cashAdvance;

            $finalAllowance = 0;
            $prorateDeduction = 0; // default

            // --- Attendance Allowance ---
            $divisionName = strtolower((string) optional($employee->division)->name);
            if (in_array($divisionName, [
                'supir',
                'kenek',
                'helper',
                'teknisi ac',
                'kasir',
                'admin wholesale',
                'admin retail',
                'operasional retail',
                'head office',
                'stock opname'
            ])) {
                $weeklyData = $this->calculateWeeklyWorkdays($employee, $divisionWorkDays, $month);
                $attendanceAllowance = $employee->attendance_allowance ?? 0;

                if ($attendanceAllowance > 0) {
                    $attendanceDeduction = $this->calculateAttendanceAllowance(
                        $employee,
                        $weeklyData,
                        $attendanceAllowance,
                        $totalAbsent
                    );
                    $finalAllowance = $attendanceAllowance - $attendanceDeduction;
                    $finalAllowanceForDb = max(0, $finalAllowance);

                    if ($finalAllowance <= 0) {
                        // allowance hangus → potong prorate
                        $maxAbsent = in_array($divisionName, ['kasir', 'admin wholesale', 'admin retail', 'operasional retail', 'head office', 'stock opname'])
                            ? 3 : 4;
                        $extraAbsents = max(0, $totalAbsent - $maxAbsent);
                        $prorateDeduction = ($employee->current_salary / 30) * $extraAbsents;

                        $totalSalary -= $prorateDeduction;
                        Log::info("AttendanceAllowance | {$employee->first_name} {$employee->last_name} | {$divisionName} | Absent: {$totalAbsent} | Hangus → Potong prorate: {$prorateDeduction}");
                    } else {
                        $totalSalary += $finalAllowance;
                        Log::info("AttendanceAllowance | {$employee->first_name} {$employee->last_name} | {$divisionName} | Allowance Final: {$finalAllowance}");
                    }

                    $finalAllowance = $finalAllowanceForDb;
                }
            }

            Log::info("Payroll Permanent Summary | {$employee->first_name} {$employee->last_name} | Month: {$month} | Workdays: {$monthlyWorkdays} | Worked: {$totalDaysWorked} | Absent: {$totalAbsent} | Base: {$baseSalary} | OT Hours: {$totalOvertimeHours} | OT Pay: {$overtimePay} | Total Deductions : {$totalDeductions} | Cash Advance: ($cashAdvance) | Total: {$totalSalary}");

            return $this->storePayroll(
                $employee,
                $month,
                $totalSalary,
                $baseSalary,
                $overtimePay,
                $totalDaysWorked,
                $totalAbsent,
                $totalEarlyCheckOut,
                $monthlyWorkdays,
                $totalLateCheckIn,
                $cashAdvance,
                $positionalAllowance,
                $transportAllowance,
                $bonusAllowance,
                $finalAllowance,
                $prorateDeduction,
            );
        } catch (\Exception $e) {
            Log::error("Payroll Permanent ERROR | {$employee->first_name} {$employee->last_name} | {$month} | {$e->getMessage()}");
            return null;
        }
    }

    private function storePayroll(
        $employee,
        $month,
        $totalSalary,
        $baseSalary,
        $overtimePay,
        $totalDaysWorked,
        $totalAbsent,
        $totalEarlyCheckOut,
        $effectiveWorkDays,
        $totalLateCheckIn,
        $cashAdvance,
        $positionalAllowance,
        $transportAllowance,
        $bonusAllowance,
        $attendanceAllowance = 0,
        $absentDeduction = 0
    ) {
        $month = Carbon::parse($month)->format('Y-m');
        $currentSalary = $employee->employee_type === 'Freelance'
            ? 0
            : ($employee->current_salary ?? 0);

        // Ambil payroll lama
        $existing = Payroll::where('employee_id', $employee->employee_id)
            ->where('month', $month)
            ->first();

        $recap = AttandanceRecap::where('employee_id', $employee->employee_id)
            ->where('month', $month)
            ->first();
        
        $totalOvertimeHours = $recap->total_overtime ?? 0;
        
        // Pertahankan status lama kalau sudah Approved
        $status = $existing && strtolower($existing->status) === 'approved'
            ? 'Approved'
            : 'Pending';

        // Update atau buat payroll baru tanpa menimpa status approved
        $data = [
            'employee_name'        => $employee->first_name . ' ' . $employee->last_name,
            'current_salary'       => $currentSalary,
            'base_salary'          => $baseSalary,
            'total_days_worked'    => $totalDaysWorked,
            'total_absent'         => $totalAbsent,
            'total_days_off'       => 0,
            'total_late_check_in'  => $totalLateCheckIn,
            'total_early_check_out' => $totalEarlyCheckOut,
            'effective_work_days'  => $effectiveWorkDays,
            'total_overtime'       => $totalOvertimeHours,
            'overtime_pay'         => $overtimePay,
            'cash_advance'         => $cashAdvance,
            'positional_allowance' => $positionalAllowance,
            'transport_allowance'  => $transportAllowance,
            'bonus_allowance'      => $bonusAllowance,
            'attendance_allowance' => $attendanceAllowance,
            'absent_deduction'     => $absentDeduction,
            'total_salary'         => $totalSalary,
            'status'               => $status,
        ];

        $payroll = Payroll::updateOrCreate(
            [
                'employee_id' => $employee->employee_id,
                'month' => Carbon::parse($month)->format('Y-m')
            ],
            $data
        );

        // Logging tambahan agar bisa kamu lihat di laravel.log
        Log::info("Payroll stored | {$employee->first_name} {$employee->last_name} | Month: {$month} | Status: {$status}");

        return [
            'payroll_id'          => $payroll->payroll_id,
            'id'                  => $employee->employee_id,
            'employee_name'       => $employee->first_name . ' ' . $employee->last_name,
            'current_salary'      => $employee->current_salary ?? 0,
            'base_salary'         => $baseSalary,
            'total_days_worked'   => $totalDaysWorked,
            'total_absent'        => $totalAbsent,
            'total_days_off'      => 0,
            'total_late_check_in' => $totalLateCheckIn,
            'total_early_check_out' => $totalEarlyCheckOut,
            'effective_work_days' => $effectiveWorkDays,
            'total_overtime'       => $totalOvertimeHours,
            'overtime_pay'        => $overtimePay,
            'cash_advance'        => $cashAdvance,
            'positional_allowance' => $positionalAllowance,
            'transport_allowance' => $transportAllowance,
            'bonus_allowance'     => $bonusAllowance,
            'attendance_allowance' => $attendanceAllowance,
            'absent_deduction'    => $absentDeduction,
            'total_salary'        => $totalSalary,
            'status'              => $status,
        ];
    }


    private function calculateCustomAbsents($employee, $plannedWorkDays, $workedDays)
    {
        $divisionName = strtolower((string) optional($employee->division)->name);
        $month = Carbon::parse(now())->format('Y-m');

        //hitung absent kotor
        $rawAbsent = max(0, $plannedWorkDays - $workedDays);

        // ambil semua tanggal di bulan ini
        $period = CarbonPeriod::create(
            Carbon::parse($month)->startOfMonth(),
            Carbon::parse($month)->endOfMonth()
        );

        // Tanggal hadir
        $presentDates = $employee->attendanceLogs()
            ->whereMonth('check_in', Carbon::parse($month)->month)
            ->whereYear('check_in', Carbon::parse($month)->year)
            ->pluck('check_in')
            ->map(fn($d) => Carbon::parse($d)->toDateString())
            ->unique()
            ->toArray();

        // Cari tanggal absen
        $absentDates = [];
        foreach ($period as $date) {
            if (!in_array($date->toDateString(), $presentDates)) {
                $absentDates[] = $date->copy();
            }
        }

        // Hitung toleransi sesuai aturan divisi
        $tolerance = 0;
        if ($divisionName === 'supir') {
            // libur 2x sebulan di hari Minggu
            $sundayAbsents = collect($absentDates)->filter(fn($d) => $d->isSunday())->take(2);
            $tolerance = $sundayAbsents->count();
        }elseif ($divisionName === 'kenek') {
            // libur 1x sebulan di hari Minggu
            $sundayAbsents = collect($absentDates)->filter(fn($d) => $d->isSunday())->take(1);
            $tolerance = $sundayAbsents->count();
        }elseif ($divisionName === 'helper') {
            // libur 1x sebulan di hari Minggu
            $sundayAbsents = collect($absentDates)->filter(fn($d) => $d->isSunday())->take(1);
            $tolerance = $sundayAbsents->count();
        } elseif ($divisionName === 'kasir') {
            // libur 2x sebulan di weekdays (Senin–Jumat)
            $weekdayAbsents = collect($absentDates)
                ->filter(fn($d) => $d->isWeekday()) // weekday = Mon–Fri
                ->take(2);
            $tolerance = $weekdayAbsents->count();
        }elseif ($divisionName === 'teknisi ac') {
            // libur 2x sebulan di hari apa pun (2 hari bebas)
            $tolerance = min(2, count($absentDates));
        }

        // Total absen final = absen kasar - toleransi
        $totalAbsent = max(0, $rawAbsent - $tolerance);

        Log::info("[ABSENT] {$employee->first_name} {$employee->last_name} | Divisi: {$divisionName} | Raw Absent: {$rawAbsent} | Tolerance: {$tolerance} | Final Absent: {$totalAbsent}");

        return $totalAbsent;
    }



    private function calculateWorkdaysForMonth(array $effectiveDays, string $month, $employee = null): int
    {
        [$year, $monthNumber] = explode('-', $month);
        $startDate = Carbon::create($year, $monthNumber, 1)->startOfMonth();
        $endDate   = Carbon::create($year, $monthNumber, 1)->endOfMonth();

        $divisionName = strtolower((string) optional($employee->division)->name);

        //Divisi libur nasional
        $divisionsUsingHoliday = [
            'head office',
            'stock opname',
            'admin wholesale'
        ];

        if (in_array($divisionName, $divisionsUsingHoliday)) {
            // Ambil libur nasional
            $holidayDates = Event::where('category', 'danger')
                ->whereBetween('start_date', [$startDate, $endDate])
                ->get()
                ->flatMap(fn($event) => CarbonPeriod::create($event->start_date, $event->end_date)->toArray())
                ->map(fn($date) => $date->format('Y-m-d'))
                ->unique()
                ->toArray();
        } else {
            $holidayDates = [];
        }

        $period = CarbonPeriod::create($startDate, $endDate);
        $division = strtolower((string) optional($employee->division)->name);

        $workdays = collect($period)->filter(function ($date) use ($effectiveDays, $holidayDates, $employee, $division) {
            $dayName = $date->format('l');
            $dateStr = $date->format('Y-m-d');

            // Log awal
            // \Log::debug("[CHECK-DIVISION] {$employee->first_name} {$employee->last_name} | Division: {$division} | Day: {$dayName} | Date: {$dateStr}");

            // Base workday check
            $isWorkday = in_array($dayName, $effectiveDays) && !in_array($dateStr, $holidayDates);

            if (!$isWorkday && in_array($dateStr, $holidayDates)) {
                Log::debug("[DEBUG] Skip National Holiday: {$dateStr} ({$dayName})");
            }

            if ($isWorkday) {
                Log::debug("[DEBUG] Workday: {$dateStr} ({$dayName})");
            }

            return $isWorkday;
        });

        return $workdays->count();
    }



    private function calculateWeeklyWorkdays($employee, array $effectiveDays, string $month): array
    {
        [$year, $monthNumber] = explode('-', $month);
        $startDate = Carbon::create($year, $monthNumber, 1)->startOfMonth();
        $endDate   = Carbon::create($year, $monthNumber, 1)->endOfMonth();

        $divisionName = strtolower((string) optional($employee->division)->name);

        //Divisi libur nasional
        $divisionsUsingHoliday = [
            'head office',
            'stock opname',
            'admin wholesale'
        ];

        if (in_array($divisionName, $divisionsUsingHoliday)) {
            // Ambil libur nasional
            $holidayDates = Event::where('category', 'danger')
                ->whereBetween('start_date', [$startDate, $endDate])
                ->get()
                ->flatMap(fn($event) => CarbonPeriod::create($event->start_date, $event->end_date)->toArray())
                ->map(fn($date) => $date->format('Y-m-d'))
                ->unique()
                ->toArray();
        } else {
            $holidayDates = [];
        }
        
        // Ambil absensi pegawai
        $attendances = $employee->attendanceLogs()
            ->whereMonth('check_in', $monthNumber)
            ->whereYear('check_in', $year)
            ->get()
            ->groupBy(fn($log) => Carbon::parse($log->check_in)->format('Y-m-d'));

        $weeklyData = [];
        $period = CarbonPeriod::create($startDate, $endDate);

        foreach ($period as $date) {
            $dayName = $date->format('l');
            $weekNum = $date->weekOfMonth;
            $dateStr = $date->format('Y-m-d');

            // hanya cek hari kerja & bukan libur nasional
            if (!in_array($dayName, $effectiveDays)) continue;
            if (in_array($dateStr, $holidayDates)) continue;

            // Apakah hadir hari ini?
            $attended = isset($attendances[$dateStr]);

            $weeklyData[$weekNum][$dayName] = $attended;

            Log::debug("[WeeklyData] {$employee->first_name} {$employee->last_name} | {$dateStr} ({$dayName}) | Week {$weekNum} | Attended: " . ($attended ? 'Yes' : 'No'));
        }

        return $weeklyData;
    }


    private function calculateAttendanceAllowance($employee, $weeklyData, $baseAllowance, $finalAbsent = null)
    {

        $division = strtolower((string) optional($employee->division)->name);

        // hitung total absen semua minggu
        $absencesPerWeek = [];
        foreach ($weeklyData as $weekNum => $days) {
            $absencesPerWeek[(int)$weekNum] = count(array_filter($days, fn($attended) => !$attended));
        }
        $rawAbsents = array_sum($absencesPerWeek);

        $totalAbsents = $finalAbsent ?? $rawAbsents;

        //  Kalau ada finalAbsent, distribusikan ke absencesPerWeek
        if ($finalAbsent !== null) {
            $rawTotal = array_sum($absencesPerWeek);
            if ($rawTotal > 0 && $finalAbsent < $rawTotal) {
                // kurangi dari minggu terakhir yang punya absen
                $reduce = $rawTotal - $finalAbsent;
                foreach (array_reverse(array_keys($absencesPerWeek)) as $week) {
                    if ($reduce <= 0) break;
                    if ($absencesPerWeek[$week] > 0) {
                        $absencesPerWeek[$week] -= 1;
                        $reduce--;
                    }
                }
            }
        }

        //  Kasir & Admin pakai aturan langsung per absen
        if (in_array($division, ['kasir', 'admin wholesale', 'admin retail', 'operasional retail', 'head office', 'stock opname'])) {
            if ($totalAbsents > 3) {
                // allowance hangus total
                Log::info("AttendanceAllowance | {$employee->first_name} {$employee->last_name} | {$division} | Absent {$totalAbsents}x > 3 → allowance hangus total.");
                return $baseAllowance; // deduction penuh
            }

            // tiap absen potong langsung per-absen allowance
            $perAbsentDeduction = $baseAllowance / 3;
            $deduction = $perAbsentDeduction * $totalAbsents;

            Log::info("AttendanceAllowance | {$employee->first_name} {$employee->last_name} | {$division} | Absent {$totalAbsents}x → Deduction {$deduction}");

            return $deduction;
        }

        //  Divisi lain tetap pakai scheme weekly share
        $maxAbsent = 4; // supir/helper/teknisi ac
        if ($totalAbsents >= $maxAbsent) {
            Log::info("AttendanceAllowance | {$employee->first_name} {$employee->last_name} | {$division} | Absent {$totalAbsents}x > {$maxAbsent} → allowance hangus total.");
            return $baseAllowance; // deduction penuh
        }

        // Weekly share scheme
        $scheme = $this->calculateWeeklyShareAllowance($baseAllowance, $absencesPerWeek);
        $shares = $scheme['shares'];
        $allFull = $scheme['allFull'];
        $lostShares = $scheme['lostShares'];
        $lostAllFull = $scheme['lostAllFull'];

        $deduction = array_sum($lostShares);

        if ($lostAllFull) {
            $allFullOriginal = $baseAllowance - (4 * ($baseAllowance / 6));
            $deduction += $allFullOriginal;
        }

        Log::info("AttendanceAllowance | {$employee->first_name} {$employee->last_name} | {$division} | Absent {$totalAbsents}x → deduction {$deduction}");

        return $deduction;
    }


    private function calculateWeeklyShareAllowance(float $baseAllowance, array $absencesPerWeek): array
    {
        // definisi share: 4 minggu + allFull (4*(1/6) + 2/6)
        $weekShare = $baseAllowance / 6;
        $shares = [
            1 => $weekShare,
            2 => $weekShare,
            3 => $weekShare,
            4 => $weekShare,
        ];
        $allFull = $baseAllowance - array_sum($shares); // biasanya 2/6

        $lostShares = [];
        $lostAllFull = false;
        $extraAbsencesOnNonShareWeeks = 0;

        // Proses hanya untuk minggu yang relevan (1..4)
        foreach ($absencesPerWeek as $week => $absenceCount) {
            $absenceCount = max(0, (int)$absenceCount);

            if ($absenceCount >= 1) {
                // minggu ini hangus
                if (isset($shares[$week]) && $shares[$week] > 0) {
                    $lostShares[$week] = $shares[$week];
                    $shares[$week] = 0;
                }
                $lostAllFull = true; // allFull hangus
            }

            if ($absenceCount === 2) {
                // minggu ini + minggu berikutnya hangus
                if (isset($shares[$week]) && $shares[$week] > 0) {
                    $lostShares[$week] = $shares[$week];
                    $shares[$week] = 0;
                }
                $next = $week + 1;
                if (isset($shares[$next]) && $shares[$next] > 0) {
                    $lostShares[$next] = $shares[$next];
                    $shares[$next] = 0;
                }
                $lostAllFull = true;
            }

            if ($absenceCount >= 3) {
                // semua minggu hangus
                foreach (array_keys($shares) as $w) {
                    if ($shares[$w] > 0) {
                        $lostShares[$w] = $shares[$w];
                        $shares[$w] = 0;
                    }
                }
                $lostAllFull = true;
                break;
            }
        }

        // Total allowance setelah potongan mingguan
        $allowanceAfterShares = array_sum($shares) + ($lostAllFull ? 0 : $allFull);

        // Hitung prorate untuk absen di luar minggu share
        $proratePerAbsence = $baseAllowance / 30;
        $prorateDeduction = $extraAbsencesOnNonShareWeeks * $proratePerAbsence;

        $finalAllowance = max(0, $allowanceAfterShares - $prorateDeduction);

        return [
            'shares' => $shares,
            'allFull' => $lostAllFull ? 0 : $allFull,
            'lostShares' => $lostShares,
            'lostAllFull' => $lostAllFull,
            'extraAbsencesOnNonShareWeeks' => $extraAbsencesOnNonShareWeeks,
            'prorateDeduction' => $prorateDeduction,
            'finalAllowance' => $finalAllowance,
        ];
    }

    public function updateCashAdvance(Request $request, $id)
    {
        try {
            $request->validate([
                'cash_advance' => 'required|numeric|min:0',
            ]);

            $payroll = Payroll::findOrFail($id);
            $oldValue = $payroll->cash_advance;

            $payroll->cash_advance = $request->cash_advance;
            $payroll->save();

            Log::info("Cash Advance updated", [
                'payroll_id' => $id,
                'old_value' => $oldValue,
                'new_value' => $payroll->cash_advance,
                'user_id' => auth()->id(), // jika pakai auth
            ]);

            return response()->json([
                'message' => 'Cash advance updated successfully.',
                'cash_advance' => number_format($payroll->cash_advance, 0, ',', '.')
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to update cash advance', [
                'payroll_id' => $id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id(), // optional
            ]);

            return response()->json([
                'message' => 'Terjadi kesalahan saat menyimpan kasbon.',
            ], 500);
        }
    }

    public function updateTransport(Request $request, $id)
    {
        try {
            $request->validate([
                'transport_allowance' => 'required|numeric|min:0',
            ]);

            $payroll = Payroll::findOrFail($id);
            $oldValue = $payroll->transport_allowance;

            $payroll->transport_allowance = $request->transport_allowance;
            $payroll->save();

            Log::info("Transport updated", [
                'payroll_id' => $id,
                'old' => $oldValue,
                'new' => $payroll->transport_allowance,
            ]);

            return response()->json([
                'message' => 'Transport allowance updated successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update transport allowance',
            ], 500);
        }
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Employee;
use App\Models\Division;
use App\Models\Attandance;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\Log;


class AllInOneAttendanceSeeder extends Seeder
{
    public function run()
    {
        date_default_timezone_set('Asia/Jakarta');

        $month = '2025-11';
        [$year, $monthNumber] = explode('-', $month);

        $startDate = Carbon::createFromDate($year, $monthNumber, 1, 'Asia/Jakarta')->startOfMonth();
        $endDate   = Carbon::createFromDate($year, $monthNumber, 1, 'Asia/Jakarta')->endOfMonth();
        $period    = CarbonPeriod::create($startDate, $endDate);

        $divisions = [
            'Admin Wholesale' => [
                'days' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'],
                'check_in' => '09:00',
                'check_out' => '18:00',
            ],
            'Head Office' => [
                'days' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'],
                'check_in' => '09:00',
                'check_out' => '17:00',
            ],
            'Operasional Retail' => [
                'days' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'],
                'check_in' => '09:00',
                'check_out' => '18:00',
            ],
            'Stock Opname' => [
                'days' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'],
                'check_in' => '09:00',
                'check_out' => '18:00',
            ],
            'Kasir' => [
                'days' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'],
                'check_in' => '09:00',
                'check_out' => '18:00',
            ],
            'Supir' => [
                'days' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'],
                'check_in' => '09:00',
                'check_out' => '18:00',
            ],
            'Kenek' => [
                'days' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'],
                'check_in' => '09:00',
                'check_out' => '18:00',
            ],
            'Helper' => [
                'days' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'],
                'check_in' => '09:00',
                'check_out' => '18:00',
            ],
            'Teknisi AC' => [
                'days' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'],
                'check_in' => '09:00',
                'check_out' => '18:00',
            ],
            'Freelance Admin' => [
                'days' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'],
                'check_in' => '09:00',
                'check_out' => '18:00',
            ],
        ];

        foreach ($divisions as $divisionName => $config) {
            $division = Division::where('name', $divisionName)->first();
            if (!$division) continue;

            $employees = Employee::where('division_id', $division->id)->get();
            if ($employees->isEmpty()) continue;

            // --- Global absent randomizer (per divisi, tapi sama untuk semua employee di divisi itu) ---
            $workDates = [];
            foreach ($period as $date) {
                if (in_array($date->format('l'), $config['days'])) {
                    $workDates[] = $date->toDateString();
                }
            }

            // --- Generate attendance untuk setiap employee ---
            foreach ($employees as $employee) {
                $divisionName = strtolower($divisionName);

                // 1️⃣ ambil semua tanggal kerja default dari divisi
                $employeeWorkDates = $workDates;

                // 2️⃣ terapkan aturan toleransi libur agar hasil payroll match
                if ($divisionName === 'supir') {
                    // libur 2x hari Minggu
                    $sundays = collect($employeeWorkDates)
                        ->filter(fn($d) => Carbon::parse($d)->isSunday())
                        ->values();
                    if ($sundays->count() > 2) {
                        $skipSundays = $sundays->random(2);
                        $employeeWorkDates = array_diff($employeeWorkDates, $skipSundays->toArray());
                        Log::debug("[Seeder] {$employee->first_name} {$employee->last_name} | Supir libur Minggu: " . implode(', ', $skipSundays->toArray()));
                    }
                } elseif ($divisionName === 'teknisi ac') {
                    // libur 2x hari Minggu
                    $sundays = collect($employeeWorkDates)
                        ->filter(fn($d) => Carbon::parse($d)->isSunday())
                        ->values();
                    if ($sundays->count() > 2) {
                        $skipSundays = $sundays->random(2);
                        $employeeWorkDates = array_diff($employeeWorkDates, $skipSundays->toArray());
                        Log::debug("[Seeder] {$employee->first_name} {$employee->last_name} | Supir libur Minggu: " . implode(', ', $skipSundays->toArray()));
                    }
                } elseif ($divisionName === 'helper') {
                    // libur 1x hari Minggu
                    $sundays = collect($employeeWorkDates)
                        ->filter(fn($d) => Carbon::parse($d)->isSunday())
                        ->values();
                    if ($sundays->count() > 0) {
                        $skipSunday = $sundays->random(1);
                        $employeeWorkDates = array_diff($employeeWorkDates, $skipSunday->toArray());
                        Log::debug("[Seeder] {$employee->first_name} {$employee->last_name} | Helper libur Minggu: " . implode(', ', $skipSunday->toArray()));
                    }
                } elseif ($divisionName === 'kenek') {
                    // libur 1x hari Minggu
                    $sundays = collect($employeeWorkDates)
                        ->filter(fn($d) => Carbon::parse($d)->isSunday())
                        ->values();
                    if ($sundays->count() > 0) {
                        $skipSunday = $sundays->random(1);
                        $employeeWorkDates = array_diff($employeeWorkDates, $skipSunday->toArray());
                        Log::debug("[Seeder] {$employee->first_name} {$employee->last_name} | Helper libur Minggu: " . implode(', ', $skipSunday->toArray()));
                    }
                } elseif ($divisionName === 'kasir') {
                    // libur 2x weekdays
                    $weekdays = collect($employeeWorkDates)
                        ->filter(fn($d) => Carbon::parse($d)->isWeekday())
                        ->values();
                    if ($weekdays->count() > 2) {
                        $skipWeekdays = $weekdays->random(2);
                        $employeeWorkDates = array_diff($employeeWorkDates, $skipWeekdays->toArray());
                        Log::debug("[Seeder] {$employee->first_name} {$employee->last_name} | Kasir libur weekday: " . implode(', ', $skipWeekdays->toArray()));
                    }
                }

                // 3️⃣ random absen tambahan (simulasi karyawan bolos)
                $absentCount = rand(0, 5);
                $absentDays = [];
                if ($absentCount > 0 && count($employeeWorkDates) >= $absentCount) {
                    $absentKeys = (array) array_rand($employeeWorkDates, $absentCount);
                    $absentDays = array_map(fn($key) => $employeeWorkDates[$key], $absentKeys);
                }

                // 4️⃣ buat attendance (skip hari libur & absen)
                foreach ($employeeWorkDates as $dateStr) {
                    if (in_array($dateStr, $absentDays)) continue;

                    $date = Carbon::parse($dateStr, 'Asia/Jakarta');

                    // Jam masuk 09:00–09:30
                    $checkInMinute = rand(0, 30);
                    $checkIn = $date->copy()->setTime(9, $checkInMinute);

                    // Jam keluar + tambahan 0–120 menit
                    $baseOut = Carbon::parse("{$dateStr} {$config['check_out']}", 'Asia/Jakarta');
                    $checkOut = $baseOut->copy()->addMinutes(rand(0, 120));

                    $checkInStatus = ($checkInMinute > 15) ? 'LATE' : 'IN';
                    $checkOutStatus = ($checkOut->lt($baseOut)) ? 'EARLY' : 'OUT';

                    if (!Attandance::where('employee_id', $employee->employee_id)
                        ->whereYear('check_in', $year)
                        ->whereMonth('check_in', $monthNumber)
                        ->whereDate('check_in', $dateStr)
                        ->exists()) {
                        Attandance::create([
                            'employee_id'      => $employee->employee_id,
                            'check_in'         => $checkIn->format('Y-m-d H:i:s'),
                            'check_out'        => $checkOut->format('Y-m-d H:i:s'),
                            'check_in_status'  => $checkInStatus,
                            'check_out_status' => $checkOutStatus,
                            'image'            => null,
                        ]);
                    }
                }
            }
        }
    }
}

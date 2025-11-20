<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CashAdvance;
use App\Models\Employee;
use App\Models\Payroll;
use Carbon\Carbon;

class CashAdvanceController extends Controller
{
    public function index()
    {
        $kasbon = CashAdvance::with('employee')->latest()
            ->orderBy('employee_id')
            ->orderBy('start_month')
            ->get();

        return view('Superadmin.kasbon.index', compact('kasbon'));
    }

    public function kasbonList() {
        $kasbonlists = CashAdvance::whereHas('employee', function ($query) {
            $query->where('employee_id', auth()->user()->employee->employee_id); 
        })
        ->orderBy('start_month', 'desc')
        ->get();

        return view('Superadmin.kasbon.list', compact('kasbonlists'));
    }

    public function create($id = null)
    {   
        $employee_id = $id;
        
        $employees = Employee::with('division')
            ->where('status', 'Active')
            ->orderBy('first_name', 'asc')
            ->get();
        return view('Superadmin.kasbon.create', compact('employees', 'employee_id'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,employee_id',
            'total_amount' => 'required|numeric|min:1',
            'installments' => 'required|integer|in:1,2,3',
            'start_month' => 'required|date',
        ]);

        $installmentAmount = $request->total_amount / $request->installments;
        $startMonth = Carbon::parse($request->start_month);

        // Loop untuk generate setiap bulan cicilan
        for ($i = 0; $i < $request->installments; $i++) {
            $month = $startMonth->copy()->addMonths($i)->format('Y-m');

            // Cek apakah kasbon untuk bulan ini sudah ada
            $exists = CashAdvance::where('employee_id', $request->employee_id)
                ->whereRaw("LEFT(start_month, 7) = ?", [$month])
                ->exists();

            if ($exists) {
                return redirect()->back()->with('error', 'You already have a cash advance for that month.');
            }
            
            CashAdvance::create([
                'employee_id' => $request->employee_id,
                'total_amount' => $request->total_amount,
                'installments' => $request->installments,
                'installment_amount' => $installmentAmount,
                'remaining_installments' => 1, // tiap bulan 1x cicilan
                'start_month' => $month,
                'status' => 'ongoing', // tandai default jadwal
            ]);
        }

        return redirect()->route('kasbon.index')->with('success', 'Cash advance schedule created successfully.');
    }
    
    public function edit($id)
    {
        $kasbon = CashAdvance::findOrFail($id);
        $employees = Employee::where('status', 'Active')->orderBy('first_name')->get();

        $employee = $kasbon->employee; // employee pemilik kasbon
        $ongoingKasbon = CashAdvance::where('employee_id', $employee->employee_id)
            ->where('status', 'ongoing')
            ->orderBy('start_month')
            ->get();
    

        return view('Superadmin.kasbon.update', compact('kasbon', 'employees', 'employee', 'ongoingKasbon'));
    }

    public function update(Request $request, $id)
    {
        $kasbon = CashAdvance::findOrFail($id);

        $request->validate([
            'employee_id' => 'required|exists:employees,employee_id',
            'total_amount' => 'required|numeric|min:1',
            'installments' => 'required|integer|min:1',
            'start_month' => 'required|date',
            'status' => 'nullable|string|in:pending,completed',
        ]);

        $installmentAmount = $request->total_amount / $request->installments;
        $startMonth = Carbon::parse($request->start_month);
    
        CashAdvance::where('employee_id', $request->employee_id)
            ->where('start_month', '>=', $startMonth->format('Y-m'))
            ->delete();
    
        for ($i = 0; $i < $request->installments; $i++) {
            $month = $startMonth->copy()->addMonths($i)->format('Y-m');
    
            CashAdvance::create([
                'employee_id' => $request->employee_id,
                'total_amount' => $request->total_amount,
                'installments' => $request->installments,
                'installment_amount' => $installmentAmount,
                'remaining_installments' => 1,
                'start_month' => $month,
                'status' => 'ongoing',
            ]);
        }
    
        return redirect()->route('kasbon.index')->with('success', 'Cash advance updated successfully.');
    }

    public function finishKasbon($employee_id)
    {
        $ongoingKasbon = CashAdvance::where('employee_id', $employee_id)
            ->where('status', 'ongoing')
            ->get();

        if ($ongoingKasbon->isEmpty()) {
            return response()->json(['error' => 'Tidak ada kasbon ongoing.']);
        }

        foreach ($ongoingKasbon as $kasbon) {
            $kasbon->status = 'completed';
            $kasbon->save();
        }

        return response()->json(['success' => 'Semua kasbon berhasil diselesaikan.']);
    }

    public function showFinishPage($employee_id)
    {
        $employee = Employee::findOrFail($employee_id);

        // Ambil semua kasbon ongoing milik employee ini
        $kasbon = CashAdvance::where('employee_id', $employee_id)
            ->where('status', 'ongoing')
            ->orderBy('start_month')
            ->get();

        $kasbon = null;
        $employees = Employee::where('status', 'Active')->orderBy('first_name')->get();
        
        return view('Superadmin.kasbon.update', compact('kasbon', 'employees', 'employee', 'ongoingKasbon'));
        // return view('Superadmin.kasbon.finish', compact('employee', 'kasbon'));
    }

    public function cancelKasbon($id)
    {
        $kasbon = CashAdvance::findOrFail($id);
        $kasbon->status = 'cancelled';
        $kasbon->save();

        return back()->with('success', 'Kasbon berhasil dibatalkan');
    }

    public function approve($id)
    {
        $kasbon = CashAdvance::findOrFail($id);
        $kasbon->status = 'completed';
        $kasbon->save();

        return back()->with('success', 'Kasbon berhasil di selesaikan');
    }


}

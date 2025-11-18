<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Mail\OvertimeStatusMail;
use App\Models\Employee;
use App\Models\Overtime;
use App\Models\Role;
use App\Models\User;
use App\Notifications\OvertimeRequestNotification;
use Google\Service\AnalyticsData\OrderBy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Encoders\JpegEncoder;



class OvertimeController extends Controller
{
    public function __construct()
    {
        // Menggunakan middleware untuk validasi hak akses
        $this->middleware('permission:overtime.create')->only(['index', 'create', 'store', 'searchEmployees']);
        $this->middleware('permission:overtime.approvals')->only(['approvals', 'reject', 'approve']);
    }

    public function index()
    {
        // Mengambil data overtime untuk employee yang sedang login
        $overtimes = Overtime::whereHas('employee', function ($query) {
            $query->where('employee_id', auth()->user()->employee->employee_id); // Sesuaikan dengan 'employee_id' atau 'id' sesuai data Anda
        })
        ->orderBy('overtime_date', 'desc')
        ->get();

        return view('Superadmin.overtime.index', compact('overtimes'));
    }


    public function create()
    {   
        $employee = auth()->user()->employee;

        //Cek apakah employee mempunyai division
        if (!$employee || !$employee->division) {
            return redirect()->route('overtime.index')
                ->with('error', 'You have to set your division first.');
        }

        // Cegah jika employee tidak diizinkan lembur
        if ($employee->division->has_overtime == 0) {
            return redirect()->route('overtime.index')
                ->with('error', 'You are not allowed to request overtime.');
        }

        // Ambil daftar manager (user dengan role 'manager')
        $managers = User::role('manager')->get();

        // dd($managers);
        return view('Superadmin.overtime.create', compact('managers'));
    }

    public function store(Request $request)
    {   
        $employee = auth()->user()->employee;

        if ($employee->division->has_overtime == 0) {
            return redirect()->route('overtime.index')
                ->with('error', 'You are not allowed to request overtime.');
        }

        // Validasi data overtime
        $request->validate([
            'overtime_date' => [
                'required',
                'date',
                function ($attribute, $value, $fail) {
                    // Validasi apakah sudah ada overtime pada tanggal yang sama
                    $existingOvertime = Overtime::whereHas('employee', function ($query) {
                        $query->where('employee_id', Auth::user()->employee->employee_id);  // Menggunakan employee_id dari user yang sedang login
                    })
                        ->where('overtime_date', $value)  // Mencari overtime yang sudah ada pada tanggal yang sama
                        ->whereIn('status', ['pending', 'approved'])  // Mengecek jika statusnya pending atau approved
                        ->exists();

                    if ($existingOvertime) {
                        session()->flash('error', 'You already have an overtime request for this date.');
                        $fail('You already have an overtime request for this date.');
                    }
                }
            ],
            'duration' => 'required|integer|in:1,2',
            'notes' => 'required|string|max:255',
            'manager_id' => 'required|exists:users,user_id',
            'attachment' => 'required|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        try {
            // Simpan file jika ada
            if ($request->hasFile('attachment')) {
                $image = $request->file('attachment');
                $filename = time() . '.jpg'; // paksa ke JPG

                // Gunakan Intervention v3
                $manager = new ImageManager(new Driver());
    
                // Read image
                $img = $manager->read($image->getRealPath());
    
                // Resize & maintain ratio
                $img = $img->scaleDown(width: 1200, height: 1200);
    
                // Path simpan
                $savePath = public_path('storage/overtime_attachments/' . $filename);

                $encoder = new JpegEncoder(quality: 50);
    
                // Simpan dengan kompresi quality: 75
                $img->encode($encoder)->save($savePath);
    
                $attachmentPath = 'overtime_attachments/' . $filename;
            }

            // Membuat data overtime
            $overtime = Overtime::create([
                'employee_id' => auth()->user()->employee->employee_id,  // Mendapatkan employee_id dari user yang sedang login
                'overtime_date' => $request->overtime_date,
                'duration' => $request->duration,
                'notes' => $request->notes,
                'attachment' => $attachmentPath,
                'manager_id' => $request->manager_id,  // Mendapatkan manager_id dari form'
                'status' => 'pending',  // Status default untuk overtime yang diajukan
            ]);

            // Kirim notifikasi ke manager
            $managers = User::role('manager')->get();
            foreach ($managers as $manager) {
                $manager->notify(new OvertimeRequestNotification($overtime));
            }

            Log::info('Overtime created successfully', [
                'employee_id' => $request->employee_id,
                'overtime_id' => $request->id,
                'attachment'  => $attachmentPath,
            ]);

            // Redirect ke halaman overtime index dengan pesan sukses
            return redirect()->route('overtime.index')->with('success', 'Overtime request has been successfully added!');
        } catch (\Exception $e) {
            Log::error('Failed to create overtime', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id(),
            ]);

            return redirect()->back()->with('error', 'Failed to submit overtime request. Please try again.');
        }
    }


    public function approvals()
    {
        $managerId = auth()->id(); // ID manager yang login

        // Pengajuan overtime yang pending untuk manager ini
        $pendingOvertimes = Overtime::where('status', 'pending')
            ->where('manager_id', $managerId)
            ->get();

        // Riwayat overtime (approved/rejected) untuk manager ini
        $historyOvertimes = Overtime::whereIn('status', ['approved', 'rejected'])
            ->where('manager_id', $managerId)
            ->get();

        return view('Superadmin.overtime.approve', compact('pendingOvertimes', 'historyOvertimes'));
    }

    public function approve($id)
    {
        $overtime = Overtime::where('id', $id)
            ->where('manager_id', auth()->id()) // Hanya manager terkait
            ->firstOrFail();

        $overtime->update(['status' => 'approved']);

        Mail::to($overtime->employee->user->email)->send(new OvertimeStatusMail($overtime, 'approved'));

        return redirect()->route('overtime.approvals')
            ->with('success', 'Overtime request successfully approved!');
    }

    public function reject($id)
    {
        $overtime = Overtime::where('id', $id)
            ->where('manager_id', auth()->id()) // Hanya manager terkait
            ->firstOrFail();

        $overtime->update(['status' => 'rejected']);

        Mail::to($overtime->employee->user->email)->send(new OvertimeStatusMail($overtime, 'rejected'));

        return redirect()->route('overtime.approvals')
            ->with('success', 'Overtime request successfully rejected!');
    }
}

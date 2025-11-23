<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payroll extends Model
{
    use HasFactory;

    protected $primaryKey = 'payroll_id';

    protected $fillable = [
        'employee_id',
        'employee_name',
        'month',
        'total_days_worked',
        'total_absent',
        'total_days_off',
        'total_late_check_in',
        'total_early_check_out',
        'effective_work_days',
        'current_salary',
        'base_salary',
        'overtime_pay',
        'attendance_allowance',
        'transport_allowance',
        'absent_deduction',
        'cash_advance',
        'total_salary',
        'status',

    ];

    public function getValidationStatusLabelAttribute()
    {
        switch ($this->validation_status) {
            case 'approved':
                return 'green'; 
            case 'declined':
                return 'red'; 
            default:
                return 'yellow'; 
        }
    }


    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'employee_id');
    }
    public function attandanceRecap()
    {
        return $this->hasOne(AttandanceRecap::class, 'employee_id', 'employee_id');
    }
    // Relasi ke model WorkdaySetting
    public function workdaySetting()
    {
        return $this->hasOne(WorkdaySetting::class, 'id'); // Sesuaikan foreign key jika berbeda
    }

    // Relasi ke model Overtime
    public function overtime()
    {
        return $this->hasMany(Overtime::class, 'employee_id', 'employee_id');
    }

    // Relasi ke model Offrequest
    public function offRequests()
    {
        return $this->hasMany(Offrequest::class, 'employee_id', 'employee_id');
    }

    public function getWasRecentlyApprovedAttribute()
    {
        return strtolower($this->status) === 'approved';
    }

}

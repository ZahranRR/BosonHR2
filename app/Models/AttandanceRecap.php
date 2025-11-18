<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AttandanceRecap extends Model
{
    use HasFactory;
    protected $table = 'attandance_recaps';

    protected $fillable = ['employee_id', 'month', 'total_present', 'total_late', 'total_early', 'total_absent', 'total_overtime'];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'employee_id');
    }
}

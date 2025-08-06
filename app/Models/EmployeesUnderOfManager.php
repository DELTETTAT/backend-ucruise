<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeesUnderOfManager extends Model
{
    use HasFactory;

    protected $fillable = [
        'manager_id',
        'employee_id',
    ];

    public function manager()
    {
        return $this->belongsTo(SubUser::class, 'manager_id');
    }

    public function employee()
    {
        return $this->belongsTo(SubUser::class, 'employee_id');
    }


    public function scopeOfManager($query, $managerId)
    {
        return $query->where('manager_id', $managerId);
    }
    public function scopeOfEmployee($query, $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }
    public function scopeOfManagerAndEmployee($query, $managerId, $employeeId)
    {
        return $query->where('manager_id', $managerId)
                     ->where('employee_id', $employeeId);
    }
}

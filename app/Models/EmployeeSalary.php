<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeSalary extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'basic',
        'hra',
        'medical',
        'conveyance',
        'bonus',
        'gross_salary',
        'professional_tax',
        'epf_employee',
        'epf_employer',
        'esi_employee',
        'esi_employer',
        'take_home',
        'total_package_salary',
        "increment_from_date",
        "increment_to_date",
        "is_active",
        "reason",
    ];

    // Optional: If employee is in sub_users table
    public function employee()
    {
        return $this->belongsTo(SubUser::class, 'employee_id');
    }

    // If using users table instead:
    // public function employee()
    // {
    //     return $this->belongsTo(User::class, 'employee_id');
    // }
}

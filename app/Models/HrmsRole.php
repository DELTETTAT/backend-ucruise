<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\HrmsNewRole;

class HrmsRole extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'specific_role_id'
    ];

    public function hrms_permissions(){
        return $this->belongsToMany(HrmsPermission::class, 'hrms_role_permissions','role_id', 'permission_id')->withPivot(['can_view', 'can_edit', 'can_access']);
    }

    public function viewrole(){
        return $this->hasOne(HrmsNewRole::class, 'id','specific_role_id');
    }

    public function employees()
    {
        return $this->belongsToMany(SubUser::class, 'hrms_employee_roles', 'role_id', 'employee_id');
    }


}

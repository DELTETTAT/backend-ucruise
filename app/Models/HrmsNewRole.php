<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HrmsNewRole extends Model
{
    use HasFactory;

    protected $fillable = [
         'name',
         'status'
    ];


    public function hrms_permissions(){
        return $this->belongsToMany(HrmsPermission::class, 'hrms_role_permissions','role_id', 'permission_id')->withPivot(['can_view', 'can_edit', 'can_access']);
    }

    // public function employeeRole(){
    //     return $this->hasMany(HrmsRole::class, '');
    // }

    public function employeeRoles(){
        return $this->belongsTo(HrmsRole::class, 'id','specific_role_id');
    }
}

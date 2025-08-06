<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Role;
use App\Models\HrmsPermission;

class HrmsRolePermission extends Model
{
    use HasFactory;

    protected $fillable = [
        'role_id',
        'permission_id',
        'can_view',
        'can_edit',
        'can_access',
        'status'
    ];


    public function hrms_role(){
        return $this->belongsTo(Role::class);
    }

    public function hrms_permission(){
        return $this->belongsTo(HrmsPermission::class);
    }

}

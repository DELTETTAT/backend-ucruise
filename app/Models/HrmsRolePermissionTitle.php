<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HrmsRolePermissionTitle extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'status',
    ];

    public function roleandPermissions(){
        return $this->hasMany(HrmsRoleAndPermission::class,'hrms_role_permission_title_id','id')->select('id','title','hrms_role_permission_title_id','permissions','status');
    }

}

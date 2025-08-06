<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HrmsRoleAndPermission extends Model
{
    use HasFactory;

    protected $table = 'hrms_role_and_permissions';

    protected $fillable = [
        'title', 
        'hrms_role_permission_title_id', 
        'permissions',
        'status',
    ];

    protected $casts = [
        'permissions' => 'array',
    ];

    public function roleTitle(){
        return $this->belongsTo(HrmsRolePermissionTitle::class, 'hrms_role_permission_title_id', 'id')->select('id','name','status');
    }
}

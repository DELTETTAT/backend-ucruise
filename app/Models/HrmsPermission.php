<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Role;

class HrmsPermission extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'status'
    ];

    public function hrms_roles(){
        return $this->belongsToMany(Role::class, 'hrms_role_permissions')->withPivot('can_view', 'can_edit'. 'can_access');
    }
}

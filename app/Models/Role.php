<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\HrmsPermission;

class Role extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
    ];

    public function permissions()
    {
        return $this->belongsToMany(Permission::class);
    }


    // public function hrms_permissions(){
    //     return $this->belongsToMany(HrmsPermission::class, 'hrms_role_permissions','role_id', 'permission_id')->withPivot(['can_view', 'can_edit', 'can_access']);
    // }




}

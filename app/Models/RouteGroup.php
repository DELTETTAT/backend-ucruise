<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RouteGroup extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = ['group_name', 'driver_id'];

    public function driver()
    {
        return $this->belongsTo(SubUser::class, 'driver_id')->select('id','first_name','middle_name', 'last_name', 'email','mobile','phone','profile_image');
    }

    public function users()
    {
        return $this->hasMany(RouteGroupUser::class, 'route_group_id');
    }

    public function routeSchedule()
    {
        return $this->belongsTo(RouteGroupSchedule::class, 'id', 'route_group_id');
    }


}


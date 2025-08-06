<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RouteGroupUser extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $fillable = ['route_group_id', 'user_id','latitude','longitude'];


    public function user()
    {
        return $this->belongsTo(User::class, 'user_id')->select('id','first_name','middle_name', 'last_name', 'email','mobile','phone','company_name','address','latitude','longitude','office_distance','profile_image');
    }

    public function routeGroup()
    {
        return $this->belongsTo(RouteGroup::class, 'route_group_id');
    }


}

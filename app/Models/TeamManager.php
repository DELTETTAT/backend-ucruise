<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\SubUser;
use App\Models\HrmsTeam;

class TeamManager extends Model
{
    use HasFactory;

    public  const PAGINATE = 12;

    protected $fillable = [
        'name',
        'description',
        'location',
        'latitude',
        'longitude',
    ];


    public function employees()
    {
        return $this->belongsToMany(SubUser::class, 'employee_team_managers', 'team_manager_id', 'employee_id');
    }

    public function hrmsTeam()
    {
        return $this->hasOne(HrmsTeam::class, 'team_manager_id');
    }

    public function hrmsEmployees()
    {
        $team = $this->hrmsTeam;
        if (!$team || empty($team->members)) {
            return collect();
        }

        $memberIds = is_array($team->members) ? $team->members : json_decode($team->members, true);
        return SubUser::whereIn('id', $memberIds)->get();
    }

    public function teams()
    {
        return $this->hasMany(HrmsTeam::class, 'team_manager_id', 'id');
    }
}

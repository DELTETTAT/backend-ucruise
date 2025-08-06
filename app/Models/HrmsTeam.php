<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\TeamManager;
use App\Models\SubUser;

class HrmsTeam extends Model
{
    use HasFactory;
    protected $table = 'hrms_teams';

    protected $fillable = [
        'team_name',
        'description',
        'members',
        'team_leader',
        'team_manager_id'
    ];
    public const PAGINATE = 10;


    public function teamMembers()
    {
        return $this->hasMany(HrmsTeamMember::class, 'hrms_team_id', 'id');
    }

    public function teamLeader()
    {
        return $this->belongsTo(SubUser::class, 'team_leader', 'id');
    }

    public function teamManager(){
        return $this->hasOne(EmployeeTeamManager::class, 'team_manager_id', 'team_manager_id');
    }

    public function teamTask(){

        return $this->hasMany(HrmsTask::class, 'hrms_team_id','id');
    }
      public function manager()
    {
        return $this->belongsTo(TeamManager::class, 'team_manager_id');
    }



}

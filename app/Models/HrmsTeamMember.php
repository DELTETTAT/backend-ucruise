<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HrmsTeamMember extends Model
{
    use HasFactory;

    protected $table = 'hrms_team_members';

    protected $fillable = [
        'member_id',
        'hrms_team_id'
    ];
    public const PAGINATE = 10;


    public function team()
    {
        return $this->belongsTo(HrmsTeam::class, 'hrms_team_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'member_id', 'id') ;          ///->select('id', 'first_name');
    }

}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\MinProject;

class HrmsProject extends Model
{
    use HasFactory;

    protected  $fillable = [
        'id',
        'project_title',
        'description',
        'admin_id',
        'status',
        'priority',
        'start_date',
        'end_date',
        'assignees'
    ];

    protected $casts = [
        'assignees' => 'array'
    ];

    public function admin(){
        return $this->belongsTo(User::class, 'admin_id', 'id')->select('id','unique_id','first_name','last_name','email');
    }

    public function subProject(){
        return $this->hasMany(SubProject::class, 'project_id');
    }

    public function getPriorityAttribute($value){
        $data = [0 => 'Low', 1 => 'Medium', 2 => 'High'];
        return $data[$value] ?? 'Unknown';
    }

    public function getStatusAttribute($value){
        $priorityMap = [0 => 'Not Started', 1 => 'In Progress',2 => 'Completed'];
        return $priorityMap[$value] ?? 'Unknown';
    }

    
}


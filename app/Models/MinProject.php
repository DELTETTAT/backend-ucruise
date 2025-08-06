<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MinProject extends Model
{
    use HasFactory;

    protected  $fillable = [
        'name',
        'description',
        'sub_project_id',
        'start_date',
        'end_date',
        'assigned_id',
        'priority',
        'status',
        'completion',
        'created_by'
    ];

    public function projectTask(){
        return $this->hasMany(ProjectTask::class, 'min_project_id');
    }

    public function getPriorityAttribute($value){
        $data = [0 => 'Low', 1 => 'Medium', 2 => 'High'];
        return $data[$value] ?? 'Unknown';
    }

    public function getStatusAttribute($value){
        $priorityMap = [0 => 'Not Started', 1 => 'In Progress',2 => 'Done', 3 => 'On Hold', 4 => 'Close'];
        return $priorityMap[$value] ?? 'Unknown';
    }



}

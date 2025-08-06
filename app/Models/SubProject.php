<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubProject extends Model
{
    use HasFactory;

    protected  $fillable = [
        'name',
        'description',
        //'project_id',
        'start_date',
        'end_date',
        'priority',
        'status',
        'completion',
        'assigned_id',
        'created_by'
    ];

    public function minProjects(){
       return $this->hasMany(MinProject::class);
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

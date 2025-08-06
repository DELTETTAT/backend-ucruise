<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectTask extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'min_project_id',
        'start_date',
        'end_date',
        'created_by',
        'assigned_id',
        'priority',
        'status',
    ];

    public function getPriorityAttribute($value){
        $priorityMap = [0 => 'Low', 1 => 'Medium',2 => 'High'];
        return $priorityMap[$value] ?? 'Unknown';
    }

    public function getStatusAttribute($value){
        $priorityMap = [0 => 'Not Started', 1 => 'In Progress',2 => 'Done', 3 => 'On Hold', 4 => 'Close'];
        return $priorityMap[$value] ?? 'Unknown';
    }
}

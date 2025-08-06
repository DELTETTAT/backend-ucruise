<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UpdateEmployeeHistory extends Model
{
    use HasFactory;
    protected $fillable = [
        'employee_id',
        'date',
        'time',
        'updated_by',
        'notes',
        'changed',
    ];

    public function employee()
    {
        return $this->belongsTo(SubUser::class, 'employee_id', 'id');
    }

    public function changedBy()
    {
        return $this->belongsTo(User::class, 'updated_by', 'id');
    }
}

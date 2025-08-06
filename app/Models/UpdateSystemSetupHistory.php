<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UpdateSystemSetupHistory extends Model
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
        return $this->belongsTo(User::class, 'employee_id', 'id');
    }

    /**
     * The user who made the update
     */
    public function changedBy()
    {
        return $this->belongsTo(User::class, 'updated_by', 'id');
    }
}

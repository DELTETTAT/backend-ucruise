<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HrmsEmployeeRole extends Model
{
    use HasFactory;

    protected $fillable = [
             'role_id',
             'employee_id'
    ];


   // public function
}

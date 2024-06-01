<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Goal extends Model
{
    use HasFactory;


    protected $fillable = [
        'user_id',
        'employee_id',
        'supervisor_id',
        'employer_id',
        'type',

        'title',
        'period',
        'reminder',
        'reminder_date',
        'metric'
    ];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'employer_id',
        'name',
        'email',
        'phone',
        'birth_date',
        'job_code_id'
    ];

    // DOB, Job title, job code, employee number and a lot more;
}

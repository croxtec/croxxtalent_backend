<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployerJobcode extends Model
{
    use HasFactory;

    protected $fillable = [
        'employer_id',
        'job_code',
        'job_title',
        'description',
        "manager1_id",
        "manager2_id",
    ];

}

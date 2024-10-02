<?php

namespace App\Models\Competency;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DepartmentMapping extends Model
{
    use HasFactory;

    protected $fillable = [
        'employer_id',
        'department_id',
        'competency',
        'competency_role',
        'description'
    ];

}

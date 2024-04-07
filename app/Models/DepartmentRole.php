<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DepartmentRole extends Model
{
    use HasFactory;

    protected $fillable = [
        'employer_id',
        'department_id',
        'name',
        'description'
    ];

}

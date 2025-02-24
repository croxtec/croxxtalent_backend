<?php

namespace App\Models\Competency;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DepartmentSetup extends Model
{
    use HasFactory;

    protected $fillable = [
        'department',
        'competency',
        'level',
        'target_score',
        'department_role',
        'description'
    ];




}

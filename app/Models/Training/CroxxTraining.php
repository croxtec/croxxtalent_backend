<?php

namespace App\Models\Training;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CroxxTraining extends Model
{
    use HasFactory;


    protected $fillable = [
        'user_id',
        'employer_id',
        'code',
        'type',   //company, training,competency
        'title',
        'experience_level',
        'objective',

        'department_id',
        'career_id',
        'assessment_level',
        'assessment_id',
        'is_published'
    ];

}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssesmentScoreSheet extends Model
{
    use HasFactory;

    protected $fillable = [
        'assessment_id',
        'supervisor_id',
        'employee_id',
        'talent_id',
        'assessment_question_id',

        'score',
        'comment',
        'attachment'
    ];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssesmentScoreSheet extends Model
{
    use HasFactory;

    protected $fillable = [
        'supervisor_id',
        'assessment_id',
        'employee_id',
        'assesment_question_id',

        'score',
        'comment',
        'attachment'
    ];
}

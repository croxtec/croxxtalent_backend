<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EvaluationQuestionBank extends Model
{
    use HasFactory;

    protected $fillable = [
        'industry_id',
        'level',
        'competency_name',
        'question',
        'option1',
        'option2',
        'option3',
        'option4',
        'answer',
    ];

}

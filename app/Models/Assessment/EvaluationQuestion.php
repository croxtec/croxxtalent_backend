<?php

namespace App\Models\Assessment;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EvaluationQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'assessment_id',
        'type',
        'question',
        'option1',
        'option2',
        'option3',
        'option4',
        'answer'
    ];
}

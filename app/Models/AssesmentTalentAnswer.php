<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class AssesmentTalentAnswer extends Model
{
    use HasFactory;

    protected $fillable = [
        'talent_id',
        'employee_id',
        'assessment_id',
        'assessment_question_id',
        'evaluation_result',

        'comment',
        'option',
        'options',
        'upload',
        'document'
    ];

    protected function options(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => json_decode($value, true),
            set: fn ($value) => json_encode($value),
        );
    }

}

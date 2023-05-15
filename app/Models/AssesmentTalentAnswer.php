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
        'assesment_id',
        'assesment_question_id',

        'comment',
        'period',
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

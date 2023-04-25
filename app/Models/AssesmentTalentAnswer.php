<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        'upload',
        'document'
    ];
}

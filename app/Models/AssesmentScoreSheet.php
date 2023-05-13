<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssesmentScoreSheet extends Model
{
    use HasFactory;

    protected $fillable = [
        'manager_id',
        'assesment_id',
        'talent_id',
        'assesment_question_id',

        'comment',
        'score',
        'attachment'
    ];
}

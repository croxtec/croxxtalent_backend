<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssesmentQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'assesment_id',
        'type',
        'question',
        'description',
        'option1',
        'option2',
        'option3',
        'option4'
    ];
}

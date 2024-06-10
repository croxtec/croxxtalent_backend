<?php

namespace App\Models\Assessment;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompetencyQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'assessment_id',
        'type',
        'question',
        'description',
        'files'
    ];
}

<?php

namespace App\Models\Assessment;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TalentAssessmentSummary extends Model
{
    use HasFactory;

    protected $fillable = [
          'assessment_id',
          'talent_id',
          'time_taken',
          'graded_score'
    ];

    protected $hidden = [
        'updated_at',
    ];
}

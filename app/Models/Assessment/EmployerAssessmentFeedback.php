<?php

namespace App\Models\Assessment;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployerAssessmentFeedback extends Model
{
    use HasFactory;

    protected $fillable = [
          'assessment_id',
          'employee_id',
          'employer_user_id',
          'supervisor_id',
          'time_taken',
          'graded_score'
    ];
}

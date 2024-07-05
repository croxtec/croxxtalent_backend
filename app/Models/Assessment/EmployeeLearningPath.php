<?php

namespace App\Models\Assessment;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeLearningPath extends Model
{
    use HasFactory;

    protected $fillable = [
        'employer_user_id',
        'employee_id',
        'assessment_feedback_id',
        'training_id'
    ];
}

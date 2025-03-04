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

    public function employee(){
        return $this->belongsTo('App\Models\Employee', 'employee_id', 'id')
                    ->with('department','department_role')
                    ->select(['id','name', 'job_code_id', 'department_role_id', 'photo_url', 'code']);
    }

    public function assessment_feedback(){
        return $this->belongsTo('App\Models\Assessment\EmployerAssessmentFeedback', 'assessment_feedback_id', 'id');
    }

    public function training(){
        return $this->belongsTo('App\Models\Training\CroxxTraining', 'training_id', 'id');
    }


}

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

    protected $hidden = [
        'updated_at',
    ];



    public function employee(){
        return $this->belongsTo('App\Models\Employee', 'employee_id', 'id')
                    ->with('department','department_role')
                    ->select(['id','name', 'job_code_id', 'department_role_id']);
    }

    public function supervisor(){
        return $this->belongsTo('App\Models\Employee', 'supervisor_id', 'id')
                    ->with('department','department_role')
                    ->select(['id','name', 'job_code_id', 'department_role_id']);
    }


}

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

    protected $appends = ['estimated_time'];

    public function employee(){
        return $this->belongsTo('App\Models\Employee', 'employee_id', 'id')
                    ->with('department','department_role')
                    ->select(['id','name', 'job_code_id', 'department_role_id', 'photo_url', 'code']);
    }

    public function supervisor(){
        return $this->belongsTo('App\Models\Employee', 'supervisor_id', 'id')
                    ->with('department','department_role')
                    ->select(['id','name', 'job_code_id', 'department_role_id', 'photo_url', 'code']);
    }

    public function assessment()
    {
        return $this->belongsTo('App\Models\Assessment\CroxxAssessment', 'assessment_id', 'id')
                    ->select(['id','name','type','description','code']);
    }

    public function getEstimatedTimeAttribute()
    {
        if (is_numeric($this->time_taken)) {
            $timetaken = intval($this->time_taken);
            $minutes = floor($timetaken / 60);
            $seconds = $timetaken % 60;

            // Return the formatted time
            return sprintf('%d minutes %d seconds', $minutes, $seconds);
        }

        // If not numeric, return null or a default value
        return null;
    }

}

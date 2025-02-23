<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Employee extends Model
{
    use HasFactory,  Notifiable;


    protected $fillable = [
        'user_id',
        'employer_id',
        'name',
        'email',
        'phone',
        'birth_date', 'hired_date',
        'job_code_id',
        'department_role_id',
        'photo_url',
        //
        'performance',
        'level','code',
        'gender',
        'work_type',
        'language',
        'location'
    ];

    // DOB, Job title, job code, employee number and a lot more;

    protected $appends = [
        'status_info'
    ];

    private const STATUS_LABELS = [
        0 => 'Inactive',
        1 => 'Active',
        2 => 'On Leave',
        3 => 'Suspended',
        4 => 'Terminated',
        5 => 'Resigned',
        6 => 'Retired',
        7 => 'Probation',
        8 => 'Contract Expired',
        9 => 'Account Deactivated',
        10 => 'Transferred',
    ];

    public function getStatusInfoAttribute(){
        return self::STATUS_LABELS[$this->status] ?? 'Unknown';
    }

    public function performanceRecords()
    {
        return $this->morphMany(PerformanceRecord::class, 'recordable');
    }

    public function getMonthlyPerformance($year, $month)
    {
        return $this->performanceRecords()
            ->where('year', $year)
            ->where('month', $month)
            ->first();
    }

    public function department(){
        return $this->belongsTo('App\Models\EmployerJobcode', 'job_code_id', 'id')
                    ->select(['id','job_code', 'job_title']);
    }

    public function department_role(){
        return $this->belongsTo('App\Models\DepartmentRole', 'department_role_id', 'id')
                    ->select(['id','name']);
    }

    public function employer(){
        return $this->belongsTo('App\Models\User', 'employer_id', 'id')
                    ->select(['id','photo', 'first_name','last_name','company_name']);
    }

    public function talent(){
        return $this->belongsTo('App\Models\User', 'user_id', 'id')
                    ->select(['id','email','first_name','last_name','photo']);
    }

    public function supervisor(){
        return $this->belongsTo('App\Models\Supervisor', 'supervisor_id', 'id');
    }

    public function verifications()
    {
        return $this->morphMany('App\Models\Verification', 'verifiable');
    }

    public function completedAssessment(){
        return $this->hasMany('App\Models\Assessment\EmployerAssessmentFeedback', 'employee_id', 'id');
    }

    public function learningPaths(){
        return $this->hasMany('App\Models\Assessment\EmployeeLearningPath', 'employee_id', 'id');
    }

    public function goalsCompleted(){
        return $this->hasMany('App\Models\Goal', 'employee_id', 'id');
    }

    public function feedbackSent(){
        return $this->hasMany('App\Models\Assessment\EmployerAssessmentFeedback', 'supervisor_id', 'id');
    }

    public function taskAssigned(){
        return $this->hasMany('App\Models\Goal', 'supervisor_id', 'id');
    }


    // public function routeNotificationForMail()
    // {
    //     return $this->email; // Assuming the employee model has an email attribute
    // }
}

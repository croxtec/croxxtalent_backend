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
        'birth_date',
        'job_code_id',
        'department_role_id',
        //
        'code', 'gender', 'work_type', 'language'
    ];

    // DOB, Job title, job code, employee number and a lot more;

    protected $appends = [

    ];

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

    // public function routeNotificationForMail()
    // {
    //     return $this->email; // Assuming the employee model has an email attribute
    // }
}

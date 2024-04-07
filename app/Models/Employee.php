<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'employer_id',
        'name',
        'email',
        'phone',
        'birth_date',
        'job_code_id',
        'department_role_id'
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
                    ->select(['id','photo', 'company_name']);
    }

    public function talent(){
        return $this->belongsTo('App\Models\User', 'user_id', 'id')
                    ->select(['id','first_name','last_name','photo']);
    }

    public function verifications()
    {
        return $this->morphMany('App\Models\Verification', 'verifiable');
    }
}

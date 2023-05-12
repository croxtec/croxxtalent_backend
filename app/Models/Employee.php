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
        'job_code_id'
    ];

    // DOB, Job title, job code, employee number and a lot more;

    protected $appends = [

    ];

    public function job_code(){
        return $this->belongsTo('App\Models\EmployerJobcode', 'job_code_id', 'id')
                    ->select(['id','job_code', 'job_title']);
    }

    public function employer(){
        return $this->belongsTo('App\Models\User', 'employer_id', 'id')
                    ->select(['id','first_name','last_name','photo']);
    }

    public function talent(){
        return $this->belongsTo('App\Models\User', 'user_id', 'id')
                    ->select(['id','first_name','last_name','photo']);
    }
}

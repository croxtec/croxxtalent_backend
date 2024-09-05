<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supervisor extends Model
{
    use HasFactory;

    protected $fillable = [
        'employer_id',
        'supervisor_id',
        'type',
        'department_id',
        'department_role_id'
    ];

    public function employee(){
        return $this->belongsTo('App\Models\Employee', 'supervisor_id', 'id')
        ->select(['id','name', 'email', 'job_code_id', 'department_role_id', 'photo_url', 'code']);
    }


    public function department(){
        return $this->belongsTo('App\Models\EmployerJobcode', 'department_id', 'id')
                    ->select(['id','job_code', 'job_title']);
    }

    public function department_role(){
        return $this->belongsTo('App\Models\DepartmentRole', 'department_role_id', 'id')
                    ->select(['id','name']);
    }
}


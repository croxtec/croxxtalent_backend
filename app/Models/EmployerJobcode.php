<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class EmployerJobcode extends Model
{
    use HasFactory;

    protected $fillable = [
        'employer_id',
        'job_code',
        'job_title',
        'description'
    ];

    // protected $appends = [
    //     'department_managers',
    //     'total_employee',
    //     'total_assessment'
    // ];

    protected function managers(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => json_decode($value, true),
            set: fn ($value) => json_encode($value),
        );
    }

    public function employee(){
        return $this->hasMany('App\Models\Employee', 'job_code_id', 'id');
    }

    public function assessment(){
        return $this->hasMany('App\Models\Assesment', 'job_code_id', 'id');
    }

    public function getDepartmentManagersAttribute(){
        $department = Employee::where('id', $this->managers)->get();
        return $department;
    }

    public function getTotalEmployeeAttribute(){
        return $this->employee->count();
    }

    public function getTotalAssessmentAttribute(){
        return $this->assessment->count();
    }

}

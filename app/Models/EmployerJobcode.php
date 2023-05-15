<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployerJobcode extends Model
{
    use HasFactory;

    protected $fillable = [
        'employer_id',
        'job_code',
        'job_title',
        'description',
        'managers'
    ];

    protected $appends = [
        'department_managers'
    ];

    protected function managers(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => json_decode($value, true),
            set: fn ($value) => json_encode($value),
        );
    }

    public function getDepartmentManagersAttribute(){
        $department = Employee::where('id', $this->managers)->get();
        return $department;
    }

    public function firstManager(){
        return $this->belongsTo('App\Models\User', 'manager1_id', 'id');
    }

    public function secondManager(){
        return $this->belongsTo('App\Models\User', 'manager2_id', 'id');
    }
}

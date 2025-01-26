<?php

namespace App\Models\Project;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssignedEmployee extends Model
{
    use HasFactory;

    protected $table = 'goal_assigned_employees';

    protected $fillable = [
        'goal_id', 'employee_id', 'assigned_by', 'assigned_at'
    ];

    public function employee(){
        return $this->belongsTo('App\Models\Employee', 'employee_id', 'id')
                     ->select(['id','name', 'job_code_id', 'department_role_id', 'photo_url', 'code']);
    }
}

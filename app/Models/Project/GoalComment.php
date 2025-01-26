<?php

namespace App\Models\Project;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GoalComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'goal_id',
        'employee_id',
        'comment',
        'attachment'
    ];

    public function employee(){
        return $this->belongsTo('App\Models\Employee', 'employee_id', 'id')
                     ->select(['id','name', 'job_code_id', 'department_role_id', 'photo_url', 'code']);
    }

}

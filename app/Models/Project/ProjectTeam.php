<?php

namespace App\Models\Project;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProjectTeam extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'employer_user_id',
        'project_id',
        'employee_id',
        'is_team_lead'
    ];

    public function employee(){
        return $this->belongsTo('App\Models\Employee', 'employee_id', 'id')
        ->select(['id','name', 'job_code_id', 'department_role_id', 'photo_url', 'code']);
    }
}

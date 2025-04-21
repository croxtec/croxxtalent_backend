<?php

namespace App\Models\Project;

use App\Models\Employee;
use App\Models\EmployerJobcode;
use App\Models\User;
use App\Models\Project\Milestone;
use App\Models\Project\ProjectGoal;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use HasFactory, SoftDeletes;


    protected $fillable = [
        'employer_user_id',
        'department_id',
        'title',
        'code',
        'description',
        'start_date',
        'end_date',
        'project_type',
        'priority_level',
        'category',
        // 'currency_code',
        'budget',
        'resource_allocation',
    ];

    public function employer()
    {
        return $this->belongsTo(User::class, 'employer_user_id');
    }

    public function supervisor()
    {
        return $this->belongsTo(Employee::class, 'supervisor_id');
    }

    public function department()
    {
        return $this->belongsTo(EmployerJobcode::class, 'department_id');
    }

    public function milestones()
    {
        return $this->hasMany(Milestone::class);
    }

    public function goals()
    {
        return $this->hasMany(ProjectGoal::class);
    }

    public function tasks()
    {
        return $this->hasMany(ProjectGoal::class);
    }

    public function team()
    {
        return $this->hasMany(ProjectTeam::class);
    }

       /**
     * Get all project team leads
     */
    public function teamLeads()
    {
        return $this->hasMany(ProjectTeam::class)
            ->where('is_team_lead', true)
            ->with('employee');
    }

    /**
     * Get all project team members (non-leads)
     */
    public function teamMembers()
    {
        return $this->hasMany(ProjectTeam::class)
            ->where('is_team_lead', false)
            ->with('employee');
    }

    /**
     * Get all project team members including leads
     */
    public function projectTeam()
    {
        return $this->hasMany(ProjectTeam::class)
            ->with('employee');
    }

    /**
     * Get organized team structure with leads and members
     */
    public function getTeamStructure()
    {
        return [
            'leads' => $this->teamLeads()->get()->map(function ($team) {
                return [
                    'id' => $team->employee->id,
                    'name' => $team->employee->name,
                    'job_code' => $team->employee->job_code_id,
                    'department_role' => $team->employee->department_role_id,
                    'photo_url' => $team->employee->photo_url,
                    'code' => $team->employee->code,
                    'department' => $team->employee->department,
                ];
            }),
            'members' => $this->teamMembers()->get()->map(function ($team) {
                return [
                    'id' => $team->employee->id,
                    'name' => $team->employee->name,
                    'job_code' => $team->employee->job_code_id,
                    'department_role' => $team->employee->department_role_id,
                    'photo_url' => $team->employee->photo_url,
                    'code' => $team->employee->code,
                    'department' => $team->employee->department,
                ];
            })
        ];
    }



    public function totalTasks()
    {
        return $this->goals()->count();
    }

    public function completedTasks()
    {
        return $this->goals()
            ->where('status', 'completed')
            ->count();
    }

    public function getTaskStatistics()
    {
        $total = $this->totalTasks();
        $completed = $this->completedTasks();

        return [
            'total_tasks' => $total,
            'completed_tasks' => $completed,
            'completion_percentage' => $total > 0 ? round(($completed / $total) * 100, 2) : 0
        ];
    }

}

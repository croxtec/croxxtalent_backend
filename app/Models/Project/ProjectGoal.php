<?php

namespace App\Models\Project;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProjectGoal extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'project_id',
        'milestone_id',
        'employer_user_id',
        'code',

        'title',
        'metric',
        'end_date', //due_date
        'priority_level',
        'status',
        'rating',
    ];

    public function milestone(){
        return $this->belongsTo(Milestone::class);
    }

    public function assigned(){
        return $this->hasMany(AssignedEmployee::class, 'goal_id', 'id');
    }

    public function competencies(){
        // return $this->hasMany(GoalCompetency::class, 'goal_id', 'id');
        return $this->belongsToMany('App\Models\Competency\DepartmentMapping', 'goal_competencies', 'goal_id', 'competency_id');
    }

    public function comments(){
        return $this->hasMany(GoalComment::class, 'goal_id', 'id');
    }

    public function activities(){
        return $this->hasMany(GoalActivity::class, 'goal_id', 'id');
    }

}

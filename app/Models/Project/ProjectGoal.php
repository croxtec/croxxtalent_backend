<?php

namespace App\Models\Project;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectGoal extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'milestone_id',
        'employer_user_id',
        // 'code',

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

}

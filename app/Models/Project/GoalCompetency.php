<?php

namespace App\Models\Project;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GoalCompetency extends Model
{
    use HasFactory;

    protected $fillable = [
        'goal_id',
        'competency_id',
        // 'rating',
        // 'comment'
    ];

}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PerformanceRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'recordable_id',    // Employee ID or Department ID
        'recordable_type',  // 'App\Models\Employee' or 'App\Models\Department'
        'year',
        'month',

        // Assessment scores
        'assessment_score',
        'assessment_completion_rate',

        // Peer review scores
        'peer_review_score',
        'peer_review_completion_rate',

        // Goals achievement
        'goals_completion_rate',
        'goals_achieved_count',
        'goals_total_count',

        // Project performance
        'project_completion_rate',
        'project_on_time_rate',
        'tasks_completed_count',
        'tasks_total_count',

        // Competency scores
        'competency_score',
        'kpi_achievement_rate',

        // Overall scores
        'overall_score',

        // Optional context
        'notes'
    ];

    protected $casts = [
        'assessment_score' => 'float',
        'assessment_completion_rate' => 'float',
        'peer_review_score' => 'float',
        'peer_review_completion_rate' => 'float',
        'goals_completion_rate' => 'float',
        'project_completion_rate' => 'float',
        'project_on_time_rate' => 'float',
        'competency_score' => 'float',
        'kpi_achievement_rate' => 'float',
        'overall_score' => 'float'
    ];

    public function recordable()
    {
        return $this->morphTo();
    }
}

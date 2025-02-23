<?php

namespace App\Models\Assessment;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PeerReview extends Model
{
    use HasFactory;

    protected $fillable = [
        'assessment_id',
        'employee_id',      // Employee being reviewed
        'reviewer_id',      // Employee doing the review
        'status',          // pending, completed
        'due_date',
        'completed_at',
        'reminder_sent_at'
    ];

    protected $casts = [
        'due_date' => 'datetime',
        'completed_at' => 'datetime',
        'reminder_sent_at' => 'datetime'
    ];

    public function assessment()
    {
        return $this->belongsTo(CroxxAssessment::class, 'assessment_id');
    }

    public function employee()
    {
        return $this->belongsTo('App\Models\Employee', 'employee_id')
            ->select(['id', 'name', 'job_code_id', 'department_role_id', 'photo_url', 'code']);
    }

    public function reviewer()
    {
        return $this->belongsTo('App\Models\Employee', 'reviewer_id')
            ->select(['id', 'name', 'job_code_id', 'department_role_id', 'photo_url', 'code']);
    }

    public function feedback()
    {
        return $this->hasMany(PeerReviewFeedback::class);
    }
}

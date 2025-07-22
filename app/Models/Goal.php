<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Goal extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'employee_id',
        'supervisor_id',
        'employer_id',
        'parent_id',
        'type', // career,supervisor,company

        'title',
        'period',
        'reminder',
        'reminder_date',
        'metric',
        'status', // ['pending', 'employee_submitted', 'supervisor_review', 'done', 'missed', 'rejected']
        'employee_status', // ['done', 'missed'] - Employee's self-assessment
        'supervisor_status', // ['done', 'missed'] - Supervisor's final decision
        'employee_comment', // Employee's comment on completion
        'supervisor_comment', // Supervisor's feedback
        'employee_submitted_at', // When employee submitted their assessment
        'supervisor_reviewed_at', // When supervisor made final decision
        'score'
    ];

      protected $casts = [
        'employee_submitted_at' => 'datetime',
        'supervisor_reviewed_at' => 'datetime',
        'period' => 'datetime',
        'reminder_date' => 'datetime',
    ];

    // Add relationships
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function supervisor()
    {
        return $this->belongsTo(Employee::class, 'supervisor_id');
    }

    // Scopes for different status queries
    public function scopePendingEmployeeSubmission($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopePendingSupervisorReview($query)
    {
        return $query->where('status', 'employee_submitted');
    }

    public function scopeCompleted($query)
    {
        return $query->whereIn('status', ['done', 'missed']);
    }


}

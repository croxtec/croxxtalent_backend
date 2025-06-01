<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Builder;

class Employee extends Model
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'code',
        'user_id',
        'employer_id',
        'name',
        'email',
        'phone',
        'job_code_id',
        'department_role_id',
        'photo_url',
        'performance',
        'level',
        'gender',
        'work_type',
        'language',
        'location',
        'birth_date',
        'hired_date',
        'status'
    ];

    protected $appends = [
        'status_info'
    ];

    protected $casts = [
        'birth_date' => 'date',
        'hired_date' => 'date',
        'performance' => 'integer'
    ];

    private const STATUS_LABELS = [
        0 => 'Pending',
        1 => 'Active',
        2 => 'On Leave',
        3 => 'Suspended',
        4 => 'Terminated',
        5 => 'Resigned',
        6 => 'Retired',
        7 => 'Probation',
        8 => 'Contract Expired',
        9 => 'Deactivated',
        10 => 'Transferred',
    ];

    public function getStatusInfoAttribute()
    {
        return self::STATUS_LABELS[$this->status] ?? 'Unknown';
    }

    // Optimized relationships with proper select statements
    public function department()
    {
        return $this->belongsTo('App\Models\EmployerJobcode', 'job_code_id', 'id');
    }

    public function department_role()
    {
        return $this->belongsTo('App\Models\DepartmentRole', 'department_role_id', 'id')
                    ->select(['id', 'name']);
    }

    public function employer()
    {
        return $this->belongsTo('App\Models\User', 'employer_id', 'id')
                    ->select(['id', 'photo', 'first_name', 'last_name', 'company_name']);
    }

    public function talent()
    {
        return $this->belongsTo('App\Models\User', 'user_id', 'id')
                    ->select(['id', 'email', 'first_name', 'last_name', 'photo']);
    }

    public function supervisor()
    {
        return $this->belongsTo('App\Models\Supervisor', 'supervisor_id', 'id');
    }

    // Performance related relationships
    public function performanceRecords()
    {
        return $this->morphMany(PerformanceRecord::class, 'recordable');
    }

    public function completedAssessment()
    {
        return $this->hasMany('App\Models\Assessment\EmployerAssessmentFeedback', 'employee_id', 'id');
    }

    public function learningPaths()
    {
        return $this->hasMany('App\Models\Assessment\EmployeeLearningPath', 'employee_id', 'id');
    }

    public function goalsCompleted()
    {
        return $this->hasMany('App\Models\Goal', 'employee_id', 'id');
    }

    public function projectTeam()
    {
        return $this->hasMany('App\Models\Project\ProjectTeam', 'employee_id', 'id');
    }

    public function feedbackSent()
    {
        return $this->hasMany('App\Models\Assessment\EmployerAssessmentFeedback', 'supervisor_id', 'id');
    }

    public function taskAssigned()
    {
        return $this->hasMany('App\Models\Goal', 'supervisor_id', 'id');
    }

    public function verifications()
    {
        return $this->morphMany('App\Models\Verification', 'verifiable');
    }

    // Utility methods
    public function getMonthlyPerformance($year, $month)
    {
        return $this->performanceRecords()
            ->where('year', $year)
            ->where('month', $month)
            ->first();
    }

    // Scopes for better query performance
    public function scopeActive(Builder $query)
    {
        return $query->where('status', 1);
    }

    public function scopeByEmployer(Builder $query, $employerId)
    {
        return $query->where('employer_id', $employerId);
    }

    public function scopeWithBasicInfo(Builder $query)
    {
        return $query->with([
            'department:id,job_code,job_title',
            'department_role:id,name',
            'talent:id,email,first_name,last_name,photo'
        ]);
    }

    // Performance calculation methods
    public function getOverallPerformanceAttribute()
    {
        $assessmentScore = $this->getAssessmentPerformance();
        $goalScore = $this->getGoalPerformance();
        $trainingScore = $this->getTrainingPerformance();

        // Weight the scores (you can adjust these weights)
        $weights = [
            'assessment' => 0.4,
            'goals' => 0.4,
            'training' => 0.2
        ];

        return ($assessmentScore * $weights['assessment']) +
               ($goalScore * $weights['goals']) +
               ($trainingScore * $weights['training']);
    }

    private function getAssessmentPerformance()
    {
        $feedback = $this->completedAssessment()
            ->selectRaw('AVG(graded_score) as avg_score')
            ->first();

        return $feedback->avg_score ?? 0;
    }

    private function getGoalPerformance()
    {
        $goals = $this->goalsCompleted()
            ->selectRaw('COUNT(*) as total, SUM(CASE WHEN status = "done" THEN 1 ELSE 0 END) as completed')
            ->first();

        $total = $goals->total ?? 0;
        $completed = $goals->completed ?? 0;

        return $total > 0 ? ($completed / $total) * 100 : 0;
    }

    private function getTrainingPerformance()
    {
        // You can implement training performance logic here
        // For now, returning 0 as in your original code
        return 0;
    }
}

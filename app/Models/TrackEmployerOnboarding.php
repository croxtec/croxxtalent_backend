<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrackEmployerOnboarding extends Model
{
    use HasFactory;

    protected $fillable = [
        'employer_id',
        'department_faq',
        'employees_faq',
        'supervisors_faq',
        'assessment_faq',
        'projects_faq',
        'trainings_faq',
        'campaigns_faq',
        'candidate_faq',
        'skill_gap_faq',
        'competency_analysis_faq',
        'department_performance_faq',
        'employee_performance_faq',
        'department_development_faq',
        'employee_development_faq',
        'assessment_report_faq',
        'training_report_faq',
        'competency_report_faq'
    ];

    protected $casts = [
        'department_faq' => 'boolean',
        'employees_faq' => 'boolean',
        'supervisors_faq' => 'boolean',
        'assessment_faq' => 'boolean',
        'projects_faq' => 'boolean',
        'trainings_faq' => 'boolean',
        'campaigns_faq' => 'boolean',
        'candidate_faq' => 'boolean',
        'skill_gap_faq' => 'boolean',
        'competency_analysis_faq' => 'boolean',
        'department_performance_faq' => 'boolean',
        'employee_performance_faq' => 'boolean',
        'department_development_faq' => 'boolean',
        'employee_development_faq' => 'boolean',
        'assessment_report_faq' => 'boolean',
        'training_report_faq' => 'boolean',
        'competency_report_faq' => 'boolean'
    ];
}
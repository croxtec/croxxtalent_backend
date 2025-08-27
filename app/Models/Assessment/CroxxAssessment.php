<?php

namespace App\Models\Assessment;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CroxxAssessment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', //created by [S]
        'employer_id',
        'code',
        'type',   //company,supervisor,company_training,vetting,training,competency_match
        'category', // competency_evaluation, peer_review, experience
        'delivery_type', //quiz,classroom,on_the_job,assessment,experience,exam,external
        'is_published',

        'name',
        'description',
        'career_id',
        'department_id',
        'department_role_id',
        'level',
        'validity_period',
        'expected_percentage',
        'is_published'
    ];

    protected $appends = [
        // 'total_questions'
    ];

    public function company(){
        return $this->belongsTo('App\Models\User', 'employer_id', 'id')
                 ->select(['id','photo', 'first_name','last_name','company_name']);
    }

    public function competencies()
    {
        return $this->belongsToMany('App\Models\Competency\DepartmentMapping', 'assessment_competency', 'assessment_id', 'competency_id');
    }

    public function department(){
        return $this->belongsTo('App\Models\EmployerJobcode', 'department_id', 'id')
                    ->select(['id','job_code', 'job_title']);
    }

    public function department_role(){
        return $this->belongsTo('App\Models\DepartmentRole', 'department_role_id', 'id')
                    ->select(['id','name']);
    }

    public function evaluationQuestions()
    {
        return $this->hasMany('App\Models\Assessment\EvaluationQuestion', 'assessment_id', 'id')
                 ->with('questionImages')->whereNull('archived_at');
    }

    public function competencyQuestions()
    {
        return $this->hasMany('App\Models\Assessment\CompetencyQuestion', 'assessment_id', 'id')
                 ->with('questionDocument')->whereNull('archived_at');
    }

    public function questions()
    {
        if ($this->category == 'competency_evaluation') {
            return $this->evaluationQuestions();
        } else {
            return $this->competencyQuestions();
        }
    }

    public function career(){
        return $this->belongsTo('App\Models\Competency\CompetencySetup', 'career_id', 'id')
                    ->select(['id','competency', 'description']);
    }

    public function feedbacks()
    {
        return $this->hasMany('App\Models\Assessment\EmployerAssessmentFeedback', 'assessment_id', 'id');
    }

    public function assignedEmployees()
    {
        return $this->hasMany('App\Models\Assessment\AssignedEmployee', 'assessment_id', 'id');
    }

    // public function getTotalQuestionsAttribute(){
    //     return $this->questions->count();
    // }

    public function peerReviews()
    {
        return $this->hasMany(PeerReview::class, 'assessment_id');
    }

    public function reviewers()
    {
        return $this->belongsToMany('App\Models\Employee', 'peer_reviews', 'assessment_id', 'reviewer_id')
            ->select(['employees.id', 'name', 'job_code_id', 'department_role_id', 'photo_url', 'code']);
    }

    public function reviewees()
    {
        return $this->belongsToMany('App\Models\Employee', 'peer_reviews', 'assessment_id', 'employee_id')
            ->select(['employees.id', 'name', 'job_code_id', 'department_role_id', 'photo_url', 'code']);
    }

}


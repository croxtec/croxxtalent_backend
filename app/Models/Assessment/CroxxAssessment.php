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
        'type',   //company,vetting,trainings,competency
        'category', // competency_evaluation, peer_review, experience

        'name',
        'description',
        'department_id',
        'department_role_id',
        'level',
        'validity_period',
        'delivery_type',
        'expected_percentage',
        'is_published'
    ];


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
        return $this->hasMany('App\Models\Assessment\EvaluationQuestion', 'assessment_id', 'id')->whereNull('archived_at');
    }

    public function competencyQuestions()
    {
        return $this->hasMany('App\Models\Assessment\CompetencyQuestion', 'assessment_id', 'id')->whereNull('archived_at');
    }

    // public function getQuestionsAttribute()
    // {
    //     if ($this->category == 'competency_evaluation') {
    //         return $this->evaluationQuestions();
    //     } else {
    //         return $this->competencyQuestions();
    //     }
    // }

}

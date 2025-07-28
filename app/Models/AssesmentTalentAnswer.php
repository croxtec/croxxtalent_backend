<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use App\Traits\HasMedia;

class AssesmentTalentAnswer extends Model
{
    use HasFactory, HasMedia;

    protected $fillable = [
        'talent_id',
        'employee_id',
        'assessment_id',
        'assessment_question_id',
        'evaluation_result',

        'comment',
        'option',
        'options',
        'upload',
        'document'
    ];

    protected function options(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => json_decode($value, true),
            set: fn ($value) => json_encode($value),
        );
    }

     public function assessment()
    {
        return $this->belongsTo('App\Models\Assessment\CroxxAssessment', 'assessment_id', 'id')
                    ->select(['id','name','type','description','code']);
    }

    public function question()
    {
        return $this->belongsTo(CompetencyQuestion::class, 'assessment_question_id');
    }

    public function evaluationQuestion()
    {
        return $this->belongsTo(EvaluationQuestion::class, 'assessment_question_id');
    }

    public function talent()
    {
        return $this->belongsTo(User::class, 'talent_id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

}

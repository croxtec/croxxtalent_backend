<?php

namespace App\Models\Assessment;

use App\Models\AssesmentTalentAnswer as TalentAnswer;
use App\Models\AssesmentScoreSheet as ScoreSheet;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompetencyQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'assessment_id',
        'type',
        'question',
        'description',
        'files'
    ];

    public function response()
    {
        return $this->hasOne(TalentAnswer::class, 'assessment_question_id');
    }

    public function result()
    {
        return $this->hasOne(ScoreSheet::class, 'assessment_question_id');
    }
}

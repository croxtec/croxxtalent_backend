<?php

namespace App\Models\Assessment;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Traits\HasMedia;
use App\Models\AssesmentTalentAnswer as TalentAnswer;
use App\Models\AssesmentScoreSheet as ScoreSheet;


class EvaluationQuestion extends Model
{
    use HasFactory, HasMedia;

    protected $fillable = [
        'assessment_id',
        'type',
        'question',
        'option1',
        'option2',
        'option3',
        'option4',
        'answer'
    ];

    public function response()
    {
        return $this->hasOne(TalentAnswer::class, 'assessment_question_id');
    }

    public function result()
    {
        return $this->hasOne(ScoreSheet::class, 'assessment_question_id');
    }

     // Get only question images
     public function questionImages()
     {
         return $this->media()->where('collection_name', 'question_images');
     }
}

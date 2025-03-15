<?php

namespace App\Models\Assessment;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PeerReviewFeedback extends Model
{
    use HasFactory;

    protected $fillable = [
        'peer_review_id',
        'competency_id',
        'question_id',
        'answer',
        'score',
        'comments'
    ];

    public function peerReview()
    {
        return $this->belongsTo(PeerReview::class);
    }

    public function competency()
    {
        return $this->belongsTo('App\Models\Competency\DepartmentMapping', 'competency_id');
    }

    public function question()
    {
        return $this->belongsTo(CompetencyQuestion::class, 'question_id');
    }
}

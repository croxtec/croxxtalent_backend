<?php

namespace App\Models\Competency;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TalentCompetency extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'cv_id',
        'competency',
        'level',
        'benchmark'
    ];


    public function getCareerByCompetency(){
        return $this->belongsTo('App\Models\Competency\CompetencySetup', 'competency', 'competency');
    }

}

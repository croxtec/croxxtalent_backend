<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SavedJob extends Model
{
    use HasFactory;

    protected $fillable = [
        'campaign_id',
        'talent_user_id',
        'talent_cv_id',
        'reminder'
    ];

    protected $appends = [
        'campaign', 'cv', 'talent'
    ];


    // public function employerUser()
    // {
    //     return $this->belongsTo('App\Models\User', 'employer_user_id', 'id');
    // }
    //   // public function talentUser()
    // // {
    // //     return $this->belongsTo('App\Models\User', 'talent_user_id', 'id');
    // // }

    public function getCampaignAttribute()
    {
        return $this->belongsTo('App\Models\Campaign', 'campaign_id', 'id');
    }

    public function getTalentAttribute()
    {
        return $this->belongsTo('App\Models\User', 'talent_user_id', 'id');
    }

    public function getCvAttribute()
    {
        return $this->belongsTo('App\Models\Cv', 'talent_cv_id', 'id');
    }
}

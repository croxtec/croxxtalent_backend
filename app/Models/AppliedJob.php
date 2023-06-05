<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppliedJob extends Model
{
    use HasFactory;

    protected $fillable = [
        'employer_user_id',
        'campaign_id',
        'talent_user_id',
        'talent_cv_id',
        'status',
        'rating'
    ];

        /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'employer', 'cv'
    ];


    // User relationships

    public function employerUser()
    {
        return $this->belongsTo('App\Models\User', 'employer_user_id', 'id');
    }

    public function getEmployerAttribute()
    {
        return $this->employerUser;
    }

    public function talentUser()
    {
        return $this->belongsTo('App\Models\User', 'talent_user_id', 'id');
    }

    public function getTalentAttribute()
    {
        return $this->talentUser;
    }

    public function talentCv()
    {
        return $this->belongsTo('App\Models\Cv', 'talent_cv_id', 'id');
    }

    public function getCvAttribute()
    {
        return $this->talentCv;
    }

}

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
        'cv_upload_id',
        'status',
        'rating'
    ];

    // Rating Definition
    // 0 => Applied o
    // 1 => Qualified or ExtraUnder Review
    // 2 => Unqualify
    // 3 => Invited

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'campaign', 'employer', 'cv', 'talent', 'job_invitations'
    ];

    public function getStatusAttribute()
    {
        $status = [
            0 => 'Applied',
            1 => 'Qualified',
            2 => 'Unqualify',
            3 => 'Invited',
        ];

        return $status[$this->rating] ?? 'Unknown';
    }


    // User relationships

    public function job()
    {
        return $this->belongsTo('App\Models\Campaign', 'campaign_id', 'id');
    }

    public function employerUser()
    {
        return $this->belongsTo('App\Models\User', 'employer_user_id', 'id');
    }

    public function talentUser()
    {
        return $this->belongsTo('App\Models\User', 'talent_user_id', 'id');
    }

    public function talentCv()
    {
        return $this->belongsTo('App\Models\Cv', 'talent_cv_id', 'id');
    }

    // New relationship with JobInvitation model
    public function talentInvitation()
    {
        return $this->hasOne(JobInvitation::class, 'talent_user_id', 'talent_user_id')
                    ->where('campaign_id', $this->campaign_id);
    }


    // New relationship with CVFileUpload model
    public function cvUpload()
    {
        return $this->belongsTo(CVFileUpload::class, 'cv_upload_id');
    }

    // Combined accessors to avoid duplication
    public function getCampaignAttribute()
    {
        return $this->job;
    }

    public function getEmployerAttribute()
    {
        return $this->employerUser;
    }

    public function getTalentAttribute()
    {
        return $this->talentUser;
    }

    public function getCvAttribute()
    {
        return $this->talentCv;
    }

    public function getJobInvitationsAttribute()
    {
        return $this->talentInvitation;
    }

}

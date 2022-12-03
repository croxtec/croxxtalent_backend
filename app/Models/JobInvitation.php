<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobInvitation extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'job_invitations';

    /**
     * The attributes that are mass assignable.
     *
     * @var array $fillable
    */
    protected $fillable = [
        'employer_user_id',
        'talent_user_id',
        'talent_cv_id',
        'status',
        'accepted_at',
        'employed_at',
        'rejected_at',
        'employer_comment',
        'talent_comment',
    ];


    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'accepted_at' => 'datetime',
        'employed_at' => 'datetime',
        'rejected_at' => 'datetime',
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

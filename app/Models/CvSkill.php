<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class CvSkill extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'cv_skills';

    /**
     * The attributes that are mass assignable.
     *
     * @var array $fillable
     */
    protected $fillable = [
        'cv_id',
        'skill_id',
        'domain_id',
        'core_id',
        'level',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        // 'archived_at' => 'datetime',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'domain_name',
        'core_name',
        'skill_name'
    ];

    // Get Custom Model Attributes

    public function getDomainNameAttribute()
    {
        return $this->domain_id ? $this->domain->name : null;
    }

    public function getCoreNameAttribute()
    {
        return $this->core_id ? $this->core->name : null;
    }

    public function getSkillNameAttribute()
    {
        return $this->skill_id ? $this->skill->name : null;
    }

    public function cv()
    {
        return $this->belongsTo('App\Models\Cv', 'cv_id', 'id');
    }

    public function domain()
    {
        return $this->belongsTo('App\Models\Skill', 'domain_id', 'id');
    }

    public function core()
    {
        return $this->belongsTo('App\Models\SkillSecondary', 'core_id', 'id');
    }

    public function skill()
    {
        return $this->belongsTo('App\Models\SkillTertiary', 'skill_id', 'id');
    }

    public function vetting()
    {
        return $this->belongsTo('App\Models\VettingSummary', 'cv_skill', 'id');
    }
}

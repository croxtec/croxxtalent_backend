<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CvWorkExperience extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'cv_work_experiences';

    /**
     * The attributes that are mass assignable.
     *
     * @var array $fillable
     */
    protected $fillable = [
        'cv_id',
        'job_title_id',
        'job_title',
        'employer',
        'city',
        'country_code',
        'start_date',
        'end_date',
        'is_current',
        'description',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'start_date' => 'datetime:Y-m-d',
        'end_date' => 'datetime:Y-m-d',
        'is_current' => 'boolean',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'job_title_name', 'country_name'
    ];

    // Get Model Attributes

    public function getJobTitleNameAttribute()
    {
        return $this->job_title_id ? $this->jobTitle->name : $this->job_title;
        // return $this->job_title_id ? $this->jobTitle->name : null;
    }

    public function getCountryNameAttribute()
    {
        return $this->country_code ? $this->country->name : null;
    }

    // Set Model Attributes

    public function setStartDateAttribute($value)
    {
        if ($value) {
            $value = date("Y-m-d", strtotime($value));
        }
        $this->attributes['start_date'] = $value;
    }

    public function setEndDateAttribute($value)
    {
        if ($this->is_current) {
            $value = null;
        }
        if ($value) {
            $value = date("Y-m-d", strtotime($value));
        }
        $this->attributes['end_date'] = $value;
    }

    public function setIsCurrentAttribute($value)
    {
        if (!$this->end_date) {
            $value = true;
        }
        $this->attributes['is_current'] = $value;
    }

    public function cv()
    {
        return $this->belongsTo('App\Models\Cv', 'cv_id', 'id');
    }

    public function jobTitle()
    {
        return $this->belongsTo('App\Models\JobTitle', 'job_title_id', 'id');
    }

    public function country()
    {
        return $this->belongsTo('App\Models\Country', 'country_code', 'code');
    }

    public function competencies()
    {
        return $this->belongsToMany('App\Models\Competency\CompetencySetup', 'work_experience_competencies', 'work_experience_id', 'competency_id');
    }

}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Campaign extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'campaigns';

    /**
     * The attributes that are mass assignable.
     *
     * @var array $fillable
     */
    protected $fillable = [
        'user_id',
        'title',
        'industry_id',
        'job_title',
        'domain_id', 
        'core_id', 
        'interview',
        // 'job_title_id',
        'work_type',
        'minimum_degree_id',
        'is_confidential_salary',
        'currency_code',
        'min_salary',
        'max_salary',
        'number_of_positions',
        'years_of_experience',
        'expire_at',
        'city',
        'state_id',
        'country_code',
        'summary',
        'description',
        'photo',
        'is_managed',
    ];


    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'expire_at' => 'datetime:Y-m-d',
        'is_confidential_salary' => 'boolean',
        'is_published' => 'boolean',
        'is_managed' => 'boolean',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'industry_name', 'job_title_name', 'minimum_degree_name', 'photo_url', 'country_name', 'state_name',
        'skill_ids', 'skills','total_applications', 'currency_symbol',
        // 'certificate_course_ids', 'certificate_courses',
        'course_of_study_ids', 'course_of_studies',
        'language_ids', 'languages',
        'user_name', 'user_display_name',
    ];

    public function getPhotoUrlAttribute()
    {
        return $this->photo ? cloud_asset($this->photo) : null;
    }

    // Set Model Attributes

    public function setExpireAtAttribute($value)
    {
        if ($value) {
            $value = date("Y-m-d", strtotime($value));
            $value = "{$value} 23:59:59";
        }
        $this->attributes['expire_at'] = $value;
    }

    // User relationships

    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id', 'id');
    }

    public function getUserNameAttribute()
    {
        return $this->user_id ? $this->user->name : null;
    }

    public function getUserDisplayNameAttribute()
    {
        return $this->user_id ? $this->user->display_name : null;
    }


    // Industry relationships

    public function industry()
    {
        return $this->belongsTo('App\Models\Industry', 'industry_id', 'id');
    }

    public function getIndustryNameAttribute()
    {
        return $this->industry_id ? $this->industry->name : null;
    }

     public function getCurrencySymbolAttribute()
    {
        $symbol = '';
        if($this->currency_code == 'USD') $symbol = '$';
        if($this->currency_code == 'NGN') $symbol = '₦';
        return $symbol;
    }
    // Job Title relationships

    public function jobTitle()
    {
        return $this->belongsTo('App\Models\JobTitle', 'job_title_id', 'id');
    }

    public function getJobTitleNameAttribute()
    {
        return $this->job_title;
        // return $this->job_title_id ? $this->jobTitle->name : null;
    }

    // Degree relationships

    public function degree()
    {
        return $this->belongsTo('App\Models\Degree', 'minimum_degree_id', 'id');
    }

    public function getMinimumDegreeNameAttribute()
    {
        return $this->minimum_degree_id ? $this->degree->name : null;
    }

    // Country relationships

    public function country()
    {
        return $this->belongsTo('App\Models\Country', 'country_code', 'code');
    }

    public function getCountryNameAttribute()
    {
        return $this->country_code ? $this->country->name : null;
    }

    // State relationships

    public function state()
    {
        return $this->belongsTo('App\Models\State', 'state_id', 'id');
    }

    public function getStateNameAttribute()
    {
        return $this->state_id ? $this->state->name : null;
    }

    // Skills pivot relationships

    public function skills()
    {
        return $this->belongsToMany('App\Models\Skill', 'campaign_skill', 'campaign_id', 'skill_id');
    }

    public function getSkillsAttribute()
    {
        return $this->skills()->get();
    }

    public function getSkillIdsAttribute()
    {
        // $dataSet = $this->skills()->get(['id']);
        // $ids = [];
        // if ($dataSet->isNotEmpty()) {
        //     foreach($dataSet as $data)
        //     {
        //         $ids[] = $data->id;
        //     }
        // }
        // return $ids;
    }

    // Course of Studies pivot relationships

    public function courseOfStudies()
    {
        return $this->belongsToMany('App\Models\CourseOfStudy', 'campaign_course_of_study', 'campaign_id', 'course_of_study_id');
    }

    public function getCourseOfStudiesAttribute()
    {
        return $this->courseOfStudies()->get();
    }

    public function getCourseOfStudyIdsAttribute()
    {
        // $dataSet = $this->courseOfStudies()->get(['id']);
        // $ids = [];
        // if ($dataSet->isNotEmpty()) {
        //     foreach($dataSet as $data)
        //     {
        //         $ids[] = $data->id;
        //     }
        // }
        // return $ids;
    }


    // Certificate Courses pivot relationships

    public function certificationCourses()
    {
        return $this->belongsToMany('App\Models\CertificationCourse', 'campaign_certification_course', 'campaign_id', 'certification_course_id');
    }

    public function getCertificationCoursesAttribute()
    {
        return $this->certificationCourses()->get(); 
    }

    public function getCertificationCourseIdsAttribute()
    {
        // $dataSet = $this->certificationCourses()->get(['id']);
        // $ids = [];
        // if ($dataSet->isNotEmpty()) {
        //     foreach($dataSet as $data)
        //     {
        //         $ids[] = $data->id;
        //     }
        // }
        // return $ids;
    }


    // Languages pivot relationships

    public function languages()
    {
        return $this->belongsToMany('App\Models\Language', 'campaign_language', 'campaign_id', 'language_id');
    }

    public function getLanguagesAttribute()
    {
        return $this->languages()->get();
    }

    public function getLanguageIdsAttribute()
    {
        // $dataSet = $this->languages()->get(['id']);
        // $ids = [];
        // if ($dataSet->isNotEmpty()) {
        //     foreach($dataSet as $data)
        //     {
        //         $ids[] = $data->id;
        //     }
        // }
        // return $ids;
    }

    public function applications()
    {
        return $this->hasMany('App\Models\AppliedJob', 'campaign_id', 'id');
    }

    public function getTotalApplicationsAttribute()
    {
        return $this->applications()->count();
    }

}

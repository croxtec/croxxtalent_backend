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
        'code',
        'title',
        'industry_id',
        'job_title',
        'assessment_id',
        'department_id',
        'experience_level',
        'work_site',
        // 'domain_id',
        // 'core_id',
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
        'language_ids', 'languages', 'department_name',
        'user_name', 'user_display_name',
    ];

    // protected $appends = [
    //     'photo_url', 'currency_symbol'
    // ];

    /**
     * Heavy appends - only load when specifically needed
     */
    protected $heavyAppends = [
        'industry_name', 'job_title_name', 'minimum_degree_name', 'country_name', 'state_name',
        'department_name', 'user_name', 'user_display_name', 'total_applications'
    ];

    /**
     * Relationship appends - load separately when needed
     */
    protected $relationshipAppends = [
        'skill_ids', 'skills', 'course_of_study_ids', 'course_of_studies',
        'language_ids', 'languages'
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

    public function appliedJobs()
    {
        return $this->hasMany('App\Models\AppliedJob', 'campaign_id', 'id');
    }

    public function savedJobs()
    {
        return $this->hasMany('App\Models\SavedJob', 'campaign_id', 'id');
    }

    public function getUserNameAttribute()
    {
        return $this->user_id ? $this->user->name : null;
    }

    public function getUserDisplayNameAttribute()
    {
        return $this->user_id ? $this->user->display_name : null;
    }


    /**
     * Scope for basic campaign data (public view)
     */
    public function scopeForPublicView($query)
    {
        return $query->select([
            'id', 'code', 'title', 'job_title', 'summary', 'description',
            'experience_level', 'work_site', 'work_type', 'city', 'expire_at',
            'currency_code', 'min_salary', 'max_salary', 'number_of_positions',
            'years_of_experience', 'is_confidential_salary', 'photo',
            'industry_id', 'department_id', 'minimum_degree_id', 'country_code', 'state_id', 'user_id'
        ]);
    }

    /**
     * Scope for employer dashboard view
     */
    public function scopeForEmployerDashboard($query)
    {
        return $query->select([
            'id', 'code', 'title', 'expire_at', 'created_at', 'is_published'
           ])->withCount('applications');
    }

     public function loadHeavyAppends()
    {
        $this->appends = array_merge($this->appends, $this->heavyAppends);
        return $this;
    }

    /**
     * Load relationship appends when needed
     */
    public function loadRelationshipAppends()
    {
        $this->appends = array_merge($this->appends, $this->relationshipAppends);
        return $this;
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

    public function department()
    {
        return $this->belongsTo('App\Models\EmployerJobcode', 'department_id', 'id');
    }

    public function getDepartmentNameAttribute()
    {
        return $this->department_id ? $this->department->job_code : null;
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
        return $this->belongsToMany('App\Models\Competency\DepartmentMapping', 'campaign_skill', 'campaign_id', 'skill_id');
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

     /**
     * Method to get applications summary without loading full data
     */
    public function getApplicationsSummary()
    {
        return $this->applications()
            ->selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN rating = 0 THEN 1 ELSE 0 END) as applied,
                SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) as qualified,
                SUM(CASE WHEN rating = 2 THEN 1 ELSE 0 END) as unqualified,
                SUM(CASE WHEN rating = 3 THEN 1 ELSE 0 END) as invited
            ')
            ->first();
    }

}

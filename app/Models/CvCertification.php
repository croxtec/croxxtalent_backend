<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CvCertification extends Model
{
    use HasFactory;
    
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'cv_certifications';

    /**
     * The attributes that are mass assignable.
     *
     * @var array $fillable
     */
    protected $fillable = [
        'cv_id',
        'institution',
        'certification_course_id',
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
        'certification_course_name'
    ];

    // Get Custom Model Attribute

    public function getCertificationCourseNameAttribute()
    {
        return $this->certification_course_id ? $this->certificationCourse->name : null;
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

    public function certificationCourse()
    {
        return $this->belongsTo('App\Models\CertificationCourse', 'certification_course_id', 'id');
    }

}

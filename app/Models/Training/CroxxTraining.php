<?php

namespace App\Models\Training;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CroxxTraining extends Model
{
    use HasFactory;


    protected $fillable = [
        'user_id',
        'employer_id',
        'code',
        'type',   //company, training, competency
        'title',
        'experience_level',
        'objective',

        'cover_photo',
        'department_id',
        'career_id',
        'assessment_level',
        'assessment_id',
        'is_published'
    ];

    protected $appends = [
        'total_lessons'
    ];

    public function author(){
        return $this->belongsTo('App\Models\User', 'user_id', 'id')
                 ->select(['id','photo', 'first_name','last_name','company_name']);
    }

    public function department(){
        return $this->belongsTo('App\Models\EmployerJobcode', 'department_id', 'id')
                    ->select(['id','job_code', 'job_title']);
    }

    public function career(){
        return $this->belongsTo('App\Models\Competency\CompetencySetup', 'career_id', 'id')
                    ->select(['id','competency', 'job_title']);
    }

    public function getTotalLessonsAttribute()
    {
        return $this->hasMany('App\Models\Training\CroxxLesson', 'training_id', 'id')
            ->whereNull('archived_at')->count();
    }

    public function review_lessons(){
        return $this->hasMany('App\Models\Training\CroxxLesson', 'training_id', 'id')
                 ->whereNull('archived_at')->select(['id','title','alias']);
    }

    public function lessons(){
        return $this->hasMany('App\Models\Training\CroxxLesson', 'training_id', 'id')
                 ->whereNull('archived_at');
    }

    public function libraries()
    {
        return $this->hasMany('App\Models\Training\CourseLibrary', 'training_id', 'id');
    }

    public function libaray()
    {
        return $this->hasOne('App\Models\Training\CourseLibrary', 'training_id', 'id');
    }

    public function learning()
    {
        return $this->hasOne('App\Models\Assessment\EmployeeLearningPath', 'training_id', 'id');
    }
}

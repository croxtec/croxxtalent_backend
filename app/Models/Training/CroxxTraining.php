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

    public function getTotalLessonsAttribute()
    {
        return $this->hasMany('App\Models\Training\CroxxLesson', 'training_id', 'id')
            ->whereNull('archived_at')->count();
    }

    public function review_lessons(){
        return $this->hasMany('App\Models\Training\CroxxLesson', 'training_id', 'id')
                 ->whereNull('archived_at')->select(['id','title','alias','description']);
    }

    public function lessons(){
        return $this->hasMany('App\Models\Training\CroxxLesson', 'training_id', 'id')
                 ->whereNull('archived_at');
    }

}

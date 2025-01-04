<?php

namespace App\Models\Training;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Training\LessonResource;

class CroxxLesson extends Model
{
    use HasFactory;

    protected $fillable = [
        'training_id',
        'alias',
        'title',
        'description',
        'video_url',
        'resources',
        'keyword'
    ];


    public function resources()
    {
        return $this->hasMany('App\Models\Training\LessonResource', 'lesson_id', 'id');
    }

}

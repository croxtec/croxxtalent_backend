<?php

namespace App\Models\Training;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LessonResource extends Model
{
    use HasFactory;

    protected $fillable = [
        'employer_user_id',
        'training_id',
        'lesson_id',
        'title',
        'file_name',
        'file_type',
        'file_size',
        'file_url'
    ];

}

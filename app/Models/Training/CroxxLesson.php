<?php

namespace App\Models\Training;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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



}

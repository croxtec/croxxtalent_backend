<?php

namespace App\Models\Training;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CroxxLesson extends Model
{
    use HasFactory;

    protected $fillable = [
        'training_id',
        'title',
        'alias',
        'description',
        'video',
        'resources',
        'cover_photo',
        'keyword'
    ];



}

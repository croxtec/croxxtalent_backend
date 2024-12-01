<?php

namespace App\Models\Training;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LessonSetup extends Model
{
    use HasFactory;

    protected $fillable = [
        'department',
        'level',
        'alias',
        'title',
        'description',
        'keywords',
        'generated_id'
    ];

}

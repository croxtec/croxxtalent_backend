<?php

namespace App\Models\Training;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseLibrary extends Model
{
    use HasFactory;

    protected $fillable = [
        'talent_id',
        'training_id',
    ];

}

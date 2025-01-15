<?php

namespace App\Models\Project;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Milestone extends Model
{
    use HasFactory;

    protected $fillable = [
        'employer_user_id',
        'project_id',
        'milestone_name',
        'description',
        'start_date',
        'end_date',
        'priority_level',
        'resource_allocated'
    ];


}

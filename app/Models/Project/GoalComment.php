<?php

namespace App\Models\Project;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GoalComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'goal_id',
        'employee_id',
        'comment',
        'attachment'
    ];

}

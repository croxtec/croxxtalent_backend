<?php

namespace App\Models\Project;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GoalActivity extends Model
{
    use HasFactory;

    protected $fillable = [
        'goal_id',
        'activity_type',
        'description',
        'performed_by'
    ];
}

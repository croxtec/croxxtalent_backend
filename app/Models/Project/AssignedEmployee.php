<?php

namespace App\Models\Project;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssignedEmployee extends Model
{
    use HasFactory;

    protected $table = 'goal_assigned_employees';

    protected $fillable = [
        'goal_id', 'employee_id', 'assigned_by', 'assigned_at'
    ];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DepartmentKpiSetup extends Model
{
    use HasFactory;

    protected $fillable = [
        'department',
        'department_goals',
        'level_kpis'
    ];

    // protected $casts = [
    //     'department_goals'   => 'array',
    //     'beginner_kpis'      => 'array',
    //     'intermediate_kpis'  => 'array',
    //     'advance_kpis'       => 'array',
    //     'expert_kpis'        => 'array',
    //     'level_kpis'         => 'array',
    // ];


}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompetencyKpiSetup extends Model
{
    use HasFactory;

    protected $fillable = [
        'department_mapping_id',
        'kpi_name',
        'level',
        'description',
        'frequency',
        'target_score',
        'weight'
    ];
}

<?php

namespace App\Models\Competency;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompetencySetup extends Model
{
    use HasFactory;

    protected $fillable = [
        'industry_id',
        'job_title',
        'competency',
        'match_percentage',
        'benchmark',
        'description'
    ];

}

<?php

namespace App\Models\Assessment;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CroxxAssessment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', //created by [S]
        'employer_id',
        'code',
        'type',   //company,vetting,trainings,competency
        'category', // competency_evaluation, peer_review, experience

        'name',
        'description',
        'department_id',
        'department_role_id',
        'level',
        'validity_period',
        'delivery_type',
        'expected_percentage',
        'is_published'
    ];


}

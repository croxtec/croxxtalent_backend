<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Assesment extends Model
{
    use HasFactory;

    protected $fillable = [
        'admin_id',
        'employer_id',
        'domain_id',
        'core_id',
        'skill_id',
        'level',
        'code',
        'type',
        'name',
        'description',
        'category',
        'validity_period',
        'delivery_type',
        'expected_score'
    ];


    public function questions()
    {
        return $this->hasMany('App\Models\AssesmentQuestion', 'assesment_id', 'id');
    }

    public function answers()
    {
        return $this->hasMany('App\Models\AssesmentTalentAnswer', 'assesment_id', 'id');
    }
}

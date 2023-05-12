<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class Assesment extends Model
{
    use HasFactory;

    protected $fillable = [
        'admin_id',
        // 'employer_id',
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
        'expected_score',

        'job_code_id',
        'candidates',
        'managers'
    ];

    protected function managers(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => json_decode($value, true),
            set: fn ($value) => json_encode($value),
        );
    }

    protected function candidates(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => json_decode($value, true),
            set: fn ($value) => json_encode($value),
        );
    }


    public function questions()
    {
        return $this->hasMany('App\Models\AssesmentQuestion', 'assesment_id', 'id');
    }

    public function answers()
    {
        return $this->hasMany('App\Models\AssesmentTalentAnswer', 'assesment_id', 'id');
    }

    public function summary(){
        return $this->hasMany('App\Models\AssesmentSummary', 'assesment_id', 'id');
    }
}

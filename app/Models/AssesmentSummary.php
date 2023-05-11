<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssesmentSummary extends Model
{
    use HasFactory;

    protected $fillable = [
        'assesment_id',
        'talent_id',
        'employer_id'
    ];


    public function assesment(){
        return $this->belongsTo('App\Models\Assesment', 'assesment_id', 'id');
    }

}

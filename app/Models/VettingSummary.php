<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VettingSummary extends Model
{
    use HasFactory;

    protected $fillable = [
        'cv_skill',
        'talent_id',
        'assesment_id'
    ];

}

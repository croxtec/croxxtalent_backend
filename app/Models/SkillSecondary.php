<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SkillSecondary extends Model
{
    use HasFactory;

    protected $table = 'skill_secondaries';

    protected $fillable = [
        'skill_id',
        'name',
        'description',
    ];
}

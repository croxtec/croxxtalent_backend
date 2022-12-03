<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SkillTertiary extends Model
{
    use HasFactory;

    protected $fillable = [
        'skill_id',
        'skill_secondary_id',
        'name',
        'description',
    ];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use PhpParser\Node\Expr\FuncCall;

class Skill extends Model
{
    use HasFactory;
    
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'skills';

    /**
     * The attributes that are mass assignable.
     *
     * @var array $fillable
     */
    protected $fillable = [
        'industry_id',
        'name',
        'description',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'archived_at' => 'datetime',
    ];


    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        // 'country_name'
    ];

    public function secondary(){
        return $this->belongsTo('App\Models\SkillSecondary','skill_id', 'id');
    }

    public function total_secondaries(){
        // return $this->belongsTo('App\Models\SkillSecondary', 'skill_id')->withCount('skill_id');
    }

    public function industry(){
        return $this->belongsTo('App\Models\Industry');
    }
}

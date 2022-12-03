<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class State extends Model
{
    use HasFactory;
    
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'states';

    /**
     * The attributes that are mass assignable.
     *
     * @var array $fillable
     */
    protected $fillable = [
        // 'id',
        'country_code',
        'name',
        'latitude',
        'longitude',
        'altitude',
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
        'country_name'
    ];

    
    public function getCountryNameAttribute()
    {
        return $this->country_code ? $this->country->name : null;
    }

    public function country()
    {
        return $this->belongsTo('App\Models\Country', 'country_code', 'code');
    }
}

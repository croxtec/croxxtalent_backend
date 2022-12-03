<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    use HasFactory;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'countries';

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'archived_at' => 'datetime',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array $fillable
     */
    protected $fillable = [
        'code',
        'code3',
        'num_code',
        'phone_code',
        'name',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'total_states'
    ];

    public function states()
    {
        return $this->hasMany('App\Models\State', 'country_code', 'code');
    }

    public function getTotalStatesAttribute()
    {
        return $this->states()->count();
    }
}

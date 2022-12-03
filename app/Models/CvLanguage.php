<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CvLanguage extends Model
{
    use HasFactory;
    
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'cv_languages';

    /**
     * The attributes that are mass assignable.
     *
     * @var array $fillable
     */
    protected $fillable = [
        'cv_id',
        'language_id',
        'level',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        // 'archived_at' => 'datetime',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'language_name'
    ];

    // Get Custom Model Attribute

    public function getLanguageNameAttribute()
    {
        return $this->language_id ? $this->language->name : null;
    }

    // Model Relations

    public function cv()
    {
        return $this->belongsTo('App\Models\Cv', 'cv_id', 'id');
    }

    public function language()
    {
        return $this->belongsTo('App\Models\Language', 'language_id', 'id');
    }
}

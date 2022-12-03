<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReferenceQuestion extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'reference_questions';

    /**
     * The attributes that are mass assignable.
     *
     * @var array $fillable
     */
    protected $fillable = [
        'question',
        'description',
        'is_predefined_options',
        'sort_order',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'is_predefined_options' => 'boolean',
        'is_active' => 'boolean',
    ];


    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        // 'country_name'
    ];

    public function options()
    {
        return $this->hasMany('App\Models\ReferenceQuestionOption', 'reference_question_id', 'id');
    }

    // public function getOptionsAttribute()
    // {
    //     return $this->options()
    //                 ->orderBy("created_at", "asc")
    //                 ->get();
    // }
}

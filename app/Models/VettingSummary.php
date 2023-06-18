<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VettingSummary extends Model
{
    use HasFactory;
      /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'vetting_summaries';

    protected $fillable = [
        'cv_skill',
        'talent_id',
        'assesment_id'
    ];

    public function assesment(){
        return $this->belongsTo('App\Models\Assesment', 'assesment_id', 'id');
    }
}

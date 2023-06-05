<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SavedJob extends Model
{
    use HasFactory;

    protected $fillable = [
        'campaign_id',
        'talent_user_id',
        'talent_cv_id',
        'reminder'
    ];
}

<?php

namespace App\Models;

use App\Traits\HasMedia;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CVFileUpload extends Model
{
    use HasFactory, HasMedia;

    protected $table = 'cv_file_uploads';

    protected $fillable = [
        'user_id',
        'cv_id',
        'file_name',
        'original_name',
        'file_size',
        'file_url',
        'file_type',
        'is_primary',
        'uploaded_at'
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'uploaded_at' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function cv()
    {
        return $this->belongsTo(Cv::class);
    }

    public function setPrimary()
    {
        // Remove primary status from other CVs for this user
        self::where('user_id', $this->user_id)->update(['is_primary' => false]);

        // Set this as primary
        $this->update(['is_primary' => true]);

        // Update the CV record
        if ($this->cv) {
            $this->cv->update(['cv_file_url' => $this->file_url]);
        }
    }
}

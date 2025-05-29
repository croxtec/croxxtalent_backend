<?php

namespace App\Models\Training;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LessonResource extends Model
{
    use HasFactory;

    protected $fillable = [
        'employer_user_id',
        'training_id',
        'lesson_id',
        'title',
        'file_name',
        'file_type',
        'file_size',
        'file_url'
    ];

     /**
     * Get the training that owns this resource
     */
    public function training()
    {
        return $this->belongsTo('App\Models\Training\CroxxTraining', 'training_id');
    }

    /**
     * Get the lesson that owns this resource
     */
    public function lesson()
    {
        return $this->belongsTo('App\Models\Training\CroxxLesson', 'lesson_id');
    }

    /**
     * Get formatted file size
     */
    public function getFormattedFileSizeAttribute()
    {
        $size = $this->file_size;
        if ($size >= 1073741824) {
            return number_format($size / 1073741824, 2) . ' GB';
        } elseif ($size >= 1048576) {
            return number_format($size / 1048576, 2) . ' MB';
        } elseif ($size >= 1024) {
            return number_format($size / 1024, 2) . ' KB';
        } else {
            return $size . ' bytes';
        }
    }

    /**
     * Get file icon based on file type
     */
    public function getFileIconAttribute()
    {
        $iconMap = [
            'pdf' => 'fa-file-pdf',
            'doc' => 'fa-file-word',
            'docx' => 'fa-file-word',
            'xls' => 'fa-file-excel',
            'xlsx' => 'fa-file-excel',
            'ppt' => 'fa-file-powerpoint',
            'pptx' => 'fa-file-powerpoint',
            'mp4' => 'fa-file-video',
            'avi' => 'fa-file-video',
            'mov' => 'fa-file-video',
            'mp3' => 'fa-file-audio',
            'wav' => 'fa-file-audio',
            'jpg' => 'fa-file-image',
            'jpeg' => 'fa-file-image',
            'png' => 'fa-file-image',
            'gif' => 'fa-file-image',
            'zip' => 'fa-file-archive',
            'rar' => 'fa-file-archive',
            'txt' => 'fa-file-text',
        ];

        return $iconMap[strtolower($this->file_type)] ?? 'fa-file';
    }
}

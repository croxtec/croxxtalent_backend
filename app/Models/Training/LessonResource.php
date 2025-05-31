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
     * Boot method to handle file type conversion
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->file_type = self::normalizeFileType($model->file_type);
        });

        static::updating(function ($model) {
            if ($model->isDirty('file_type')) {
                $model->file_type = self::normalizeFileType($model->file_type);
            }
        });
    }

    /**
     * Convert MIME types to simple file type names
     */
    public static function normalizeFileType($mimeType)
    {
        $mimeToType = [
            // Microsoft Office Documents
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'word',
            'application/msword' => 'word',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'excel',
            'application/vnd.ms-excel' => 'excel',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'powerpoint',
            'application/vnd.ms-powerpoint' => 'powerpoint',

            // PDF
            'application/pdf' => 'pdf',

            // Images
            'image/jpeg' => 'image',
            'image/jpg' => 'image',
            'image/png' => 'image',
            'image/gif' => 'image',
            'image/bmp' => 'image',
            'image/webp' => 'image',
            'image/svg+xml' => 'image',

            // Videos
            'video/mp4' => 'video',
            'video/avi' => 'video',
            'video/quicktime' => 'video',
            'video/x-msvideo' => 'video',
            'video/wmv' => 'video',
            'video/mov' => 'video',
            'video/webm' => 'video',

            // Audio
            'audio/mpeg' => 'audio',
            'audio/mp3' => 'audio',
            'audio/wav' => 'audio',
            'audio/ogg' => 'audio',
            'audio/aac' => 'audio',
            'audio/flac' => 'audio',

            // Archives
            'application/zip' => 'archive',
            'application/x-rar-compressed' => 'archive',
            'application/x-7z-compressed' => 'archive',
            'application/x-tar' => 'archive',
            'application/gzip' => 'archive',

            // Text files
            'text/plain' => 'text',
            'text/csv' => 'text',
            'text/html' => 'text',
            'text/xml' => 'text',
            'application/json' => 'text',

            // Other common types
            'application/rtf' => 'document',
            'application/x-shockwave-flash' => 'flash',
        ];

        return $mimeToType[strtolower($mimeType)] ?? 'file';
    }

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
     * Get file icon based on simplified file type
     */
    public function getFileIconAttribute()
    {
        $iconMap = [
            'pdf' => 'fa-file-pdf',
            'word' => 'fa-file-word',
            'excel' => 'fa-file-excel',
            'powerpoint' => 'fa-file-powerpoint',
            'video' => 'fa-file-video',
            'audio' => 'fa-file-audio',
            'image' => 'fa-file-image',
            'archive' => 'fa-file-archive',
            'text' => 'fa-file-text',
            'document' => 'fa-file-alt',
            'flash' => 'fa-file-code',
        ];

        return $iconMap[$this->file_type] ?? 'fa-file';
    }

    /**
     * Get human readable file type name
     */
    public function getReadableFileTypeAttribute()
    {
        $typeMap = [
            'word' => 'Microsoft Word Document',
            'excel' => 'Microsoft Excel Spreadsheet',
            'powerpoint' => 'Microsoft PowerPoint Presentation',
            'pdf' => 'PDF Document',
            'image' => 'Image File',
            'video' => 'Video File',
            'audio' => 'Audio File',
            'archive' => 'Archive File',
            'text' => 'Text File',
            'document' => 'Document',
            'flash' => 'Flash File',
            'file' => 'File'
        ];

        return $typeMap[$this->file_type] ?? 'Unknown File Type';
    }

    /**
     * Check if file is downloadable
     */
    public function getIsDownloadableAttribute()
    {
        $downloadableTypes = ['pdf', 'word', 'excel', 'powerpoint', 'archive', 'text', 'document'];
        return in_array($this->file_type, $downloadableTypes);
    }

    /**
     * Check if file is previewable
     */
    public function getIsPreviewableAttribute()
    {
        $previewableTypes = ['pdf', 'image', 'text'];
        return in_array($this->file_type, $previewableTypes);
    }
}

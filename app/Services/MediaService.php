<?php 

namespace App\Services;
use Cloudinary\Cloudinary;
use App\Models\Media;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Exception;


class MediaService
{
    protected $cloudinary;

    public function __construct(Cloudinary $cloudinary)
    {
        $this->cloudinary = $cloudinary;
    }

    public function uploadFile($file, array $options = [])
    {
        $userId = $options['user_id'] ?? auth()->id();
        $employerId = $options['employer_id'] ?? null;
        $employeeId = $options['employee_id'] ?? null;
        $collection = $options['collection'] ?? 'default';
        $mediableType = $options['mediable_type'] ?? null;
        $mediableId = $options['mediable_id'] ?? null;

        // Generate filename
        $extension = $file->getClientOriginalExtension();
        $filename = time() . '-' . Str::random(32) . '.' . $extension;
        $year = date('Y');

        // Build upload path based on context
        $uploadPath = $this->buildUploadPath($userId, $employerId, $collection, $year);

        try {
            // Upload to Cloudinary
            $uploadResult = $this->cloudinary->uploadApi()->upload(
                $file->getRealPath(),
                [
                    'resource_type' => $this->getResourceType($file),
                    'folder' => $uploadPath,
                    'public_id' => pathinfo($filename, PATHINFO_FILENAME),
                ]
            );

            // Create media record
            $media = Media::create([
                'user_id' => $userId,
                'employer_id' => $employerId,
                'employee_id' => $employeeId,
                'mediable_type' => $mediableType,
                'mediable_id' => $mediableId,
                'collection_name' => $collection,
                'file_name' => $filename,
                'original_name' => $file->getClientOriginalName(),
                'file_type' => $file->getClientMimeType(),
                'file_size' => $file->getSize(),
                'file_url' => $uploadResult['secure_url'],
                'cloudinary_public_id' => $uploadResult['public_id'],
                'metadata' => [
                    'width' => $uploadResult['width'] ?? null,
                    'height' => $uploadResult['height'] ?? null,
                    'format' => $uploadResult['format'] ?? null,
                ],
                'uploaded_at' => now()
            ]);

            return $media;
        } catch (Exception $e) {
            Log::error('File upload failed', [
                'error' => $e->getMessage(),
                'file' => $file->getClientOriginalName(),
                'user_id' => $userId
            ]);
            throw $e;
        }
    }

    public function uploadMultiple($files, array $options = [])
    {
        $uploadedMedia = [];
        
        foreach ($files as $file) {
            try {
                $uploadedMedia[] = $this->uploadFile($file, $options);
            } catch (Exception $e) {
                // Log error but continue with other files
                Log::error('Failed to upload file in batch', [
                    'file' => $file->getClientOriginalName(),
                    'error' => $e->getMessage()
                ]);
            }
        }

        return collect($uploadedMedia);
    }

    protected function buildUploadPath($userId, $employerId, $collection, $year)
    {
        $path = [];
        
        if ($employerId) {
            $path[] = "company_{$employerId}";
        }
        
        $path[] = "user_{$userId}";
        $path[] = strtoupper($collection);
        $path[] = $year;

        return implode('/', $path);
    }

    protected function getResourceType($file)
    {
        $mimeType = $file->getClientMimeType();
        
        if (str_starts_with($mimeType, 'image/')) {
            return 'image';
        } elseif (str_starts_with($mimeType, 'video/')) {
            return 'video';
        } else {
            return 'raw';
        }
    }

    public function deleteMedia(Media $media)
    {
        try {
            // Delete from Cloudinary
            $this->cloudinary->uploadApi()->destroy($media->cloudinary_public_id);
            
            // Delete from database
            $media->delete();
            
            return true;
        } catch (Exception $e) {
            Log::error('Failed to delete media', [
                'media_id' => $media->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}


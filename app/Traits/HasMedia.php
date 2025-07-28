<?php

namespace App\Traits;
use App\Services\MediaService;
use App\Models\Media;

trait HasMedia
{
    public function media()
    {
        return $this->morphMany(Media::class, 'mediable');
    }

    public function addMedia($file, $collection = 'default', array $options = [])
    {
        $mediaService = app(MediaService::class);
        
        $uploadOptions = array_merge($options, [
            'mediable_type' => get_class($this),
            'mediable_id' => $this->id,
            'collection' => $collection
        ]);

        return $mediaService->uploadFile($file, $uploadOptions);
    }

    public function addMultipleMedia($files, $collection = 'default', array $options = [])
    {
        $mediaService = app(MediaService::class);
        
        $uploadOptions = array_merge($options, [
            'mediable_type' => get_class($this),
            'mediable_id' => $this->id,
            'collection' => $collection
        ]);

        return $mediaService->uploadMultiple($files, $uploadOptions);
    }

    public function getMedia($collection = null)
    {
        $query = $this->media();
        
        if ($collection) {
            $query->where('collection_name', $collection);
        }
        
        return $query->get();
    }

    public function getFirstMedia($collection = null)
    {
        return $this->getMedia($collection)->first();
    }

    public function clearMediaCollection($collection)
    {
        $mediaService = app(MediaService::class);
        $mediaItems = $this->getMedia($collection);
        
        foreach ($mediaItems as $media) {
            $mediaService->deleteMedia($media);
        }
    }
}
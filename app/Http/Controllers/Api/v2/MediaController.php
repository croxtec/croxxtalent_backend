<?php

namespace App\Http\Controllers\Api\v2;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\MediaService;

class MediaController extends Controller
{
    protected $mediaService;

    public function __construct(MediaService $mediaService)
    {
        $this->mediaService = $mediaService;
    }

    public function upload(Request $request)
    {
        $request->validate([
            'files.*' => 'required|file|max:10240', // 10MB max
            'model_type' => 'required|string',
            'model_id' => 'required|integer',
            'collection' => 'string',
            'employer_id' => 'nullable|integer',
            'employee_id' => 'nullable|integer',
        ]);

        $files = $request->file('files');
        $modelType = $request->input('model_type');
        $modelId = $request->input('model_id');
        $collection = $request->input('collection', 'default');

        // Find the model instance
        $model = $modelType::findOrFail($modelId);

        $uploadOptions = [
            'user_id' => auth()->id(),
            'employer_id' => $request->input('employer_id'),
            'employee_id' => $request->input('employee_id'),
        ];

        if (is_array($files)) {
            $uploadedMedia = $model->addMultipleMedia($files, $collection, $uploadOptions);
        } else {
            $uploadedMedia = $model->addMedia($files, $collection, $uploadOptions);
        }

        return response()->json([
            'message' => 'Files uploaded successfully',
            'media' => $uploadedMedia
        ]);
    }

    public function getCompanyDocuments($employerId)
    {
        $media = Media::forCompany($employerId)
            ->with(['user', 'mediable'])
            ->latest()
            ->paginate(20);

        return response()->json($media);
    }

    public function getEmployeeDocuments($employeeId)
    {
        $media = Media::forEmployee($employeeId)
            ->with(['user', 'mediable'])
            ->latest()
            ->paginate(20);

        return response()->json($media);
    }
}

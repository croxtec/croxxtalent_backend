<?php

namespace App\Http\Controllers\Api\v2\Learning;

use App\Http\Controllers\Controller;
use App\Http\Requests\LessonResourceRequest;
use App\Models\Training\CroxxLesson;
use App\Models\Training\LessonResource;
use Cloudinary\Cloudinary;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LessonResourceController extends Controller
{

    protected $cloudinary;

    public function __construct(Cloudinary $cloudinary)
    {
        $this->cloudinary = $cloudinary;
    }

  /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\LessonResourceRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(LessonResourceRequest $request)
    {
        try {
            $user = $request->user();
            $validatedData = $request->validated();

            $lesson = CroxxLesson::find($validatedData['lesson_id']);

            DB::beginTransaction();

            $files = $request->file('files');
            $resources = [];

            foreach ($files as $file) {
                // Upload file to Cloudinary
                $extension = $file->getClientOriginalExtension();
                $filename = time() . '-' . Str::random(32);
                $filename = "{$filename}.$extension";
                $year = date('Y');

                $rel_upload_path  = "$user->id/TRAINING/{$year}";
                $uploadResult = $this->cloudinary->uploadApi()->upload(
                    $file->getRealPath(),
                    [
                        'resource_type' => 'raw',
                        'folder' =>  $rel_upload_path,
                    ]
                );

                // Create resource record
                $resource = LessonResource::create([
                    'training_id' => $lesson->training_id,
                    'employer_user_id' => $user->id,
                    'lesson_id' => $lesson->id,
                    'title' => $request->title,
                    'file_name' => $file->getClientOriginalName(),
                    'file_type' => $file->getClientMimeType(),
                    'file_size' => $file->getSize(),
                    'file_url' => $uploadResult['secure_url']
                ]);
                info(['Lesson Created', $resource]);
                $resources[] = $resource;
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Lesson resources uploaded successfully',
                'data' => $resources
            ], Response::HTTP_CREATED);

        } catch (\Exception $e) {
            DB::rollBack();

            // Log the error
            \Log::error('Error uploading lesson resources: ' . $e->getMessage());

            return response()->json([
                "status" => false,
                'message' => 'Error uploading lesson resources',
                'error' =>"Fail to upload lesson resources"
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Training\LessonResource  $lessonResource
     * @return \Illuminate\Http\Response
     */
    public function destroy(LessonResource $lessonResource)
    {
        try {
            // Extract public_id from the file URL
            $publicId = $this->extractPublicIdFromUrl($lessonResource->file_url);

            // Delete from Cloudinary
            if ($publicId) {
                $this->cloudinary->uploadApi()->destroy($publicId);
            }

            // Delete from database
            $lessonResource->delete();

            return response()->json([
                'message' => 'Resource deleted successfully'
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            \Log::error('Error deleting lesson resource: ' . $e->getMessage());

            return response()->json([
                'message' => 'Error deleting resource',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Extract public_id from Cloudinary URL
     *
     * @param string $url
     * @return string|null
     */
    private function extractPublicIdFromUrl($url)
    {
        // Example URL: https://res.cloudinary.com/your-cloud/raw/upload/v1234567890/lesson-resources/lesson_resource_123456
        $pattern = '/lesson-resources\/lesson_resource_[a-zA-Z0-9]+/';
        if (preg_match($pattern, $url, $matches)) {
            return $matches[0];
        }
        return null;
    }
}

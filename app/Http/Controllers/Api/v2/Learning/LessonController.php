<?php

namespace App\Http\Controllers\Api\v2\Learning;

use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\Training\CroxxTraining;
use App\Models\Training\CroxxLesson;
use App\Http\Requests\CurrateLessonRequest;
use App\Models\Training\LessonResource;
use Cloudinary\Cloudinary;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use App\Traits\ApiResponseTrait;

class LessonController extends Controller
{
    use ApiResponseTrait;
    protected $cloudinary;

    public function __construct(Cloudinary $cloudinary)
    {
        $this->cloudinary = $cloudinary;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $user_type = $user->type;
        $per_page = $request->input('per_page', 25);
        $sort_by = $request->input('sort_by', 'created_at');
        $sort_dir = $request->input('sort_dir', 'desc');
        $search = $request->input('search');
        $tcode = $request->input('tcode');
        $archived = $request->input('archived');

        $archived = $archived == 'yes' ? true : ($archived == 'no' ? false : null);
        $training = CroxxTraining::where('code', $tcode)->first();

        $lessons = CroxxLesson::where('training_id', $training->id)
            ->when($user_type == 'employer', function($query) use ($training){
                $query->where('training_id', $training->id);
            })
            ->when($archived ,function ($query) use ($archived) {
            if ($archived !== null ) {
                if ($archived === true ) {
                    $query->whereNotNull('archived_at');
                } else {
                    $query->whereNull('archived_at');
                }
            }
        })
        ->with('resources')
        ->where( function($query) use ($search) {
            $query->where('title', 'LIKE', "%{$search}%");
        })
        ->orderBy($sort_by, $sort_dir);

        if ($per_page === 'all' || $per_page <= 0 ) {
            $results = $lessons->get();
            $lessons = new \Illuminate\Pagination\LengthAwarePaginator($results, $results->count(), -1);
        } else {
            $lessons = $lessons->paginate($per_page);
        }

        $response = collect([
            'status' => true,
            'message' => ""
        ])->merge($lessons);
        return response()->json($response, 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CurrateLessonRequest $request)
    {
        try {
            $user = $request->user();
            $validatedData = $request->validated();

            $validatedData['alias'] = Str::slug($validatedData['title']);
            CroxxTraining::findOrFail($validatedData['training_id']);

            $isLesson = CroxxLesson::where([
                'training_id' =>  $validatedData['training_id'],
                'alias' =>  $validatedData['alias']
            ])->exists();

            if ($isLesson) {
                return $this->badRequestResponse('services.lessons.exists');
            }

            DB::beginTransaction(); // Start DB Transaction

            if ($request->hasFile('video') && $request->file('video')->isValid()) {
                $file = $request->file('video');
                $extension = $file->extension();

                $filename = time() . '-' . Str::random(32) . ".$extension";
                $year = date('Y');
                $rel_upload_path = "CroxxVd/TRAINING/{$year}";

                try {
                    $result = $this->cloudinary->uploadApi()->upload(
                        $file->getRealPath(),
                        [
                            'folder' => $rel_upload_path,
                            'resource_type' => 'video',
                        ]
                    );

                    $validatedData['video_url'] = $result['secure_url'];
                } catch (\Exception $e) {

                    return $this->validationErrorResponse(
                        ['errors' => ['video' => [__('services.lessons.upload_error')]]],
                        'services.lessons.upload_error'
                    );

                    // return response()->json([
                    //     'status' => false,
                    //     'message' => 'The video failed to upload.',
                    //     'errors' => ['video' => ['The video failed to upload.']]
                    // ], 422);
                }
            }

            // Create Lesson and prepare resources
            $lesson = CroxxLesson::create($validatedData);

            // Ensure $files is an array before iterating
            $files = $request->file('files');

            if (is_array($files)) {
                foreach ($files as $file) {
                    $extension = $file->getClientOriginalExtension();
                    $filename = time() . '-' . Str::random(32) . ".$extension";
                    $year = date('Y');
                    $rel_upload_path  = "$user->id/TRAINING/{$year}";

                    $uploadResult = $this->cloudinary->uploadApi()->upload(
                        $file->getRealPath(),
                        [
                            'resource_type' => 'raw',
                            'folder' => $rel_upload_path,
                        ]
                    );

                    // Create resource record
                    LessonResource::create([
                        'training_id' => $validatedData['training_id'],
                        'employer_user_id' => $user->id,
                        'lesson_id' => $lesson->id,
                        'title' => $validatedData['title'], // Corrected title
                        'file_name' => $file->getClientOriginalName(),
                        'file_type' => $file->getClientMimeType(),
                        'file_size' => $file->getSize(),
                        'file_url' => $uploadResult['secure_url']
                    ]);
                }
            }

            DB::commit(); // Commit transaction
            $lesson->load('resources');
        
            return $this->successResponse(
                $lesson,
                'services.lessons.created',
                [],
                Response::HTTP_CREATED
            );

        } catch (\Exception $e) {
            DB::rollBack(); // Rollback on error
            \Log::error('Error creating lesson: ' . $e->getMessage());

            return $this->errorResponse(
                'services.lessons.create_error',
                ['error' => $e->getMessage()]
            );
        }
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request,$id)
    {
        $user = $request->user();

        if (is_numeric($id)) {
            $lesson = CroxxLesson::where('id', $id)->firstOrFail();
        } else {
            $lesson = CroxxLesson::where('alias', $id)->firstOrFail();
        }

        $lesson->resources;

        return response()->json([
            'status' => true,
            'message' => "",
            'data' => $lesson,
        ], 200);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(CurrateLessonRequest $request, $id)
    {
        $validatedData = $request->validated();
        $lesson = CroxxLesson::findOrFail($id);

        if (isset($validatedData['title'])) {
            $validatedData['alias'] = Str::slug($validatedData['title']);
        }

        if ($request->hasFile('video') && $request->file('video')->isValid()) {
            $file = $request->file('video');
            $extension = $file->extension();

            $filename = time() . '-' . Str::random(32);
            $filename = "{$filename}.$extension";
            $year = date('Y');
            $rel_upload_path = "CroxxVd/TRAINING/{$year}";

            try {
                $result = $this->cloudinary->uploadApi()->upload($file->getRealPath(), [
                    'folder' => $rel_upload_path, // Specify a folder
                    'resource_type' => 'video', // Specify the resource type as video
                ]);

                // Update with the newly uploaded file
                $validatedData['video_url'] = $result['secure_url'];
            } catch (\Exception $e) {
                return $this->validationErrorResponse(
                    ['errors' => ['video' => [__('services.lessons.upload_error')]]],
                    'services.lessons.upload_error'
                );
            }
        }

        $lesson->update($validatedData);

        return response()->json([
            'status' => true,
            'message' => "Lesson updated successfully.",
            'data' => $lesson,
        ], 200);
    }

    /**
     * Archive the specified resource from active list.
     *
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function archive($id)
    {
        $lesson = CroxxLesson::findOrFail($id);

        // $this->authorize('delete', [CroxxLesson::class, $lesson]);
        $lesson->archived_at = now();
        $lesson->save();

        return $this->successResponse(
            $lesson->fresh(),
            'services.lessons.archived'
        );
    }

    public function unarchive($id)
    {
        $lesson = CroxxLesson::findOrFail($id);

        // $this->authorize('delete', [CroxxLesson::class, $lesson]);
        $lesson->archived_at = null;
        $lesson->save();

        return $this->successResponse(
            $lesson->fresh(),
            'services.lessons.restored'
        );
    }
}

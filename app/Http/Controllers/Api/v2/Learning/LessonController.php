<?php

namespace App\Http\Controllers\Api\v2\Learning;

use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\Training\CroxxTraining;
use App\Models\Training\CroxxLesson;
use App\Http\Requests\CurrateLessonRequest;
use Cloudinary\Cloudinary;


class LessonController extends Controller
{
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
        $training = CroxxTraining::where('code', $tcode)->firstOrFail();

        $lessons = CroxxLesson::when($user_type == 'employer', function($query) use ($training){
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
            'message' => "Successful."
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
        $user = $request->user();
        $validatedData = $request->validated();

        $validatedData['alias'] = Str::slug($validatedData['title']);
        $training = CroxxTraining::findOrFail($validatedData['training_id']);

        $isLesson = CroxxLesson::where([
            'training_id' =>  $validatedData['training_id'],
            'alias' =>  $validatedData['alias']
        ])->exists();

        // if($isLesson){
        //     return response()->json([
        //         'status' => false,
        //         'message' => "Lesson already available",
        //     ], 400);
        // }

        if ($request->hasFile('cover_photo') && $request->file('cover_photo')->isValid()) {
            $file = $request->file('cover_photo');
            $extension = $file->extension();

            $filename = time() . '-' . Str::random(32);
            $filename = "{$filename}.$extension";
            $year = date('Y');
            $rel_upload_path  = "CroxxPH/TRAINING/{$year}";
            if (config('app.env') == 'local') {
                $rel_upload_path = "local/{$rel_upload_path}"; // dir for dev environment test uploads
            }

            // Delete previously uploaded file if any
            // if ($cv->photo) {
            //     $public_id = pathinfo($cv->photo, PATHINFO_FILENAME); // Extract public_id from URL
            //     info(['Public ID', $public_id]);
            //     $this->cloudinary->uploadApi()->destroy($public_id);
            // }

            $result = $this->cloudinary->uploadApi()->upload($file->getRealPath(), [
                'folder' => $rel_upload_path, // Specify a folder
            ]);

            // Update with the newly update file
            $validatedData['cover_photo'] = $result['secure_url'];
            if(!$training->cover_photo){
                $training->cover_photo  =  $result['secure_url'];
                $training->save();
            }
        }


        if ($request->hasFile('video') && $request->file('video')->isValid()) {
            $file = $request->file('video');
            $extension = $file->extension();

            $filename = time() . '-' . Str::random(32);
            $filename = "{$filename}.$extension";
            $year = date('Y');
            $rel_upload_path = "CroxxPH/TRAINING/{$year}";
            if (config('app.env') == 'local') {
                $rel_upload_path = "local/{$rel_upload_path}"; // dir for dev environment test uploads
            }

            try {
                $result = $this->cloudinary->uploadApi()->upload($file->getRealPath(), [
                    'folder' => $rel_upload_path, // Specify a folder
                    'resource_type' => 'video', // Specify the resource type as video
                ]);

                // Update with the newly uploaded file
                $validatedData['video_url'] = $result['secure_url'];
            } catch (\Exception $e) {

                return response()->json([
                    'status' => false,
                    'message' => 'The video failed to upload.',
                    'errors' => ['video' => ['The video failed to upload.']]
                ], 422);
            }
        }

        $lesson = CroxxLesson::create($validatedData);

        return response()->json([
            'status' => true,
            'message' => "",
            'data' => $lesson,
        ], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}

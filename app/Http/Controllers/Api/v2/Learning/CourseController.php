<?php

namespace App\Http\Controllers\Api\v2\Learning;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Employee;
use App\Http\Requests\TrainingRequest;
use App\Models\Training\CroxxTraining;
use App\Models\Assessment\EmployeeLearningPath;
use Cloudinary\Cloudinary;
use App\Libraries\OpenAIService;

class CourseController extends Controller
{
    protected $cloudinary;
    protected $openAIService;

    public function __construct(Cloudinary $cloudinary, OpenAIService $openAIService)
    {
        $this->cloudinary = $cloudinary;
        $this->openAIService = $openAIService;
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
        $archived = $request->input('archived');
        $department = $request->input('department');
        $datatable_draw = $request->input('draw'); // if any

        $archived = $archived == 'yes' ? true : ($archived == 'no' ? false : null);

        $training = CroxxTraining::when($user_type == 'employer', function($query) use ($user){
                $query->where('user_id', $user->id)
                      ->where('type', 'company');
            })
            ->when($department,function ($query) use ($department) {
                if ($department !== null  && is_numeric($department)) {
                   $query->where('department_id', $department);
                }
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
            $results = $training->get();
            $training = new \Illuminate\Pagination\LengthAwarePaginator($results, $results->count(), -1);
        } else {
            $training = $training->paginate($per_page);
        }

        $response = collect([
            'status' => true,
            'data' => $training,
            'message' => ""
        ]);
        return response()->json($response, 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(TrainingRequest $request)
    {
        $user = $request->user();
        $validatedData = $request->validated();
        $validatedData['code'] = $user->id . md5(time());

        $validatedData['employer_id'] = $user->id;
        $validatedData['user_id'] = $user->id;


        if ($request->hasFile('cover_photo') && $request->file('cover_photo')->isValid()) {
            $file = $request->file('cover_photo');
            $extension = $file->extension();

            $filename = time() . '-' . Str::random(32);
            $filename = "{$filename}.$extension";
            $year = date('Y');
            $rel_upload_path  = "CroxxPH/TRAINING/{$year}";

            $result = $this->cloudinary->uploadApi()->upload($file->getRealPath(), [
                'folder' => $rel_upload_path, // Specify a folder
            ]);

            $validatedData['cover_photo'] = $result['secure_url'];
        }

        $training = CroxxTraining::create($validatedData);

        return response()->json([
            'status' => true,
            'message' => "",
            'data' => $training,
        ], 201);
    }

    public function show(Request $request, $id)
    {
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function suggest(Request $request, $id)
    {
        $training = CroxxTraining::findOrFail($id);
        $training->department;

        $course =[
            'department' =>  $training->department?->job_code,
            'title' =>  $training->title,
            'level' =>  $training->experience_level,
        ];

        $lessons = $this->openAIService->currateCourseLessons($course);

        return response()->json([
            'status' => true,
            'message' => "",
            'data' => $lessons,
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(TrainingRequest $request, $id)
    {
        $validatedData = $request->validated();
        $training = CroxxTraining::findOrFail($id);

        $training->update($validatedData);

        return response()->json([
            'status' => true,
            'message' => "Training updated successfully.",
            'data' => CroxxTraining::find($training->id)
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
        $training = CroxxTraining::findOrFail($id);

        $this->authorize('delete', [CroxxTraining::class, $training]);

        $training->archived_at = now();
        $training->save();

        return response()->json([
            'status' => true,
            'message' => "Training archived successfully.",
            'data' => CroxxTraining::find($training->id)
        ], 200);
    }

    /**
     * Publish Training.
     *
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function publish($id)
    {
        $training = CroxxTraining::findOrFail($id);

        // $this->authorize('update', [CroxxTraining::class, $training]);

        if ($training->is_published != true) {
            $training->is_published = true;
            $training->save();
            // Send Push notification
            // $notification = new Notification();
            // $notification->user_id = $training->user_id;
            // $notification->action = "/Trainings";
            // $notification->title = 'Training Published';
            // $notification->message = " Your Training <b>$training->title</b> has been published.";
            // $notification->save();
            // event(new NewNotification($notification->user_id,$notification));
            // // send email notification
            // if ($training->user->email) {
            //     if (config('mail.queue_send')) {
            //         Mail::to($training->user->email)->queue(new TrainingPublished($training));
            //     } else {
            //         Mail::to($training->user->email)->send(new TrainingPublished($training));
            //     }
            // }
        }

        return response()->json([
            'status' => true,
            'message' => "Training published successfully.",
            'data' => CroxxTraining::find($training->id)
        ], 200);
    }

    /**
     * Unpublish Training.
     *
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function unpublish($id)
    {
        $training = CroxxTraining::findOrFail($id);

        $this->authorize('update', [CroxxTraining::class, $training]);

        $training->is_published = false;
        $training->save();

        return response()->json([
            'status' => true,
            'message' => "Training unpublished successfully.",
            'data' => CroxxTraining::find($training->id)
        ], 200);
    }


    /**
     * Unarchive the specified resource from archived storage.
     *
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function unarchive($id)
    {
        $training = CroxxTraining::findOrFail($id);

        // $this->authorize('delete', [CroxxTraining::class, $training]);

        $training->archived_at = null;
        $training->save();

        return response()->json([
            'status' => true,
            'message' => "Training unarchived successfully.",
            'data' => CroxxTraining::find($training->id)
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $training = CroxxTraining::findOrFail($id);

        $this->authorize('delete', [CroxxTraining::class, $training]);

        $name = $training->name;
        // check if the record is linked to other records
        $relatedRecordsCount = related_records_count(CroxxTraining::class, $training);

        if ($relatedRecordsCount <= 0) {
            $training->delete();
            return response()->json([
                'status' => true,
                'message' => "Training deleted successfully.",
            ], 200);
        } else {
            return response()->json([
                'status' => false,
                'message' => "The \"{$name}\" record cannot be deleted because it is associated with {$relatedRecordsCount} other record(s). You can archive it instead.",
            ], 400);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function destroyMultiple(Request $request)
    {
        $ids = $request->input('ids');
        $valid_ids = [];
        $deleted_count = 0;
        if (is_array($ids)) {
            foreach ($ids as $id) {
                $training = CroxxTraining::find($id);
                if ($training) {
                    $this->authorize('delete', [CroxxTraining::class, $training]);
                    $valid_ids[] = $training->id;
                }
            }
        }
        $valid_ids = collect($valid_ids);
        if ($valid_ids->isNotEmpty()) {
            foreach ($valid_ids as $id) {
                $training = CroxxTraining::find($id);
                // check if the record is linked to other records
                $relatedRecordsCount = related_records_count(CroxxTraining::class, $training);
                if ($relatedRecordsCount <= 0) {
                    $training->delete();
                    $deleted_count++;
                }
            }
        }

        return response()->json([
            'status' => true,
            'message' => "{$deleted_count} Trainings deleted successfully.",
        ], 200);
    }

    public function courses(Request $request)
    {
        $user = $request->user();
        $per_page = $request->input('per_page', 12);
        $search = $request->input('search');
        $current_company = Employee::where('id', $user->default_company_id)
                             ->where('user_id', $user->id)->with('supervisor')->firstOrFail();

        if(!$current_company->supervisor){
            return response()->json([
                'status' => false,
                'message' => 'Unautourized Access'
            ], 401);
        }

        $trainings = CroxxTraining::where('employer_id', $current_company->employer_id)
                    ->when($search,function($query) use ($search) {
                        $query->where('title', 'LIKE', "%{$search}%");
                    })
                    ->latest();
        if ($per_page === 'all' || $per_page <= 0 ) {
            $results = $trainings->get();
            $trainings = new \Illuminate\Pagination\LengthAwarePaginator($results, $results->count(), -1);
        } else {
            $trainings = $trainings->paginate($per_page);
        }

        $response = collect([
            'status' => true,
            'data' => $trainings,
            'message' => ""
        ]);
        return response()->json($response, 200);
    }

    public function progress(Request $request){
        $employer = $request->user();
        $per_page = $request->input('per_page', 25);
        $sort_by = $request->input('sort_by', 'current_lesson');
        $sort_dir = $request->input('sort_dir', 'desc');

        $paths = EmployeeLearningPath::where('employer_user_id', $employer->id)
                    ->with('employee')
                    ->orderBy($sort_by, $sort_dir);

        if ($per_page === 'all' || $per_page <= 0 ) {
            $results = $paths->get();
            $paths = new \Illuminate\Pagination\LengthAwarePaginator($results, $results->count(), -1);
        } else {
            $paths = $paths->paginate($per_page);
        }


        return response()->json([
            'status' => true,
            'data' => $paths,
            'message' => ''
        ], 200);
    }

}

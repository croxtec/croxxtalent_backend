<?php

namespace App\Http\Controllers\Api\v2\Learning;

use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\Training\CroxxTraining;
use App\Models\Training\CroxxLesson;
use App\Http\Requests\CurrateLessonRequest;

class LessonController extends Controller
{
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

        $validatedData['training_id'] = $validatedData['training_id'];
        $validatedData['alias'] = Str::slug($validatedData['title']);

        $isLesson = CroxxLesson::where([
            'training_id' =>  $validatedData['training_id'],
            'alias' =>  $validatedData['alias']
        ])->exists();

        if($isLesson){
            return response()->json([
                'status' => false,
                'message' => "Lesson already available",
            ], 400);
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

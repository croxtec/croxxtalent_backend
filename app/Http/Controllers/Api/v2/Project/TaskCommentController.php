<?php

namespace App\Http\Controllers\Api\v2\Project;

use App\Http\Controllers\Controller;
use App\Models\Project\GoalComment;
use App\Models\Project\Project;
use App\Models\Project\ProjectGoal;
use Illuminate\Http\Request;

class TaskCommentController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $user_type = $user->type;
        $per_page = $request->input('per_page', 'all');
        $sort_by = $request->input('sort_by', 'created_at');
        $sort_dir = $request->input('sort_dir', 'desc');
        $search = $request->input('search');
        $archived = $request->input('archived');
        $pcode = $request->input('pcode');

        $archived = $archived == 'yes' ? true : ($archived == 'no' ? false : null);
        $goal = ProjectGoal::where('id', $pcode)->first();

        $comments = GoalComment::when($user_type == 'employer', function($query) use ($goal){
                $query->where('goal_id', $goal->id);
            })
            ->orderBy($sort_by, $sort_dir);

        if ($per_page === 'all' || $per_page <= 0 ) {
            $results = $comments->get();
            $comments = new \Illuminate\Pagination\LengthAwarePaginator($results, $results->count(), -1);
        } else {
            $comments = $comments->paginate($per_page);
        }

        $response = collect([
            'status' => true,
            'data' => $comments,
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
    public function store(Request $request)
    {
        $user = $request->user();

        $validatedData  = $request->validate([
            'comment' => 'required|between:3,256',
            'employee_id' => 'required',
            'goal_id' => 'required|exists:project_goals,id'
        ]);

        $comments = GoalComment::create($validatedData);

        return response()->json([
            'status' => true,
            'message' => "",
            'data' => $comments,
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

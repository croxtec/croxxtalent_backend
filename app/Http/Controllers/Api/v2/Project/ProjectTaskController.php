<?php

namespace App\Http\Controllers\Api\v2\Project;

use App\Http\Controllers\Controller;
use App\Models\Project\Project;
use App\Models\Project\ProjectGoal;
use Illuminate\Http\Request;

class ProjectTaskController extends Controller
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
        $archived = $request->input('archived');
        $department = $request->input('department');
        $pcode = $request->input('pcode');

        $archived = $archived == 'yes' ? true : ($archived == 'no' ? false : null);
        $project = Project::where('code', $pcode)->firstOrFail();

        $milestone = ProjectGoal::when($user_type == 'employer', function($query) use ($project){
                $query->where('project_id', $project->id);
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
                $query->where('milestone_name', 'LIKE', "%{$search}%");
            })
            ->orderBy($sort_by, $sort_dir);

        if ($per_page === 'all' || $per_page <= 0 ) {
            $results = $milestone->get();
            $milestone = new \Illuminate\Pagination\LengthAwarePaginator($results, $results->count(), -1);
        } else {
            $milestone = $milestone->paginate($per_page);
        }

        $response = collect([
            'status' => true,
            'data' => $milestone,
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
    public function store(MilestoneRequest $request)
    {
        $user = $request->user();
        $validatedData = $request->validated();
        $validatedData['code'] = $user->id . md5(time());

        $validatedData['employer_user_id'] = $user->id;
        // $validatedData['user_id'] = $user->id;

        $milestone = Milestone::create($validatedData);

        return response()->json([
            'status' => true,
            'message' => "",
            'data' => $milestone,
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

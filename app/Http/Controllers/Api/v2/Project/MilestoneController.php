<?php

namespace App\Http\Controllers\Api\v2\Project;

use App\Http\Controllers\Controller;
use App\Http\Requests\MilestoneRequest;
use App\Models\Project\Milestone;
use App\Models\Project\Project;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MilestoneController extends Controller
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
        $project = Project::where('code', $pcode)->first();

        $milestone = Milestone::where('project_id', $project->id)
            ->when($user_type == 'employer',function($query) use ($user){
                $query->where('employer_user_id', $user->id);
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
    public function show(Request $request, $id)
    {
        $user = auth()->user();
        $pcode = $request->input('pcode');
        // $employerId =  $user->id;

        $milestone = Milestone::where('id', $id)->firstOrFail();
        $project = Project::where('code', $pcode)->first();
        $project_milestones =  Milestone::where('project_id', $project->id)->get();

        return response()->json([
            'status' => true,
            'message' => "",
            'data' => compact('milestone', 'project_milestones'),
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
 public function update(MilestoneRequest $request, $id) : JsonResponse
{
    try {
        $milestone = Milestone::findOrFail($id);
        $validatedData = $request->validated();

        $milestone->update($validatedData);

        return response()->json([
            'status' => true,
            'message' => __('services.projects.milestone_updated'),
            'data' => $milestone->fresh(),
        ], 200);

    } catch (ModelNotFoundException $e) {
        return response()->json([
            'status' => false,
            'message' => __('services.projects.milestone_not_found'),
        ], 404);
    } catch (\Exception $e) {
        return response()->json([
            'status' => false,
            'message' => __('services.projects.milestone_update_error'),
            'error' => config('app.debug') ? $e->getMessage() : null
        ], 500);
    }
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

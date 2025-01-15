<?php

namespace App\Http\Controllers\Api\v2\Project;

use App\Http\Controllers\Controller;
use App\Http\Requests\TaskGoalRequest;
use App\Models\Project\AssignedEmployee;
use App\Models\Project\GoalCompetency;
use App\Models\Project\Milestone;
use App\Models\Project\Project;
use App\Models\Project\ProjectGoal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProjectGoalController extends Controller
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
                $query->where('title', 'LIKE', "%{$search}%");
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
    public function store(TaskGoalRequest $request)
    {
        $user = $request->user();
        $validatedData = $request->validated();
        $validatedData['code'] = $user->id . md5(time());

        $validatedData['employer_user_id'] = $user->id;
        // $validatedData['user_id'] = $user->id;

        if (isset($validatedData['milestone']) &&  strlen($validatedData['milestone']) > 2) {
            $milestone = Milestone::firstOrCreate([
                'employer_user_id' => $validatedData['employer_user_id'],
                'project_id' => $validatedData['project_id'],
                'milestone_name' => $validatedData['milestone']
            ]);
            $validatedData['milestone_id'] = $milestone->id;
        }

        $task = ProjectGoal::create($validatedData);

        return response()->json([
            'status' => true,
            'message' => "",
            'data' => $task,
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

    public function addCompetency($goalId, Request $request)
    {
        $data = $request->validate([
            'competency_ids' => 'required|array', // Accept an array of competency IDs
            'competency_ids.*' => 'integer|exists:department_mappings,id', // Validate each item in the array
        ]);

        $competencies = [];
        foreach ($data['competency_ids'] as $competencyId) {
            $competencies[] = [
                'goal_id' => $goalId,
                'competency_id' => $competencyId,
                'created_at' => now(),
                'updated_at' => now(),
            ];
            // $competency = GoalCompetency::create($data);
        }

        DB::table('goal_competencies')->insert($competencies);

        return response()->json([
            'message' => 'Competency added successfully!',
            'data' => $competencies,
        ], 201);
    }

    // Remove Competency from a Goal
    public function removeCompetency($goalId, $competencyId)
    {
        $competency = GoalCompetency::where('goal_id', $goalId)
            ->where('competency_id', $competencyId)
            ->first();

        if (!$competency) {
            return response()->json(['message' => 'Competency not found.'], 404);
        }

        $competency->delete();

        return response()->json(['message' => 'Competency removed successfully.']);
    }

    // Assign Employee to a Goal
    public function assignEmployee($goal, Request $request)
    {
        $user = auth()->user();
        return $goal;
        $data = $request->validate([
            'employee_ids' => 'required|array', // Accept an array of employee IDs
            'employee_ids.*' => 'integer|exists:employees,id', // Validate each item in the array
            // 'assigned_by' => 'required|integer|exists:users,id',
        ]);

        $assignments = [];
        foreach ($data['employee_ids'] as $employeeId) {
            $assignments[] = [
                'goal_id' => $goal,
                'employee_id' => $employeeId,
                // 'assigned_by' =>  $user->id,
                'assigned_at' => now(),
            ];
            // $assignment = AssignedEmployee::create($data);
        }

        DB::table('goal_assigned_employees')->insert($assignments);


        return response()->json([
            'message' => 'Employee assigned successfully!',
            'data' => $assignment,
        ], 201);
    }

    // Remove Assigned Employee from a Goal
    public function removeEmployee($goalId, $employeeId)
    {

        $assignment = AssignedEmployee::where('goal_id', $goalId)
            ->where('employee_id', $employeeId)
            ->first();

        if (!$assignment) {
            return response()->json(['message' => 'Assignment not found.'], 404);
        }

        $assignment->delete();

        return response()->json(['message' => 'Employee unassigned successfully.']);
    }
}

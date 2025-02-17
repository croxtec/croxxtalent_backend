<?php

namespace App\Http\Controllers\Api\v2\Project;

use App\Http\Controllers\Controller;
use App\Http\Requests\TaskGoalRequest;
use App\Http\Resources\EmployeeSummaryResource;
use App\Models\Project\AssignedEmployee;
use App\Models\Project\GoalCompetency;
use App\Models\Project\Milestone;
use App\Models\Project\Project;
use App\Models\Project\ProjectGoal;
use Illuminate\Database\Eloquent\ModelNotFoundException;
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
        $code = $request->input('pcode');
        $milestone = $request->input('milestone');

        $archived = $archived == 'yes' ? true : ($archived == 'no' ? false : null);
        $project = Project::where('code', $code)->first();

        $project_goals = ProjectGoal::where('project_id', $project->id)
            ->when($user_type == 'employer',function($query) use ($user){
                $query->where('employer_user_id', $user->id);
            })
            ->when($milestone, function($query) use ($milestone) {
                if ($milestone !== null && is_numeric($milestone)) {
                    $query->where('milestone_id', $milestone);
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
            ->with('milestone', 'assigned.employee')
            ->orderBy($sort_by, $sort_dir);

        if ($per_page === 'all' || $per_page <= 0 ) {
            $results = $project_goals->get();
            $project_goals = new \Illuminate\Pagination\LengthAwarePaginator($results, $results->count(), -1);
        } else {
            $project_goals = $project_goals->paginate($per_page);
        }

        $response = collect([
            'status' => true,
            'data' => $project_goals,
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

        $validatedData['employer_user_id'] = $user->id;

        // Milestone handling
        if (!empty($validatedData['milestone'])) {
            $milestone = Milestone::firstOrCreate([
                'employer_user_id' => $validatedData['employer_user_id'],
                'project_id' => $validatedData['project_id'],
                'milestone_name' => $validatedData['milestone']
            ]);
            $validatedData['milestone_id'] = $milestone->id;
        }

        $task = ProjectGoal::create($validatedData);

        // Handle assigned employees using createOrUpdate to avoid duplicates
        if(!empty($validatedData['assigned'])) {
            foreach($validatedData['assigned'] as $employeeId) {
                AssignedEmployee::updateOrCreate(
                    [
                        'goal_id' => $task->id,
                        'employee_id' => $employeeId,
                    ],
                    [
                        'assigned_at' => now(),
                    ]
                );
            }
        }

        return response()->json([
            'status' => true,
            'message' => 'Task created successfully',
            'data' => $task->load('assigned.employee', 'milestone'),
        ], 201);
    }

    public function addCompetency($goalId, Request $request)
    {
        $data = $request->validate([
            'competency_ids' => 'required|array',
            'competency_ids.*' => 'integer|exists:department_mappings,id',
        ]);

        foreach ($data['competency_ids'] as $competencyId) {
            GoalCompetency::updateOrCreate(
                [
                    'goal_id' => $goalId,
                    'competency_id' => $competencyId,
                ],
                [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        return response()->json([
            'message' => 'Competencies added successfully!',
            'data' => GoalCompetency::where('goal_id', $goalId)->get(),
        ], 201);
    }

    public function assignEmployee($goalId, Request $request)
    {
        $data = $request->validate([
            'employee_ids' => 'required|array',
            'employee_ids.*' => 'integer|exists:employees,id',
        ]);

        foreach ($data['employee_ids'] as $employeeId) {
            AssignedEmployee::updateOrCreate(
                [
                    'goal_id' => $goalId,
                    'employee_id' => $employeeId,
                ],
                [
                    'assigned_at' => now(),
                ]
            );
        }

        return response()->json([
            'message' => 'Employees assigned successfully!',
            'data' => AssignedEmployee::where('goal_id', $goalId)
                ->with('employee')
                ->get(),
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

        $goal = ProjectGoal::where('id', $id)
            ->with(['assigned.employee', 'comments.employee'])
            ->firstOrFail();

        // Transform assigned employees
        $goal->assigned_employees = $goal->assigned->pluck('employee');
        // EmployeeSummaryResource::collection(
        //     $goal->assigned->pluck('employee')
        // );
        $goal->assigned_employees_count = $goal->assigned->count();

        // Transform comments
        // $goal->comments = $goal->comments->map(function ($item) {
        //     $item->employee = EmployeeSummaryResource::collection($item->employee);
        // });
        $goal->comments;
        $goal->milestone;
        $goal->competencies;
        $goal->activities;

        unset($goal->assigned);

        return response()->json([
            'status' => true,
            'data' => $goal,
            'message' => "",
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(TaskGoalRequest $request, $id)
    {
        try {
            $projectGoal = ProjectGoal::findOrFail($id);

            // if ($projectGoal->employer_user_id !== $request->user()->id) {
            //     return response()->json([
            //         'status' => false,
            //         'message' => 'Unauthorized to update this goal',
            //     ], 403);
            // }

            $validatedData = $request->validated();

            $projectGoal->update($validatedData);

            return response()->json([
                'status' => true,
                'message' => 'Task updated successfully',
                'data' => $projectGoal->fresh(),
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Task not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to update goal',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
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

    public function archive($id)
    {
        $projectGoal = ProjectGoal::findOrFail($id);
        // $this->authorize('delete', [Project::class, $project]);

        $projectGoal->archived_at = now();
        $projectGoal->save();

        return response()->json([
            'status' => true,
            'message' => "Project archived successfully.",
            'data' => ProjectGoal::find($projectGoal->id)
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
        $projectGoal = ProjectGoal::findOrFail($id);

        // $this->authorize('delete', [Project::class, $project]);

        $projectGoal->archived_at = null;
        $projectGoal->save();

        return response()->json([
            'status' => true,
            'message' => "Project unarchived successfully.",
            'data' => ProjectGoal::find($projectGoal->id)
        ], 200);
    }

}

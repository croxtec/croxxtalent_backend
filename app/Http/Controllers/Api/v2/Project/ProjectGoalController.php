<?php

namespace App\Http\Controllers\Api\v2\Project;

use App\Http\Controllers\Controller;
use App\Http\Requests\TaskGoalRequest;
use App\Http\Resources\EmployeeSummaryResource;
use App\Models\Project\AssignedEmployee;
use App\Models\Project\GoalActivity;
use App\Models\Project\GoalCompetency;
use App\Models\Project\Milestone;
use App\Models\Project\Project;
use App\Models\Project\ProjectGoal;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Traits\ApiResponseTrait;

class ProjectGoalController extends Controller
{

    use ApiResponseTrait;

    public function __construct()
    {
        // Apply basic project access middleware to all methods
        $this->middleware('project.access');

        // Apply team lead requirement to management operations
        $this->middleware('project.access:lead')->only([
            // 'store',
            // 'update',
            'archive',
            'unarchive',
            'addCompetency',
            'removeCompetency',
            'assignEmployee',
            'removeEmployee'
        ]);
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
        $code = $request->input('pcode');
        $milestone = $request->input('milestone');
        $status = $request->input('status');

        $archived = $archived == 'yes' ? true : ($archived == 'no' ? false : null);
        $project = Project::where('code', $code)->first();

        // if($user_type == 'talent'){
        //     $validation_result = validateProjectAccess($user, $project);

        //     if ($validation_result !== true) {
        //         return $validation_result;
        //     }
        // }

        $project_goals = ProjectGoal::where('project_id', $project->id)
            // ->when($user_type == 'employer',function($query) use ($user){
            //     $query->where('employer_user_id', $user->id);
            // })
            ->when($milestone, function($query) use ($milestone) {
                if ($milestone !== null && is_numeric($milestone)) {
                    $query->where('milestone_id', $milestone);
                }
            })
            ->when($status, function($query) use ($status) {
                $query->where('status', $status);
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

        // $project = Project::where('id',  $validatedData['project_id'])->first();

        // if($user->user_type === 'talent'){
        //     $validation_result = validateProjectAccess($user, $project);

        //     if ($validation_result !== true) {
        //         return $validation_result;
        //     }
        // }

        // Milestone handling
        if (!empty($validatedData['milestone'])) {
            $milestone = Milestone::firstOrCreate([
                'employer_user_id' => $validatedData['employer_user_id'],
                'project_id' => $validatedData['project_id'],
                'milestone_name' => $validatedData['milestone']
            ]);
            $validatedData['milestone_id'] = $milestone->id;
        }

        $validatedData['code'] = $this->generateTaskCode($validatedData['project_id'], $validatedData['milestone_id'] ?? null);

        $task = ProjectGoal::create($validatedData);

        // Track goal creation activity
        GoalActivity::create([
            'goal_id' => $task->id,
            'activity_type' => 'create',
            'description' => __('services.activities.goal_created', ['title' => $task->title]),
            'performed_by' => $user->id
        ]);

        // Handle assigned employees
        if(!empty($validatedData['assigned'])) {
            foreach($validatedData['assigned'] as $employeeId) {
                AssignedEmployee::updateOrCreate(
                    ['goal_id' => $task->id, 'employee_id' => $employeeId],
                    ['assigned_at' => now()]
                );

                GoalActivity::create([
                    'goal_id' => $task->id,
                    'activity_type' => 'employee_assign',
                    'description' => __('services.activities.employee_assigned'),
                    'performed_by' => $user->id
                ]);
            }
        }

        return $this->successResponse(
            $task->load('assigned.employee', 'milestone'),
            'services.goals.created',
            [],
            Response::HTTP_CREATED
        );
    }

    private function generateTaskCode($projectId, $milestoneId = null)
    {
        $project = Project::find($projectId);

        if (!$project) {
            throw new \Exception("Project not found.");
        }

        $projectTitle = strtoupper($project->title);
        $words = explode(' ', preg_replace('/[^a-zA-Z0-9 ]/', '', $projectTitle));
        $prefix = count($words) > 1
            ? substr($words[0], 0, 1) . substr($words[1], 0, 1)
            : substr($words[0], 0, 2); // Use first two letters if only one word

        $projectPrefix = strtoupper($prefix) . '-' . str_pad($projectId, 3, '0', STR_PAD_LEFT);

        // Count existing tasks under the same project/milestone
        $taskCount = ProjectGoal::where('project_id', $projectId)
            ->when($milestoneId, function ($query) use ($milestoneId) {
                return $query->where('milestone_id', $milestoneId);
            })
            ->count() + 1;

        // Generate unique task code (e.g., WD-001-T001)
        return "{$projectPrefix}-T" . str_pad($taskCount, 3, '0', STR_PAD_LEFT);
    }

    public function addCompetency($goalId, Request $request)
    {
        $user = $request->user();

        // $goal = ProjectGoal::where('id', $goalId)->first();
        // $project = $goal->project;

        // if($user->user_type === 'talent'){
        //     $validation_result = validateProjectAccess($user, $project);

        //     if ($validation_result !== true) {
        //         return $validation_result;
        //     }
        // }

        $data = $request->validate([
            'competency_ids' => 'required|array',
            'competency_ids.*' => 'integer|exists:department_mappings,id',
        ]);

        foreach ($data['competency_ids'] as $competencyId) {
            GoalCompetency::updateOrCreate(
                ['goal_id' => $goalId, 'competency_id' => $competencyId],
                ['created_at' => now(), 'updated_at' => now()]
            );
        
            GoalActivity::create([
                'goal_id' => $goalId,
                'activity_type' => 'add_competency',
                'description' => __('services.activities.competency_added'),
                'performed_by' => $user->id
            ]);
        }
        
        return $this->successResponse(
            GoalCompetency::where('goal_id', $goalId)->get(),
            'services.goals.competencies_added',
            [],
            Response::HTTP_CREATED
        );
    }

    public function assignEmployee($goalId, Request $request)
    {
        $user = $request->user();

        // $goal = ProjectGoal::where('id', $goalId)->first();
        // $project = $goal->project;

        // if($user->user_type === 'talent'){
        //     $validation_result = validateProjectAccess($user, $project);

        //     if ($validation_result !== true) {
        //         return $validation_result;
        //     }
        // }

        $data = $request->validate([
            'employee_ids' => 'required|array',
            'employee_ids.*' => 'integer|exists:employees,id',
        ]);

        foreach ($data['employee_ids'] as $employeeId) {
            AssignedEmployee::updateOrCreate(
                ['goal_id' => $goalId, 'employee_id' => $employeeId],
                ['assigned_at' => now()]
            );
        
            GoalActivity::create([
                'goal_id' => $goalId,
                'activity_type' => 'employee_assign',
                'description' => __('services.activities.employee_assigned'),
                'performed_by' => $user->id
            ]);
        }
        
        return $this->successResponse(
            AssignedEmployee::where('goal_id', $goalId)->with('employee')->get(),
            'services.goals.employees_assigned',
            [],
            Response::HTTP_CREATED
        );
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

        $project = $goal->project;

        // if($user->user_type === 'talent'){
        //     $validation_result = validateProjectAccess($user, $project);

        //     if ($validation_result !== true) {
        //         return $validation_result;
        //     }
        // }
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
            $user = $request->user();
            $projectGoal = ProjectGoal::findOrFail($id);

            $project = $projectGoal->project;

            // if($user->user_type === 'talent'){
            //     $validation_result = validateProjectAccess($user, $project);

            //     if ($validation_result !== true) {
            //         return $validation_result;
            //     }
            // }

            $validatedData = $request->validated();

            // Get original values for activity tracking
            $originalValues = $projectGoal->getOriginal();

            $projectGoal->update($validatedData);

            // Track goal update activity
            $changedFields = [];
            foreach ($validatedData as $key => $value) {
                if (isset($originalValues[$key]) && $originalValues[$key] != $value) {
                    $changedFields[] = $key;
                }
            }

            if (!empty($changedFields)) {
                GoalActivity::create([
                    'goal_id' => $projectGoal->id,
                    'activity_type' => 'update',
                    'description' => __('services.activities.goal_updated', [
                        'fields' => implode(', ', $changedFields)
                    ]),
                    'performed_by' => $user->id
                ]);
            }
        
            return $this->successResponse(
                $projectGoal->fresh(),
                'services.goals.updated'
            );
        
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse(
                'services.goals.not_found',
                [],
                Response::HTTP_NOT_FOUND
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'services.goals.update_error',
                ['error' => config('app.debug') ? $e->getMessage() : null],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    // Remove Competency from a Goal
    public function removeCompetency($goalId, $competencyId)
    {
        $user = auth()->user();

        // $goal = ProjectGoal::where('id', $goalId)->first();
        // $project = $goal->project;

        // if($user->user_type === 'talent'){
        //     $validation_result = validateProjectAccess($user, $project);

        //     if ($validation_result !== true) {
        //         return $validation_result;
        //     }
        // }

        $competency = GoalCompetency::where('goal_id', $goalId)
            ->where('competency_id', $competencyId)
            ->first();

        if (!$competency) {
            return $this->notFoundResponse('services.goals.competency_not_found');
        }

        $competency->delete();

        // Track competency removal activity
        GoalActivity::create([
            'goal_id' => $goalId,
            'activity_type' => 'remove_competency',
            'description' => __('services.activities.competency_removed'),
            'performed_by' => $user->id
        ]);
        
        return $this->successResponse(
            null,
            'services.goals.competency_removed'
        );
    }

    // Remove Assigned Employee from a Goal
    public function removeEmployee($goalId, $employeeId)
    {
        $user = auth()->user();
        $goal = ProjectGoal::where('id', $goalId)->first();
        // $goal = ProjectGoal::where('id', $goalId)->first();
        // $project = $goal->project;

        // if($user->user_type === 'talent'){
        //     $validation_result = validateProjectAccess($user, $project);

        //     if ($validation_result !== true) {
        //         return $validation_result;
        //     }
        // }

        $assignment = AssignedEmployee::where('goal_id', $goalId)
            ->where('employee_id', $employeeId)
            ->first();

        if (!$assignment) {
            return $this->notFoundResponse('services.goals.assignment_not_found');
        }

        $assignment->delete();

        // Track employee removal activity
        GoalActivity::create([
            'goal_id' => $goalId,
            'activity_type' => 'employee_remove',
            'description' => __('services.activities.employee_removed'),
            'performed_by' => $user->id
        ]);
        
        return $this->successResponse(
            null,
            'services.goals.employee_unassigned'
        );
    }

    public function archive($id)
    {
        $user = auth()->user();
        $projectGoal = ProjectGoal::findOrFail($id);
        // $this->authorize('delete', [Project::class, $project]);

        $project = $projectGoal->project;

        // if($user->user_type === 'talent'){
        //     $validation_result = validateProjectAccess($user, $project);

        //     if ($validation_result !== true) {
        //         return $validation_result;
        //     }
        // }

        $projectGoal->archived_at = now();
        $projectGoal->save();

        // Track archiving activity
        GoalActivity::create([
            'goal_id' => $projectGoal->id,
            'activity_type' => 'archive',
            'description' => __('services.activities.goal_archived'),
            'performed_by' => $user->id
        ]);
        
        return $this->successResponse(
            ProjectGoal::find($projectGoal->id),
            'services.goals.archived'
        );
    }


    /**
     * Unarchive the specified resource from archived storage.
     *
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function unarchive($id)
    {
        $user = auth()->user();
        $projectGoal = ProjectGoal::findOrFail($id);

        // $this->authorize('delete', [Project::class, $project]);

        // $project = $projectGoal->project;

        // if($user->user_type === 'talent'){
        //     $validation_result = validateProjectAccess($user, $project);

        //     if ($validation_result !== true) {
        //         return $validation_result;
        //     }
        // }

        $projectGoal->archived_at = null;
        $projectGoal->save();

        // Track unarchiving activity
        GoalActivity::create([
            'goal_id' => $projectGoal->id,
            'activity_type' => 'archive',
            'description' => __('services.activities.goal_restored'),
            'performed_by' => $user->id
        ]);
        
        return $this->successResponse(
            ProjectGoal::find($projectGoal->id),
            'services.goals.restored'
        );
    }
}

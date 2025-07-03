<?php

namespace App\Http\Controllers\Api\v2\Project;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProjectRequest;
use App\Models\Employee;
use App\Models\Project\Project;
use App\Models\Project\ProjectTeam;
use App\Services\DepartmentPerformanceService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Helpers\ProjectNotificationHelper;
use App\Traits\ApiResponseTrait;

class ProjectController extends Controller
{

    protected $teamCalculator;
    use ApiResponseTrait;

    public function __construct(DepartmentPerformanceService $teamCalculator)
    {
        $this->teamCalculator = $teamCalculator;
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
         $datatable_draw = $request->input('draw');

         $archived = $archived == 'yes' ? true : ($archived == 'no' ? false : null);

         $projects = Project::when($user_type == 'employer', function ($query) use ($user) {
                 $query->where('employer_user_id', $user->id);
             })
             ->when($department, function ($query) use ($department) {
                 if ($department !== null && is_numeric($department)) {
                     $query->where('department_id', $department);
                 }
             })
             ->when($archived, function ($query) use ($archived) {
                 if ($archived !== null) {
                     if ($archived === true) {
                         $query->whereNotNull('archived_at');
                     } else {
                         $query->whereNull('archived_at');
                     }
                 }
             })
             ->when($request->start_date && $request->end_date, function ($query) use ($request) {
                $startDate = Carbon::parse($request->start_date);
                $endDate = Carbon::parse($request->end_date);
                $query->whereBetween('created_at', [$startDate, $endDate]);
            })
             ->where(function ($query) use ($search) {
                 $query->where('title', 'LIKE', "%{$search}%");
             })
             ->with(['department'])
             ->withCount('team')
             ->orderBy($sort_by, $sort_dir);

         if ($per_page === 'all' || $per_page <= 0) {
             $results = $projects->get();
             $projects = new \Illuminate\Pagination\LengthAwarePaginator($results, $results->count(), -1);
         } else {
             $projects = $projects->paginate($per_page);
         }

         $projects->setCollection(
             $projects->getCollection()->map(function ($project) {
                 $project->team_structure = $project->getTeamStructure();
                 $project->task_statistics = $project->getTaskStatistics();
                 unset($project->goals);
                 return $project;
             })
         );


         return $this->successResponse(
            $projects,
            'services.projects.fetched'
        );
    }

    public function overview(Request $request){
        $employer = $request->user();

        $month = $request->input('month', Carbon::now()->month);
        $year = $request->input('year', Carbon::now()->year);
        $startDate = Carbon::create(Carbon::now()->year - 1, 1, 1)->startOfMonth();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth();

        $performance = $this->teamCalculator->calculateDepartmentProjectMetrics($employer->id, $startDate, $endDate, 'company');

        return $this->successResponse(
            $projects,
            'services.projects.fetched'
        );

        return response()->json($response, 200);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(ProjectRequest $request)
    {
        // Start a transaction
        DB::beginTransaction();

        try {
            $user = $request->user();
            $validatedData = $request->validated();
            $validatedData['code'] = $user->id . md5(time());
            $validatedData['employer_user_id'] = $user->id;
            // $validatedData['user_id'] = $user->id;
            // Create Project
            $project = Project::create($validatedData);
           // Create assigned employees
            $memberInstances = [];
            $leadInstances = [];

            //
            $employees = $validatedData['team_members'];
            foreach ($employees as $employee) {
                $assignedMember = ProjectTeam::create([
                    'project_id' => $project->id,
                    'employee_id' => $employee,
                    'is_team_lead' => false
                ]);

                $memberInstances[] = $assignedMember;
            }
            //
            $leads = $validatedData['team_leads'];
            foreach ($leads as $employee) {
                $assignedLead = ProjectTeam::create([
                    'project_id' => $project->id,
                    'employee_id' => $employee,
                    'is_team_lead' => true
                ]);

                $leadInstances[] = $assignedLead;
            }

            // Send notifications to members and leads
            ProjectNotificationHelper::notifyAssignedUsers($memberInstances, $leadInstances, $project);

            // Commit the transaction
            DB::commit();

            return $this->successResponse(
                $project,
                'services.projects.created',
                [],
                Response::HTTP_CREATED
            );
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse(
                'services.projects.create_error',
                ['error' => $e->getMessage()],
                Response::HTTP_BAD_REQUEST
            );
        }
    }


    public function employee(Request $request, $code)
    {
        $user = $request->user();
        $per_page = $request->input('per_page', 12);

        $employee = Employee::where('code', $code)->firstOrFail();

        if($user->type == 'talent'){
           $validation_result = validateEmployeeAccess($user, $employee);

             // If validation fails, return the response
            if ($validation_result !== true) {
                return $validation_result;
            }
        }

        $projects = Project::join('project_teams', 'projects.id', '=', 'project_teams.project_id')
                    ->where('project_teams.employee_id', $employee->id)
                    ->where('projects.employer_user_id', $employee->employer_id)
                    ->with(['department'])->withCount('team')
                    ->latest()
                    ->paginate($per_page);

        $projects->setCollection(
            $projects->getCollection()->map(function ($project) {
                $project->team_structure = $project->getTeamStructure();
                $project->task_statistics = $project->getTaskStatistics();
                unset($project->goals);
                return $project;
            })
        );

        return response()->json([
            'status' => true,
            'data' => $projects,
            'message' => ""
        ], 200);
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        $user = $request->user();
        $user_type = $user->type;
        $employerId = ($user_type == 'employer') ? $user->id: null;

          // Validate Employee Access
        if($user_type == 'talent'){
            $employee = Employee::where('user_id', $user->id)->where('id', $user->default_company_id)->firstOrFail();
            $employerId = $employee->employer_id;
        }


        $project = Project::where('code', $id)
        ->with([
            'department',
            'milestones',
            'goals'
        ])
        ->where('employer_user_id', $employerId)
        ->firstOrFail();

        if($user_type == 'talent'){
            $validation_result = validateProjectAccess($user, $project);
            info(['Validation Result', $validation_result]);

            if ($validation_result !== true) {
                return $validation_result;
            }
        }

        $project->team_structure = $project->getTeamStructure();
        $project->task_statistics = $project->getTaskStatistics();

        return response()->json([
            'status' => true,
            'message' => "",
            'data' => $project,
        ], 200);
    }

    public function getProjectTeam(Request $request, $id)
    {
        $user = $request->user();
        $user_type = $user->type;
        $employerId = ($user_type == 'employer') ? $user->id: null;

          // Validate Employee Access
        if($user_type == 'talent'){
            $employee = Employee::where('user_id', $user->id)->where('id', $user->default_company_id)->firstOrFail();
            $employerId = $employee->employer_id;
        }

        $project = Project::where('code', $id)
                ->where('employer_user_id', $employerId)
                ->firstOrFail();

        if($user_type == 'talent'){
            $validation_result = validateProjectAccess($user, $project);

            if ($validation_result !== true) {
                return $validation_result;
            }
        }


        $project->team_structure = $project->getTeamStructure();

        return response()->json([
            'status' => true,
            'message' => "",
            'data' => $project,
        ], 200);
    }

      /**
     * Add team members or leads to a project
     *
     * @param ProjectRequest $request
     * @param int $id Project ID
     * @return \Illuminate\Http\JsonResponse
     */
    public function addTeam(Request $request, $id)
    {
        $user = $request->user();
        // Permision check
        if (!($project = validateEmployerProjectOwnership($user, $id)) instanceof \App\Models\Project\Project) return $project;

        $validatedData = $request->validate([
            'team_members.*' => 'integer|exists:employees,id',
            'team_leads.*' => 'nullable|integer|exists:employees,id',
        ]);

        // $project = Project::findOrFail($id);
        $employeeInstances = [];

        // Add team members if provided
        if (isset($validatedData['team_members']) && !empty($validatedData['team_members'])) {
            $employees = $validatedData['team_members'];

            foreach ($employees as $employee) {
                // Check if employee is already a member to avoid duplicates
                $exists = ProjectTeam::where('project_id', $project->id)
                    ->where('employee_id', $employee)
                    ->where('is_team_lead', false)
                    ->exists();

                if (!$exists) {
                    $assignedMember = ProjectTeam::create([
                        'project_id' => $project->id,
                        'employee_id' => $employee,
                        'is_team_lead' => false
                    ]);

                    $employeeInstances[] = $assignedMember;
                }
            }
        }

        // Add team leads if provided
        if (isset($validatedData['team_leads']) && !empty($validatedData['team_leads'])) {
            $leads = $validatedData['team_leads'];
            foreach ($leads as $employee) {
                // Check if employee is already a lead to avoid duplicates
                $exists = ProjectTeam::where('project_id', $project->id)
                    ->where('employee_id', $employee)
                    ->where('is_team_lead', true)
                    ->exists();

                if (!$exists) {
                    $assignedMember = ProjectTeam::create([
                        'project_id' => $project->id,
                        'employee_id' => $employee,
                        'is_team_lead' => true
                    ]);

                    $employeeInstances[] = $assignedMember;
                }
            }
        }

        // Load fresh project data with team relationships
        $refreshedProject = Project::with(['team.employee','department'])
            ->findOrFail($project->id);

        return $this->successResponse(
            $refreshedProject,
            'services.projects.team_updated'
        );
    }

    /**
     * Remove a team member or lead from a project
     *
     * @param Request $request
     * @param int $id Project ID
     * @return \Illuminate\Http\JsonResponse
     */
    public function removeTeam(Request $request, $id)
    {
        // Permision check
        $user = $request->user();
        if (!($project = validateEmployerProjectOwnership($user, $id)) instanceof \App\Models\Project\Project) return $project;

        $validatedData = $request->validate([
            'employee_id' => 'required|integer|exists:employees,id',
            'is_team_lead' => 'required|boolean',
        ]);

        // $project = Project::findOrFail($id);

        // Find and delete the team member
        $deleted = ProjectTeam::where('project_id', $project->id)
            ->where('employee_id', $validatedData['employee_id'])
            ->where('is_team_lead', $validatedData['is_team_lead'])
            ->delete();

        if (!$deleted) {
            return $this->badRequestResponse(
                'services.projects.team_not_found'
            );
        }

        $refreshedProject = Project::with(['team.employee','department'])
                    ->findOrFail($project->id);

        return $this->successResponse(
            $projects,
            'services.projects.tean_removed'
        );
    }



    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(ProjectRequest $request, $id)
    {
         // Permision check
        $user = $request->user();
        if (!($project = validateEmployerProjectOwnership($user, $id)) instanceof \App\Models\Project\Project) return $project;

        $validatedData = $request->validated();
        // $project = Project::findOrFail($id);

        $project->update($validatedData);

        return $this->successResponse(
            $projects->fresh(),
            'services.projects.updated'
        );
    }

    /**
     * Archive the specified resource from active list.
     *
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function archive(Request $request, $id)
    {
        $user = $request->user();
        if (!($project = validateEmployerProjectOwnership($user, $id)) instanceof \App\Models\Project\Project) return $project;

        // $project = Project::findOrFail($id);
        // $this->authorize('delete', [Project::class, $project]);

        $project->archived_at = now();
        $project->save();

        return $this->successResponse(
            $projects->fresh(),
            'services.projects.archived'
        );
    }


    /**
     * Unarchive the specified resource from archived storage.
     *
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function unarchive(Request $request, $id)
    {
        $user = $request->user();
        if (!($project = validateEmployerProjectOwnership($user, $id)) instanceof \App\Models\Project\Project) return $project;

        // $project = Project::findOrFail($id);

        // $this->authorize('delete', [Project::class, $project]);

        $project->archived_at = null;
        $project->save();

        return $this->successResponse(
            $projects->fresh(),
            'services.projects.restored'
        );
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

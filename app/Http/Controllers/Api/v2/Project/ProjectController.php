<?php

namespace App\Http\Controllers\Api\v2\Project;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProjectRequest;
use App\Models\Project\Project;
use App\Models\Project\ProjectTeam;
use App\Services\DepartmentPerformanceService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProjectController extends Controller
{

    protected $teamCalculator;

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


        return response()->json([
            'status' => true,
            'data' => $projects,
            'message' => "Team structure and projects fetched successfully",
        ], 200);
    }

    public function overview(Request $request){
        $employer = $request->user();

        $month = $request->input('month', Carbon::now()->month);
        $year = $request->input('year', Carbon::now()->year);
        $startDate = Carbon::create(Carbon::now()->year - 1, 1, 1)->startOfMonth();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth();

        $performance = $this->teamCalculator->calculateDepartmentProjectMetrics($employer->id, $startDate, $endDate, 'company');

        $response = collect([
            'status' => true,
            'data' => $performance,
            'message' => "Successful.",
        ]);

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
            $employeeInstances = [];
            $leadInstances = [];
            //
            $employees = $validatedData['team_members'];
            foreach ($employees as $employee) {
                $assignedMember = ProjectTeam::create([
                    'project_id' => $project->id,
                    'employee_id' => $employee,
                    'is_team_lead' => false
                ]);

                $employeeInstances[] = $assignedMember;
            }
            //
            $leads = $validatedData['team_leads'];
            foreach ($leads as $employee) {
                $assignedMember = ProjectTeam::create([
                    'project_id' => $project->id,
                    'employee_id' => $employee,
                    'is_team_lead' => true
                ]);

                $employeeInstances[] = $assignedMember;
            }

            // Commit the transaction
            DB::commit();

            return response()->json([
                'status' => true,
                'message' => "",
                'data' => $project,
            ], 201);
        }catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => "Could not complete request. " . $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $project = Project::where('code', $id)
        ->with([
            'department',
            'milestones',
            'goals'
        ])
        ->firstOrFail();

        $project->team_structure = $project->getTeamStructure();
        $project->task_statistics = $project->getTaskStatistics();

        return response()->json([
            'status' => true,
            'message' => "Project details fetched successfully",
            'data' => $project,
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
     /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(ProjectRequest $request, $id)
    {
        $validatedData = $request->validated();
        $project = Project::findOrFail($id);

        $project->update($validatedData);

        return response()->json([
            'status' => true,
            'message' => "Project updated successfully.",
            'data' => Project::find($project->id)
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
        $project = Project::findOrFail($id);
        // $this->authorize('delete', [Project::class, $project]);

        $project->archived_at = now();
        $project->save();

        return response()->json([
            'status' => true,
            'message' => "Project archived successfully.",
            'data' => Project::find($project->id)
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
        $project = Project::findOrFail($id);

        // $this->authorize('delete', [Project::class, $project]);

        $project->archived_at = null;
        $project->save();

        return response()->json([
            'status' => true,
            'message' => "Project unarchived successfully.",
            'data' => Project::find($project->id)
        ], 200);
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

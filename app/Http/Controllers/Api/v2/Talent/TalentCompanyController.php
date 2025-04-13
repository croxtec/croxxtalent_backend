<?php

namespace App\Http\Controllers\Api\v2\Talent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\Supervisor;
use App\Models\Goal;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Cloudinary\Cloudinary;

class TalentCompanyController extends Controller
{
    protected $cloudinary;

    public function __construct(Cloudinary $cloudinary)
    {
        $this->cloudinary = $cloudinary;
    }

    public function index(Request $request){
        $user = $request->user();
        $companies = Employee::where('user_id', $user->id)->with('employer')->get();
        $default_company = null;
        $dashboard = [];

        if (count($companies)) {
            // Get the first company's employer_id as the default
            $firstCompanyEmployerId = $companies->first()->id;

            if($request->input('employer')) {
                $user->default_company_id = $request->input('employer');
                $user->save();
            }

            $default_company = $companies->firstWhere('id', $user->default_company_id);

            // Check if $default_company exists before accessing properties
            if($default_company) {
                // Check company status
                if(in_array($default_company->status, [0, 3, 9])){
                    return response()->json([
                        'status' => false,
                        'data' => compact('default_company','companies'),
                        'message' => 'Unauthorized Access'
                    ], 403);
                }

                $default_company->department;
                $default_company->department_role;
                $default_company->employer;

                if($default_company->department && !$default_company->supervisor_id){
                    $dashboard = [
                        'completed_assessment' => $default_company->completedAssessment()->count(),
                        'learning_paths' => $default_company->learningPaths()->count(),
                        'goals_completed' => $default_company->goalsCompleted()->count(),
                    ];

                    $default_company->department->technical_skill;
                    $default_company->department->soft_skill;

                    $technical_skills = array_column($default_company->department->technical_skill->toArray(),'competency');
                    $assessment_distribution = [];
                    $trainings_distribution = [];

                    if(count($technical_skills)){
                        foreach($technical_skills as $skill){
                            array_push($assessment_distribution, mt_rand(0, 100));
                        }
                    }

                    $default_company->technical_distribution = [
                        'categories' => $technical_skills,
                        'assessment_distribution' =>  $assessment_distribution,
                        'trainings_distribution' =>  $assessment_distribution,
                    ];
                }

                if($default_company->supervisor){
                    $dashboard = [
                        'feedback_sent' => $default_company->feedbackSent()->count(),
                        'task_assigned' => $default_company->taskAssigned()->count(),
                    ];
                }

                $default_company->summary = $dashboard;
            }
        }

        return response()->json([
            'status' => true,
            'data' => compact('default_company','companies'),
            'message' => ''
        ], 200);
    }

    public function supervisor(Request $request){
        $user = $request->user();
        $companies = Employee::where('user_id', $user->id)->get();

        $myinfo =  null;

        if (count($companies)) {
            // Get the first company's employer_id as the default

            // $firstCompanyEmployerId = $companies->first()->id;
            // Retrieve the id from the request or use the first company's id as default
            // $defaultCompanyId = $request->input('employer', $firstCompanyEmployerId);
            // Employerr
            $myinfo = $companies->firstWhere('id', $user->default_company_id);

            if(isset($myinfo->supervisor_id)){
                // Get Supervisor Info
                $supervisor = Supervisor::where('supervisor_id', $myinfo->id)->first();
                // Supervisor Detail
                $team_structure =  Employee::where('employer_id', $supervisor->employer_id)
                                     ->where('job_code_id',  $supervisor->department_id)
                                     ->with(['department', 'department_role'])
                                     ->whereNull('supervisor_id')->get();

                return response()->json([
                    'status' => true,
                    'data' => compact('supervisor', 'team_structure'),
                    'message' => ''
                ], 200);
            }else{
                return response()->json([
                    'status' => false,
                    'message' => 'Unautourized Access'
                ], 403);
            }
        }

        return response()->json([
            'status' => false,
            'message' => 'Supervisor not found'
        ], 404);
    }

    public function employeeInformation(Request $request, $id){
        $user = $request->user();
        $companies = Employee::where('user_id', $user->id)->get();

        $myinfo =  null;

        if (count($companies)) {
            $firstCompanyEmployerId = $companies->first()->id;
            $defaultCompanyId = $request->input('employer', $firstCompanyEmployerId);
            $myinfo = $companies->firstWhere('id', $user->default_company_id);

            if(isset($myinfo->supervisor_id)){

                if (is_numeric($id)) {
                    $employee = Employee::where('id', $id)->where('employer_id', $myinfo->employer_id)->first();
                } else {
                    $employee = Employee::where('code', $id)->where('employer_id', $myinfo->employer_id)->first();
                }


                $employee->department;
                $employee->department_role;
                $employee->talent;
                $employee->supervisor;

                $technical_skills = array_column($employee->department->technical_skill->toArray(0),'competency');
                $assessment_distribution = [];
                $trainings_distribution = [];

                if(count($technical_skills)){
                    foreach($technical_skills as $skill){
                        array_push($assessment_distribution, mt_rand(0, 10));
                        array_push($trainings_distribution, mt_rand(0, 10));
                    }
                }

                $employee->technical_distribution = [
                    'categories' => $technical_skills,
                    'assessment_distribution' =>  $assessment_distribution,
                    'trainings_distribution' =>  $trainings_distribution,
                ];

                $employee->proficiency = [
                    'total' =>  '90%',
                    'assessment' => [
                        'taken' => $employee->completedAssessment()->count(),
                        'performance' => '0%'
                    ],
                    'goals' => [
                        'taken' => $employee->goalsCompleted()->count(),
                        'performance' => '0%'
                    ],
                    'trainings' => [
                        'taken' => $employee->learningPaths()->count(),
                        'performance' => '0%'
                    ],
                ];

                return response()->json([
                    'status' => true,
                    'data' => $employee,
                    'message' => ''
                ], 200);
            }else{
                return response()->json([
                    'status' => false,
                    'message' => 'Unautourized Access'
                ], 403);
            }
        }

        return response()->json([
            'status' => false,
            'message' => 'Supervisor not found'
        ], 404);
    }

    public function teamPerformanceProgress(Request $request){
        $user = $request->user();
        $companies = Employee::where('user_id', $user->id)->get();

        $myinfo =  null;

        if (count($companies)) {
            $firstCompanyEmployerId = $companies->first()->id;
            $per_page = $request->input('per_page', 4);
            $defaultCompanyId = $request->input('employer', $firstCompanyEmployerId);

            $myinfo = $companies->firstWhere('id', $user->default_company_id);

            if(isset($myinfo->supervisor_id)){
                $supervisor = Supervisor::where('supervisor_id', $myinfo->id)->first();
                // Add Pagination here
                $employees = Employee::where('employer_id', $supervisor->employer_id)
                                    ->where('job_code_id', $supervisor->department_id)
                                    ->with(['department', 'department_role'])
                                    ->whereNull('supervisor_id')
                                    ->paginate($per_page);

                $employeeIds =  $employees->pluck('id');

                $goals = Goal::whereIn('employee_id', $employeeIds)
                                ->orderBy('created_at', 'desc')
                                ->limit(3)->get();

                $groupedGoals = $goals->groupBy('employee_id');

                // $team_goals = $groupedGoals->map(function ($goals, $employeeId) {
                //     return [
                //         'employee' => $employeeId,
                //         'goals' => $goals
                //     ];
                // });

                // Map employee details and goals
                $team_goals = $employees->map(function ($employee) use ($groupedGoals) {
                    return [
                        'employee' => $employee,
                        'goals' => $groupedGoals->get($employee->id, collect()),
                    ];
                });

                return response()->json([
                    'status' => true,
                    'data' => $team_goals,
                    'message' => ''
                ], 200);
            }else{
                return response()->json([
                    'status' => false,
                    'message' => 'Unautourized Access'
                ], 403);
            }
        }

        return response()->json([
            'status' => false,
            'message' => 'Supervisor not found'
        ], 404);
    }


    public function photo(Request $request)
    {
        $user = $request->user();

        $myinfo = Employee::where('user_id', $user->id)->where('id', $user->default_company_id)->firstOrFail();

        if ($request->hasFile('photo') && $request->file('photo')->isValid()) {
            $file = $request->file('photo');
            $extension = $file->extension();
            $fileSize = $file->getSize(); // size in bytes
            $transformation = [];

            // Check if the file size is greater than 700KB (700 * 1024 bytes)
            if ($fileSize > 700 * 1024) {
                $transformation['quality'] = '60';
            }

            $filename = time() . '-' . Str::random(32);
            $filename = "{$filename}.$extension";
            $rel_upload_path  = "CroxxCompany/{$myinfo->employer_id}";

            // Delete previously uploaded file if any
            if ($myinfo->photo) {
                $public_id = pathinfo($myinfo->photo, PATHINFO_FILENAME); // Extract public_id from URL
                // info(['Public ID', $public_id]);
                $this->cloudinary->uploadApi()->destroy($public_id);
            }

            // Upload new photo
            $result = $this->cloudinary->uploadApi()->upload($file->getRealPath(), [
                'folder' => $rel_upload_path, // Specify a folder
            ]);

            $myinfo->photo_url = $result['secure_url'];
            $myinfo->photo_updated_at = Carbon::now();
            $myinfo->save();

            return response()->json([
                'status' => true,
                'message' => 'Photo uploaded successfully.',
                'data' => [
                    'photo_url' => $result['secure_url'],
                    'employee' => $myinfo
                ]
            ], 200);
        }

        return response()->json([
            'status' => true,
            'message' => "Could not upload photo, please try again.",
        ], 400);
    }

}

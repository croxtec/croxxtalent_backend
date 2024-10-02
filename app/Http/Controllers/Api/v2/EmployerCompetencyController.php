<?php

namespace App\Http\Controllers\Api\v2;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\User;
use App\Models\Employee;
use App\Models\AssesmentSummary;
use App\Models\Assesment;
use App\Models\VettingSummary;
use App\Models\EmployerJobcode as Department;
use App\Models\Competency\DepartmentMapping;
use App\Models\Competency\DepartmentSetup;
use App\Services\OpenAIService;

class EmployerCompetencyController extends Controller
{
    protected $openAIService;

    public function __construct(OpenAIService $openAIService)
    {
        $this->openAIService = $openAIService;
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $job_code = $request->input('department');

        // Fetch department with its technical skills
        $department = Department::where('employer_id', $user->id)
                                ->where('id', $job_code)
                                ->with('technical_skill')
                                ->firstOrFail();

        // Check if preferred competencies are already attached to the department
        if ($department->technical_skill->count()) {
            return response()->json([
                'status' => false,
                'message' => 'Competency mapping already available.'
            ], 400);
        }

        $job_title = trim($department->job_code);

        // Check if there are stored competencies for this department
        $storedCompetencies = DepartmentSetup::where('department', $job_title)
                                             ->get()
                                             ->groupBy('department_role');

        if ($storedCompetencies->count()) {
            $competencies = $storedCompetencies;
        } else {
            // Generate competencies using OpenAIService if not stored
            $competencies = $this->openAIService->generateCompetencyMapping($job_title);

            // Save competencies to DepartmentSetup
            foreach ($competencies as $competency) {
                foreach (['technical_skill', 'soft_skill'] as $skillType) {
                    foreach ($competency[$skillType] as $skill) {
                        DepartmentSetup::create([
                            'department' => $job_title,
                            'competency' => $skill['competency'],
                            'level' => strtolower($competency['level']),
                            'department_role' => $skillType,
                            'description' => $skill['description'],
                            'generated_id' => $user->id
                        ]);
                    }
                }
            }

            $competencies = DepartmentSetup::where('department', $job_title)
                ->get()
                ->groupBy('department_role');
        }

        return response()->json([
            'status' => true,
            'data' => $competencies,
            'message' => 'Competency Mapping Generated.'
        ], 200);
    }


    public function storeCompetency(Request $request, $id){
        $employer = $request->user();

        if (is_numeric($id)) {
            $department = Department::where('id', $id)->where('employer_id', $employer->id)
                ->select(['id','job_code', 'job_title', 'description'])->firstOrFail();
        } else {
            $department = Department::where('job_title', $id)->where('employer_id', $employer->id)
                ->select(['id','job_code', 'job_title', 'description'])->firstOrFail();
        }

        $validatedData =   $request->validate([
            'mapping' => 'required|array',
            'mapping.*' => 'required|exists:department_setups,id'
        ]);

        $mapping_setups =  DepartmentSetup::whereIn('id', $validatedData['mapping']) ->get();


        if(count($mapping_setups)){
            foreach($mapping_setups as $map){
                DepartmentMapping::firstOrCreate([
                    'employer_id' => $employer->id,
                    'department_id' => $department->id,
                    'competency' => $map['competency'],
                ],[
                    'competency_role' => $map['department_role'],
                    'description' => $map['description'],
                ]);
            }
        }

        if(isset($employer->onboarding_stage) && $employer->onboarding_stage == 2){
            $employer->onboarding_stage = 3;
            $employer->save();
        }

        return response()->json([
            'status' => true,
            'message' => "Competency matched.",
        ], 201);

    }

    public function addCompetency(Request $request, $id){
        $employer = $request->user();

        if (is_numeric($id)) {
            $department = Department::where('id', $id)->where('employer_id', $employer->id)
                ->select(['id','job_code', 'job_title', 'description'])->firstOrFail();
        } else {
            $department = Department::where('job_title', $id)->where('employer_id', $employer->id)
                ->select(['id','job_code', 'job_title', 'description'])->firstOrFail();
        }

        $validatedData =   $request->validate([
            'competency' => 'required|string|between:3,30',
            'role' => 'required|in:technical_skill,soft_skill'
        ]);

        $department = DepartmentMapping::firstOrCreate([
            'employer_id' => $employer->id,
            'department_id' => $department->id,
            'competency' => $validatedData['competency'],
        ],[
            'competency_role' => $validatedData['role'],
            'description' => $validatedData['description'] ?? '',
        ]);

        return response()->json([
            'status' => true,
            'data' => $department,
            'message' => "Competency matched.",
        ], 201);

    }

    public function confirmWelcome(Request $request){
        $employer = $request->user();

        if(isset($employer->onboarding_stage) && $employer->onboarding_stage >= 2){
            $employer->onboarding_stage = 4;
            $employer->save();
        }

        return response()->json([
            'status' => true,
            'message' => "",
            'data' => []
        ], 201);

    }


}


 // public function index(Request $request)
    // {
    //     $user = $request->user();

    //     // $competencies = EmployerCompetencyController::competencies();
    //     // Separate technical and soft skills
    //     $technical_skills = array_filter($competencies, function($competency) {
    //         return $competency['competency_role'] === 'technical_skill';
    //     });

    //     $soft_skills = array_filter($competencies, function($competency) {
    //         return $competency['competency_role'] === 'soft_skill';
    //     });

    //     // Shuffle the arrays to randomize selection
    //     shuffle($technical_skills);
    //     shuffle($soft_skills);

    //     // Select 6 technical skills and 4 soft skills
    //     $technical_skills = array_slice($technical_skills, 0, 5);
    //     $soft_skills = array_slice($soft_skills, 0, 3);

    //     // Combine selected skills
    //     // $selected_skills = array_merge($selected_technical_skills, $selected_soft_skills);

    //     // Filter for competencies in the 'operations' department
    //     // $operations_competencies = array_filter($competencies, function($competency) {
    //     //     return $competency['department'] === 'operations';
    //     // });

    //     return response()->json([
    //         'status' => true,
    //         'data' => compact('technical_skills','soft_skills'),
    //         'message' => 'Suggested Competency Mapping.'
    //     ], 200);
    // }

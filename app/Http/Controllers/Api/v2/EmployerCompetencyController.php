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
use App\Models\CompetencyKpiSetup;
use App\Models\DepartmentKpiSetup;
use App\Services\OpenAIService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EmployerCompetencyController extends Controller
{
    protected $openAIService;

    public function __construct(OpenAIService $openAIService)
    {
        $this->openAIService = $openAIService;
    }

    public function index(Request $request)
    {
        try {
            DB::beginTransaction();

            $user = $request->user();
            $job_code = $request->input('department');

            // Fetch department record for the current employer
            $department = Department::where('employer_id', $user->id)
                ->where('id', $job_code)
                ->firstOrFail();

            $job_title = trim($department->job_code);

            // Check if competencies are already stored for this department
            $storedCompetencies = DepartmentSetup::where('department', $job_title)
                ->get()
                ->groupBy('department_role');

            if ($storedCompetencies->count()) {
                $competencies = $storedCompetencies;
            } else {
                // Generate department template (includes KPI data, goals, and competency mapping)
                $templateData = $this->openAIService->generateDepartmentTemplate($job_title);

                // Update or create the DepartmentKpiSetup record
                DepartmentKpiSetup::updateOrCreate(
                    ['department' => $job_title],
                    [
                        'department_goals'         => $templateData['department_goals'] ?? null,
                        'recommended_assessments'  => $templateData['recommended_assessments'] ?? null,
                        'recommended_trainings'    => $templateData['recommended_trainings'] ?? null,
                    ]
                );

                // Save the competency mapping into DepartmentSetup
                if (isset($templateData['competency_mapping'])) {
                    // Process technical skills
                    if (isset($templateData['competency_mapping']['technical_skills'])) {
                        foreach ($templateData['competency_mapping']['technical_skills'] as $skill) {
                            DepartmentSetup::create([
                                'department'      => $job_title,
                                'competency'      => $skill['competency'],
                                'level'           => strtolower($skill['level']),
                                'department_role' => 'technical_skill',
                                'description'     => $skill['description'],
                                'target_score' => $skill['target_score'],
                                'generated_id'    => $user->id,
                            ]);
                        }
                    }
                    // Process soft skills
                    if (isset($templateData['competency_mapping']['soft_skills'])) {
                        foreach ($templateData['competency_mapping']['soft_skills'] as $skill) {
                            DepartmentSetup::create([
                                'department'      => $job_title,
                                'competency'      => $skill['competency'],
                                'level'           => strtolower($skill['level']),
                                'department_role' => 'soft_skill',
                                'target_score' => $skill['target_score'],
                                'description'     => $skill['description'],
                                'generated_id'    => $user->id,
                            ]);
                        }
                    }
                }

                // Retrieve the newly stored competencies
                $competencies = DepartmentSetup::where('department', $job_title)
                    ->get()
                    ->groupBy('department_role');
            }

            DB::commit();

            return response()->json([
                'status'  => true,
                'data'    => $competencies,
                'message' => 'Competency Mapping and KPI Setup Generated.'
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error generating mapping: ' . $e->getMessage());
            return response()->json([
                'status'  => false,
                'message' => 'Error generating mapping: ' . $e->getMessage()
            ], 500);
        }
    }


    public function storeCompetency(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            $employer = $request->user();

            // Get department info
            if (is_numeric($id)) {
                $department = Department::where('id', $id)
                    ->where('employer_id', $employer->id)
                    ->select(['id', 'job_code', 'job_title', 'description'])
                    ->firstOrFail();
            } else {
                $department = Department::where('job_title', $id)
                    ->where('employer_id', $employer->id)
                    ->select(['id', 'job_code', 'job_title', 'description'])
                    ->firstOrFail();
            }

            $validatedData = $request->validate([
                'mapping' => 'required|array',
                'mapping.*' => 'required|exists:department_setups,id'
            ]);

            $mapping_setups = DepartmentSetup::whereIn('id', $validatedData['mapping'])->get();

            foreach ($mapping_setups as $map) {
                DepartmentMapping::firstOrCreate([
                    'employer_id'   => $employer->id,
                    'department_id' => $department->id,
                    'competency'    => $map->competency,
                ], [
                    'level' => $map->level,
                    'target_score' => $map->target_score,
                    'competency_role' => $map->department_role,
                    'description'     => $map->description,
                ]);

                // At this point you can optionally use information from $departmentKpiSetup
                // (like recommended_assessments or recommended_trainings) to update or trigger
                // further logic if needed.
            }

            // Update the onboarding stage if applicable
            if (isset($employer->onboarding_stage) && $employer->onboarding_stage == 2) {
                $employer->onboarding_stage = 3;
                $employer->save();
            }


            DB::commit();

            return response()->json([
                'status' => true,
                'message' => "Competency and KPI mapping stored successfully.",
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error storing competency mapping: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Error storing competency mapping'
            ], 500);
        }
    }


    public function storeCompetencyOld(Request $request, $id){
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
            'competency' => trim($validatedData['competency']),
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

    // [
    //     532,
    //     533,
    //     534,
    //     538,
    //     539,
    //     540,
    //     544,
    //     545,
    //     546,
    //     550,
    //     551,
    //     552,
    //     529,
    //     530,
    //     531,
    //     535,
    //     536,
    //     537,
    //     541,
    //     542,
    //     543,
    //     547,
    //     548,
    //     549
    // ];

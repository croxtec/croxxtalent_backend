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

            // Fetch department with its technical skills
            $department = Department::where('employer_id', $user->id)
                ->where('id', $job_code)
                ->with('technical_skill')
                ->firstOrFail();

            // If competencies are already attached, return an error
            if ($department->technical_skill->count()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Competency mapping already available.'
                ], 400);
            }

            $job_title = trim($department->job_code);

            // Generate department template (includes KPI data)
            $templateData = $this->openAIService->generateDepartmentTemplate($job_title);
            // info($templateData);
            // Store or update the Department KPI Setup
            $kpiSetup = DepartmentKpiSetup::updateOrCreate(
                ['department' => $job_title],
                [
                    'department_goals'   => $templateData['department_goals'] ?? null,
                    // 'beginner_kpis'      => $templateData['beginner_kpis'] ?? null,
                    // 'intermediate_kpis'  => $templateData['intermediate_kpis'] ?? null,
                    // 'advance_kpis'       => $templateData['advance_kpis'] ?? null,
                    // 'expert_kpis'        => $templateData['expert_kpis'] ?? null,
                    'level_kpis'         => $templateData['level_kpis'] ?? null,
                ]
            );

            // Check if competencies are already stored for this department
            $storedCompetencies = DepartmentSetup::where('department', $job_title)
                ->get()
                ->groupBy('department_role');

            if ($storedCompetencies->count()) {
                $competencies = $storedCompetencies;
            } else {
                // Generate competencies if not stored
                $competencies = $this->openAIService->generateCompetencyMapping($job_title);

                foreach ($competencies as $competency) {
                    foreach (['technical_skill', 'soft_skill'] as $skillType) {
                        foreach ($competency[$skillType] as $skill) {
                            DepartmentSetup::create([
                                'department'      => $job_title,
                                'competency'      => $skill['competency'],
                                'level'           => strtolower($competency['level']),
                                'department_role' => $skillType,
                                'description'     => $skill['description'],
                                'generated_id'    => $user->id,
                            ]);
                        }
                    }
                }

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

            $departmentKpiSetup = DepartmentKpiSetup::where('department', $department->job_code)
                ->firstOrFail();

            $mapping_setups = DepartmentSetup::whereIn('id', $validatedData['mapping'])->get();

            foreach ($mapping_setups as $map) {
                $departmentMapping = DepartmentMapping::firstOrCreate([
                    'employer_id' => $employer->id,
                    'department_id' => $department->id,
                    'competency' => $map['competency'],
                ], [
                    'competency_role' => $map['department_role'],
                    'description' => $map['description'],
                ]);

                // Get relevant KPIs for this competency
                $levelKpis = json_decode($departmentKpiSetup->level_kpis, true) ?? [];

                foreach ($levelKpis as $levelData) {
                    foreach ($levelData['kpis'] as $kpi) {
                        foreach ($kpi['mandatory_competencies'] as $mandatoryCompetency) {
                            if ($mandatoryCompetency['competency'] === $map['competency']) {

                                CompetencyKpiSetup::updateOrCreate([
                                    'department_mapping_id' => $departmentMapping->id,
                                    'kpi_name' => $kpi['kpi_name'],
                                    'level' => $levelData['level']
                                ], [
                                    'description' => $kpi['description'],
                                    'frequency' => $kpi['frequency'],
                                    'target_score' => $mandatoryCompetency['target_score'],
                                    'weight' => $mandatoryCompetency['weight']
                                ]);
                            }
                        }
                    }
                }
            }

            // Update onboarding stage if needed
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

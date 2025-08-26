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
use App\Models\TrackEmployerOnboarding;
use App\Services\OpenAIService;
use App\Services\CroxxAI\CroxxAIService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Response;

class EmployerCompetencyController extends Controller
{
    protected $croxxAI;
    protected $openAIService;

    public function __construct( CroxxAIService $croxxAI )
    {
        $this->croxxAI = $croxxAI;
        // $this->openAIService = $openAIService;OpenAIService $openAIService,
    }

   public function index(Request $request)
    {
        try {
            DB::beginTransaction();

            $user = $request->user();
            $job_code = $request->input('department');
            $language = $user->language ?? 'en'; // default to English

            // Fetch department record for the current employer
            $department = Department::where('employer_id', $user->id)
                ->where('id', $job_code)
                ->firstOrFail();

            $job_title = trim($department->job_code);

            $storedCompetencies = DepartmentSetup::where('department', $job_title)->where('language', $language)
                                        ->get()->groupBy('department_role');

            if ($storedCompetencies->count()) {
                $competencies = $storedCompetencies;
            } else {
                
                $templateData = $this->croxxAI->generateCompetencyMapping($job_title, $language);

                return response()->json([
                    'status' => true,
                    'data' => $templateData,
                    'message' => "Competency mapping generated.",
                ], 200);
               
                foreach ($templateData as $mapping) {
                    $level = strtolower($mapping['level'] ?? 'beginner'); // enforce lowercase

                    // Technical skills
                    if (!empty($mapping['technical_skills']) && is_array($mapping['technical_skills'])) {
                        foreach ($mapping['technical_skills'] as $skill) {
                            DepartmentSetup::create([
                                'department'      => $job_title,
                                'competency'      => $skill['competency'],
                                'level'           => $level,
                                'department_role' => 'technical_skill',
                                'description'     => $skill['description'],
                                'target_score'    => $skill['target_score'] ?? 0,
                                'language'        => $language,
                                'generated_id'    => $user->id,
                            ]);
                        }
                    }

                    // Soft skills
                    if (!empty($mapping['soft_skills']) && is_array($mapping['soft_skills'])) {
                        foreach ($mapping['soft_skills'] as $skill) {
                            DepartmentSetup::create([
                                'department'      => $job_title,
                                'competency'      => $skill['competency'],
                                'level'           => $level,
                                'department_role' => 'soft_skill',
                                'description'     => $skill['description'],
                                'target_score'    => $skill['target_score'] ?? 0,
                                'language'        => $language,
                                'generated_id'    => $user->id,
                            ]);
                        }
                    }
                }

                // ✅ Retrieve the newly stored competencies filtered by language
                $competencies = DepartmentSetup::where('department', $job_title)
                    ->where('language', $language)
                    ->get()
                    ->groupBy('department_role');
            }

            DB::commit();

            return $this->successResponse($competencies, 'company.competency.generated');

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error generating mapping: ' . $e->getMessage());

            return $this->errorResponse('company.competency.generation_error', [
                'error' => $e->getMessage()
            ]);
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
                'level'          => strtolower($map->level) === 'advance' ? 'advanced' : $map->level,
                    'target_score'   => min(max($map->target_score, 0), 100),
                    'competency_role' => $map->department_role,
                    'description'     => $map->description,
                ]);
            }

            // Update the onboarding stage if applicable
            if (isset($employer->onboarding_stage) && $employer->onboarding_stage == 2) {
                $employer->onboarding_stage = 3;
                $employer->save();
            }

            DB::commit();

            return $this->successResponse([],'company.competency.add_competency');

            // return response()->json([
            //     'status' => true,
            //     'message' => "Competency and KPI mapping stored successfully.",
            // ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error storing competency mapping: ' . $e->getMessage());

             return $this->errorResponse('company.competency.mapping_error', [
                'error' => $e->getMessage()
            ]);

            // return response()->json([
            //     'status' => false,
            //     'message' => 'Error storing competency mapping'
            // ], 500);
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

         return $this->successResponse([],'company.competency.matched', [], 201);

        // return response()->json([
        //     'status' => true,
        //     'data' => $department,
        //     'message' => "Competency matched.",
        // ], 201);

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

    public function confirmOnboardingReview(Request $request)
    {
        $employer = $request->user();
        $faqType = $request->input('faq_type');
        $onboarding = TrackEmployerOnboarding::where('employer_id', $employer->id)->first();

        $validFaqTypes = [
            'department_faq', 'employees_faq', 'supervisors_faq', 'assessment_faq',
            'projects_faq', 'trainings_faq', 'campaigns_faq', 'candidate_faq',
            'skill_gap_faq', 'competency_analysis_faq', 'department_performance_faq',
            'employee_performance_faq', 'department_development_faq',
            'employee_development_faq', 'assessment_report_faq',
            'training_report_faq', 'competency_report_faq'
        ];

        if (!in_array($faqType, $validFaqTypes)) {
            return $this->errorResponse(
                'company.competency.invalid_faq_type.',
                [],
                Response::HTTP_BAD_REQUEST
            );
            // return response()->json([
            //     'status' => false,
            //     'message' => 'Invalid FAQ type provided',
            //     'data' => []
            // ], 400);
        }

        $onboarding->{$faqType} = true;
        $onboarding->save();

         return $this->successResponse(
            ['onboarding' => $onboarding],
            'company.competency.faq_reviewed',
        );

        // return response()->json([
        //     'status' => true,
        //     'message' => "FAQ $faqType has been marked as reviewed",
        //     'data' => [
        //         'onboarding' => $onboarding
        //     ]
        // ], 200);
    }

}
<?php

namespace App\Http\Controllers\Api\v2\Talent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cv;
use App\Models\CvSkill;
use App\Models\Employee;
use App\Models\Training\CroxxTraining;
use App\Models\AssesmentSummary;
use App\Models\Assessment\CroxxAssessment;
use App\Models\Assessment\TalentAssessmentSummary;
use App\Models\VettingSummary;
use App\Models\EmployerJobcode as JobCode;
use App\Models\JobInvitation;
use App\Models\AssesmentQuestion;
use App\Models\AssesmentTalentAnswer;
use App\Models\Competency\CompetencySetup;
use App\Models\Competency\TalentCompetency;
use App\Libraries\OpenAIService;

class TalentCompetencyController extends Controller
{

    protected $openAIService;

    public function __construct(OpenAIService $openAIService)
    {
        $this->openAIService = $openAIService;
    }

    public function progress(Request $request){
        $user = $request->user();
        $careers = TalentCompetency::where('user_id', $user->id)->get();

        $labels = [];
        $datasets  = [];

        foreach($careers as $career){
            $labels[] = $career->competency;
            $assessments = CroxxAssessment::join('talent_assessment_summaries', 'croxx_assessments.id', '=', 'talent_assessment_summaries.assessment_id')
                         ->where('croxx_assessments.career_id', $career->id)
                         ->where('talent_assessment_summaries.talent_id', $user->id)
                         ->count();
            $datasets['assessment_taken'][] = $assessments;

            $trainings = CroxxTraining::join('course_libraries','croxx_trainings.id', '=', 'course_libraries.training_id')
                            ->where('croxx_trainings.career_id', $career->id)
                            ->where('course_libraries.talent_id', $user->id)
                            ->count();
            $datasets['training_libaries'][] = $trainings;
        }

        $chartData = [
            'labels' => $labels,
            'datasets' => ($datasets)
        ];

        return response()->json([
            'status' => true,
            'data' => $chartData,
            'message' => ''
        ], 200);

    }

    public function suggestion(Request $request){
        $user = $request->user();

        $suggestion = [];

        if(isset($user->cv?->job_title_name)){
            $list =  CompetencySetup::where('industry_id',  $user->cv?->industry_id)->get()->toArray();

            // $list = array_filter($competencies, function($competency) use ($user) {
            //     return $competency['industry'] === $user->cv?->industry_name;
            // });

            shuffle($list);
            $suggestion = array_slice($list,0,5);
        }

        return response()->json([
            'status' => true,
            'data' =>   $suggestion,
            'message' => ""
        ], 200);
    }

    public function jobTraining(Request $request){
        $user = $request->user();

        $per_page = $request->input('per_page', 4);
        $sort_by = $request->input('sort_by', 'created_at');
        $sort_dir = $request->input('sort_dir', 'desc');

        $careers = TalentCompetency::with('getCareerByCompetency')->where('user_id', $user->id)->get();
        $careerIds = $careers->pluck('getCareerByCompetency.id')->toArray();

        $assessments = CroxxAssessment::with('career')
                        ->whereIn('type', ['training'])
                        ->where('category', 'competency_evaluation')
                        ->whereIn('career_id', $careerIds)
                        ->limit($per_page)->get();

        return response()->json([
            'status' => true,
            'message' => "",
            'data' => $assessments
        ], 200);
    }

    public function competencyMatch(Request $request){
        $user = $request->user();

        $per_page = $request->input('per_page', 4);
        $sort_by = $request->input('sort_by', 'created_at');
        $sort_dir = $request->input('sort_dir', 'desc');

        $careers = TalentCompetency::with('getCareerByCompetency')->where('user_id', $user->id)->get();
        $careerIds = $careers->pluck('getCareerByCompetency.id')->toArray();
        array_push($careerIds, 20);

        $assessments = CroxxAssessment::with('career')->where('category', 'competency_evaluation')
                         ->whereIn('career_id', $careerIds)
                         ->limit($per_page)->get();

        foreach ($assessments as $assessment) {
            $total_duration_seconds = $assessment->questions->sum('duration');
            $assessment->total_questions = $assessment->questions->count();

            $total_answered = AssesmentTalentAnswer::where([
                'talent_id' => $user?->id,
                'assessment_id' => $assessment->id
            ])->count();
            $assessment->percentage = ($total_answered / $assessment->total_questions) * 100;
            $assessment->is_feedback  = TalentAssessmentSummary::where([
                'talent_id' => $user->id,
                'assessment_id' => $assessment->id,
                'is_published' => true
            ])->exists();
            unset($assessment->questions);
        }

        return response()->json([
            'status' => true,
            'message' => "",
            'data' => $assessments
        ], 200);
    }

    public function exploreAssessment(Request $request){
        $user = $request->user();
        $per_page = $request->input('per_page', 4);
        $sort_by = $request->input('sort_by', 'created_at');
        $sort_dir = $request->input('sort_dir', 'desc');

        $ids = TalentAssessmentSummary::where('talent_id', $user->id)->pluck('assessment_id')->toArray();

        $assessments = CroxxAssessment::whereIn('id', $ids)
                         ->limit($per_page)->get();

        foreach ($assessments as $assessment) {
            $total_duration_seconds = $assessment->questions->sum('duration');
            $assessment->total_questions = $assessment->questions->count();

            $total_answered = AssesmentTalentAnswer::where([
                'talent_id' => $user?->id,
                'assessment_id' => $assessment->id
            ])->count();
            $assessment->percentage = ($total_answered / $assessment->total_questions) * 100;
            $assessment->is_feedback  = TalentAssessmentSummary::where([
                'talent_id' => $user->id,
                'assessment_id' => $assessment->id,
                'is_published' => true
            ])->exists();
            unset($assessment->questions);
        }

        return response()->json([
            'status' => true,
            'message' => "",
            'data' => $assessments
        ], 200);
    }

    private function assessment_percentage($user, $assessment){

        $assessment->total_questions = AssesmentQuestion::where('assesment_id', $assessment->assesment_id)->count();
        $assessment->answered = AssesmentTalentAnswer::where([ 'assesment_id' => $assessment->assesment_id, 'talent_id' => $user->id ])->count();
        $assessment->percentage = $assessment->answered ? round(($assessment->answered / $assessment->total_questions ) * 100) : 0;

        return $assessment;
    }
}

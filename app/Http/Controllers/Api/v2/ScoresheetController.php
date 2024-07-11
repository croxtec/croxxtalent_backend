<?php

namespace App\Http\Controllers\Api\v2;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AssesmentTalentAnswer as TalentAnswer;
use App\Models\AssesmentScoreSheet as ScoreSheet;

use App\Models\Employee;
use App\Models\Supervisor;
use App\Models\Assessment\CompetencyQuestion;
use App\Models\Assessment\CroxxAssessment;
use App\Models\Assessment\AssignedEmployee;
use App\Models\Assessment\EmployerAssessmentFeedback;


class ScoresheetController extends Controller
{
    public function employeeList(Request $request, $id){
        $user = $request->user();

        if (is_numeric($id)) {
            $assessment = CroxxAssessment::where('id', $id)->where('is_published', 1)->firstOrFail();
        }else{
            $assessment = CroxxAssessment::where('code', $id)->where('is_published', 1)->firstOrFail();
        }

        $user_type = $user->type;
        $per_page = $request->input('per_page', 100);
        $sort_by = $request->input('sort_by', 'created_at');
        $sort_dir = $request->input('sort_dir', 'desc');
        $search = $request->input('search');
        $archived = $request->input('archived');
        $supervisor = $request->input('supervisor', 0);

        if($user->type == "employer"){
            $supervisor = $supervisor == 'yes' ? 1 : 0;
            $archived = $archived == 'yes' ? true : ($archived == 'no' ? false : null);
        }

        $summaries = AssignedEmployee::where('assessment_id', $assessment->id)
            ->where('is_supervisor',  $supervisor)
            ->when($archived ,function ($query) use ($archived) {
            if ($archived !== null ) {
                if ($archived === true ) {
                    $query->whereNotNull('archived_at');
                } else {
                    $query->whereNull('archived_at');
                }
            }
        })->when($search,function($query) use ($search) {
            $query->where('assesment_id', 'LIKE', "%{$search}%");
        })
        ->with('employee')
        ->orderBy($sort_by, $sort_dir);

        if ($per_page === 'all' || $per_page <= 0 ) {
            $results = $summaries->get();
            $summaries = new \Illuminate\Pagination\LengthAwarePaginator($results, $results->count(), -1);
        } else {
            $summaries = $summaries->paginate($per_page);
        }

        foreach ($summaries as $summary) {
            $exists = EmployerAssessmentFeedback::where([
                'assessment_id' => $summary->assessment_id,
                'employee_id' => $summary->employee_id,
                'employer_user_id' => $assessment->employer_id
            ])->exists();

            $summary->is_submited = $exists;
        }

        return response()->json([
            'status' => true,
            'message' => "Successful.",
            'data' => compact('summaries','assessment')
        ], 200);
    }

    public function assessmentResult(Request $request, $code, $talent) {
        $user = $request->user();

        if (is_numeric($code)) {
            $assessment = CroxxAssessment::where('id', $code)->where('is_published', 1)->firstOrFail();
        } else {
            $assessment = CroxxAssessment::where('code', $code)->where('is_published', 1)->firstOrFail();
        }

        if (is_numeric($talent)) {
            $talentField = 'talent_id';
        } else {
            $talentField = 'employee_id';
            $employee = Employee::where('code', $talent)->first();
            $talent = $employee->id;
        }

        $assessment->questions;

        foreach ($assessment->questions as $question) {
            $question->response = TalentAnswer::where([
                'assessment_question_id' => $question->id,
                $talentField => $talent,
                'assessment_id' => $assessment->id
            ])->first();

            if ($assessment->category != 'competency_evaluation') {
                $question->result = ScoreSheet::where([
                    'assessment_question_id' => $question->id,
                    $talentField => $talent,
                    'assessment_id' => $code
                ])->first();
            }
        }

        return response()->json([
            'status' => true,
            'message' => "",
            'data' => compact('assessment')
        ], 200);
    }

    public function assessmentFeedback(Request $request, $code, $talent){
        $user = $request->user();
        $employee = Employee::where('code', $talent)->first();

        if (is_numeric($code)) {
            $assessment = CroxxAssessment::where('id', $code)->where('is_published', 1)
                    ->firstOrFail();
        }else{
            $assessment = CroxxAssessment::where('code', $code)->where('is_published', 1)
                    ->firstOrFail();
        }

        $feedback = EmployerAssessmentFeedback::where([
            'assessment_id' => $assessment->id,
            'employee_id' => $employee->id,
            'employer_user_id' => $assessment->employer_id
        ])->first();

        return response()->json([
            'status' => true,
            'message' => "",
            'data' => compact('feedback','assessment')
        ], 200);
    }

    public function gradeAssessmentScoreSheet(Request $request, $id)
    {
        $user = $request->user();
        $assessment = CroxxAssessment::where('id', $id)->where('is_published', 1)->firstOrFail();

        $rules = [
            // 'assessment_id' => 'required|exists:assesments,id',
            'employee_code' => 'required',
            'question_id' => 'required',
            'score' => 'required|integer|between:0,4',
            'comment' => 'nullable|max:256'
        ];

        $validatedData = $request->validate($rules);
        $validatedData['assessment_question_id'] = $validatedData['question_id'];

        $employee = Employee::where('code', $validatedData['employee_code'])->first();
        $question = CompetencyQuestion::where('assessment_id', $assessment->id)
                             ->where('id', $validatedData['assessment_question_id'])->first();

        $validatedData['assessment_id'] = $assessment->id;
        $validatedData['supervisor_id'] = $user->default_company_id;
        $score['comment'] =  $validatedData['comment'] ;
        $score['employee_id'] = $employee->id ;

        unset($validatedData['employee_code']);
        unset($validatedData['question_id']);
        $score = ScoreSheet::firstOrCreate($validatedData);

        return response()->json([
            'status' => true,
            'message' => "Assesment Answer submited"
        ], 201);

    }

    public function publishSupervisorFeedback(Request $request, $id)
    {
        $user = $request->user();
        $assessment = CroxxAssessment::where('id', $id)->where('is_published', 1)->firstOrFail();

        // $this->authorize('update', [Assesment::class, $assessment]);

        $rules =[
            'employee_code' => 'required',
            'feedback' => 'required|string|min:10|max:256',
            'goal_id' => 'nullable|exists:goals,id',
        ];

        $validatedData = $request->validate($rules);
        $employee = Employee::where('code', $validatedData['employee_code'])->first();

        $feedback = EmployerAssessmentFeedback::where([
            'assessment_id' => $assessment->id,
            'employee_id' => $employee->id,
            'employer_user_id' => $assessment->employer_id
        ])->firstOrFail();

        // $total_question = Question::where('assesment_id', $id)->count();
        // $total_score = $total_question * 5;

        // $talent_score = ScoreSheet::where([
        //     'talent_id' => $request->talent,  'assesment_id' => $id
        // ])->sum('score');

        // $score_average = ((int)$talent_score / $total_score) * 5;
        if(!$feedback->supervisor_id){
            $feedback->supervisor_id = $user->default_company_id;
            $feedback->supervisor_feedback = $validatedData['feedback'];
            $feedback->goal_id = $validatedData['goal_id'] ?? null;
            // $feedback->total_score = $total_score;
            // $feedback->talent_score = $talent_score;
            // $feedback->score_average = $score_average;
            $feedback->save();
        }else{
            return response()->json([
                'status' => false,
                'message' => "Feedback already submited.",
                'data' => ""
            ], 400);
        }

        return response()->json([
            'status' => true,
            'message' => "Assesment Scoresheet  has been recorded for this talent.",
            'data' => $feedback
        ], 200);
    }
}

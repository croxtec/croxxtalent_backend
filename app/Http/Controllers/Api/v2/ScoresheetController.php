<?php

namespace App\Http\Controllers\Api\v2;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AssesmentTalentAnswer as TalentAnswer;
use App\Models\AssesmentScoreSheet as ScoreSheet;
use App\Models\AssesmentQuestion as Question;
use App\Models\Employee;
use App\Models\AssesmentSummary;
use App\Models\Assessment\CroxxAssessment;
use App\Models\Assessment\AssignedEmployee;
use App\Models\Assessment\CompetencyQuestion;
use App\Models\Assessment\EvaluationQuestion;

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
        $supervisor = $request->input('supervisor', "no");

        if($user->type == "employer"){
            $supervisor = $supervisor == 'yes' ? true : ($archived == 'no' ? false : null);
            $archived = $archived == 'yes' ? true : ($archived == 'no' ? false : null);
        }

        $summaries = AssignedEmployee::where('assessment_id', $assessment->id)
            ->where('is_supervisor', $supervisor)
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

        // foreach ($summaries as $submitted) {
        //     $submitted->talent =  Employee::where('user_id',$submitted->talent_id)->with('job_code')->first();
        // }

        return response()->json([
            'status' => true,
            'message' => "Successful.",
            'data' => compact('summaries','assessment')
        ], 200);
    }

    public function assesmentResult(Request $request, $code, $talent){
        $user = $request->user();

        if (is_numeric($code)) {
            $assessment = CroxxAssessment::where('id', $code)->where('is_published', 1)
                    ->firstOrFail();
        }else{
            $assessment = CroxxAssessment::where('code', $code)->where('is_published', 1)
                    ->firstOrFail();
        }

        $assessment->questions;

        foreach ($assessment->questions as $question) {
            $question->answer = TalentAnswer::where([
                    'assessment_question_id' => $question->id,
                    'talent_id' => $talent,
                    'assessment_id' => $assessment->id
             ])->first();

            if($assessment->category != 'competency_evaluation'){
                $question->result = ScoreSheet::where([
                        'assessment_question_id' => $question->id,
                        'talent_id' => $talent,
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

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storeTalentAnswer(Request $request)
    {
        $user = $request->user();

        $rules =[
            'assessment_id' => 'required|exists:assesments,id',
            'question_id' => 'required'
            // 'question_id' => 'required|exists:assesment_questions,id'
        ];

        $searchData = $request->validate($rules);
        $searchData['talent_id'] = $user->id;
        $searchData['assessment_question_id'] = $searchData['question_id'];
        unset($searchData['question_id']);

        $assessment = CroxxAssessment::where('id', $searchData['assessment_id'])
                            ->where('is_published', 1)->firstOrFail();

        if ($assessment->category == 'competency_evaluation') {
            $question = EvaluationQuestion::where('assessment_id', $assessment->id)
                            ->where('id', $searchData['assessment_question_id'])->firstOrFail();
        } else {
            $question = CompetencyQuestion::where('assessment_id', $assessment->id)
                            ->where('id', $searchData['assessment_question_id'])->firstOrFail();
        }

        // info($searchData);
        $answer = TalentAnswer::firstOrCreate($searchData);

        if($assessment->category != 'competency_evaluation') {
            $request->validate([
                'answer' => 'required|min:10|max:250'
             ]);

            $answer->comment = $request->answer;

            if ($request->hasFile('file') && $request->file('file')->isValid()) {
                $extension = $request->file('file')->extension();
                $filename = $user->id . '-' . time() . '-' . Str::random(32);
                $filename = "{$filename}.$extension";
                $year = date('Y');
                $month = date('m');
                $rel_upload_path    = "assesment/{$year}/{$month}";
                if ( config('app.env') == 'local')  $rel_upload_path = "local/{$rel_upload_path}"; // dir for dev environment test uploads

                // do upload
                $uploaded_file_path = $request->file('file')->storeAs($rel_upload_path, $filename);
                Storage::setVisibility($uploaded_file_path, 'public'); //set file visibility to  "public"

                // Update with the newly update file
                $answer->upload = $request->comment;
            }
        }

        if($assessment->category == 'competency_evaluation'){
            $request->validate([
                'answer' => 'required|in:option1,option2,option3,option4'
            ]);

            $answer->option = $request->answer;
        }

        $answer->save();

        return response()->json([
            'status' => true,
            'data' => $answer,
            'message' => ""
        ], 201);
    }


    public function publishTalentAnswers(Request $request, $id)
    {
        $user = $request->user();
        // $this->authorize('update', [Assesment::class, $assessment]);
        $summary = AssesmentSummary::firstOrCreate([
            'assesment_id' => $id,
            'talent_id' => $user->id
        ]);

        if($summary->is_published){
            $summary->talent_feedback = $request->feedback;
            $summary->is_published = true;
            $summary->save();
        }

        return response()->json([
            'status' => true,
            'message' => "Assessment submitted.",
            'data' =>$summary
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function storeAssesmentScoreSheet(Request $request)
    {
        $user = $request->user();

        $rules =[
            'assesment_id' => 'required|exists:assesments,id',
            'talent_id' => 'required|exists:users,id',
            'question_id' => 'required|exists:assesment_questions,id',
            'score' => 'required|integer|between:1,5'
        ];

        $searchData = $request->validate($rules);
        $searchData['manager_id'] = $user->id;
        $searchData['assesment_question_id'] = $searchData['question_id'];
        unset($searchData['question_id']);

        $question = Question::find($searchData['assesment_question_id']);
        $score = ScoreSheet::firstOrCreate($searchData);

        $score->comment = $request->comment;

        $score->save();

        return response()->json([
            'status' => true,
            'message' => "Assesment Answer submited"
        ], 201);

    }


    public function publishManagementFeedback(Request $request, $id)
    {
        $user = $request->user();
        // $this->authorize('update', [Assesment::class, $assessment]);
        $summary = AssesmentSummary::where([
            'assesment_id' => $id,
            'talent_id' => $request->talent
        ])->firstOrFail();

        $total_question = Question::where('assesment_id', $id)->count();
        $total_score = $total_question * 5;

        $talent_score = ScoreSheet::where([
            'talent_id' => $request->talent,  'assesment_id' => $id
        ])->sum('score');

        $score_average = ((int)$talent_score / $total_score) * 5;

        $summary->manager_id = $user->id;
        $summary->is_published = 1;
        $summary->total_score = $total_score;
        $summary->talent_score = $talent_score;
        $summary->score_average = $score_average;
        $summary->manager_feedback = $request->feedback;
        $summary->save();

        return response()->json([
            'status' => true,
            'message' => "Assesment Scoresheet  has been recorded for this talent.",
            'data' =>$summary
        ], 200);
    }
}

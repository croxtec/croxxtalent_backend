<?php

namespace App\Http\Controllers\Api\v2;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AssesmentTalentAnswer as TalentAnswer;
use App\Models\AssesmentScoreSheet as ScoreSheet;
use App\Models\AssesmentQuestion as Question;
use App\Models\AssesmentSummary;
use App\Models\Assesment;

class ScoresheetController extends Controller
{

    public function employeeList(Request $request, $id){
        $user = $request->user();
        $assesment = Assesment::where('id', $id)->firstOrFail();

        $per_page = $request->input('per_page', 100);
        $sort_by = $request->input('sort_by', 'created_at');
        $sort_dir = $request->input('sort_dir', 'desc');
        $search = $request->input('search');
        $archived = $request->input('archived');

        $archived = $archived == 'yes' ? true : ($archived == 'no' ? false : null);

        $summaries = AssesmentSummary::where('assesment_id', $id)
            ->when($archived ,function ($query) use ($archived) {
            if ($archived !== null ) {
                if ($archived === true ) {
                    $query->whereNotNull('archived_at');
                } else {
                    $query->whereNull('archived_at');
                }
            }
        })->when( $search,function($query) use ($search) {
            $query->where('assesment_id', 'LIKE', "%{$search}%");
        })->orderBy($sort_by, $sort_dir);

        if ($per_page === 'all' || $per_page <= 0 ) {
            $results = $summaries->get();
            $summaries = new \Illuminate\Pagination\LengthAwarePaginator($results, $results->count(), -1);
        } else {
            $summaries = $summaries->paginate($per_page);
        }

        return response()->json([
            'status' => true,
            'message' => "Successful.",
            'data' => compact('assesment', 'summaries')
        ], 200);
    }

    public function assesmentResult(Request $request, $code, $talent){
        $user = $request->user();
        $assesment = Assesment::where('id', $code)->with('questions')->firstOrFail();
        $answers = TalentAnswer::where([ 'talent_id' => $talent, 'assesment_id' => $code ])->get();
        $results = ScoreSheet::where([ 'talent_id' => $talent, 'assesment_id' => $code ])->get();

        return response()->json([
            'status' => true,
            'message' => "Successful.",
            'data' => compact('assesment', 'answers','results')
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
            'assesment_id' => 'required|exists:assesments,id',
            'question_id' => 'required|exists:assesment_questions,id',
        ];

        $searchData = $request->validate($rules);
        $searchData['talent_id'] = $user->id;
        $searchData['assesment_question_id'] = $searchData['question_id'];
        unset($searchData['question_id']);

        $question = Question::find($searchData['assesment_question_id']);
        $answer = TalentAnswer::firstOrCreate($searchData);

        if($question->type == 'text') $answer->comment = $request->answer;
        if($question->type == 'radio') $answer->option = $request->answer;
        if($question->type == 'checkbox') $answer->options = $request->answer; //json
        if($question->type == 'file'){
            $answer->comment = $request->comment;
        }
        // if($question->type == 'text') $answer->comment = $request->comment;

        $answer->save();

        return response()->json([
            'status' => true,
            'message' => "Assesment Answer submited"
        ], 201);
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

    public function publishTalentAnswers(Request $request, $id)
    {
        $user = $request->user();
        // $this->authorize('update', [Assesment::class, $assesment]);
        $summary = AssesmentSummary::where([
            'assesment_id' => $id,
            'talent_id' => $user->id
        ])->firstOrFail();

        $summary->talent_feedback = $request->feedback;
        $summary->is_published = true;
        $summary->save();

        return response()->json([
            'status' => true,
            // 'message' => "Assesment \"{$assesment->name}\" publish successfully.",
            'data' =>$summary
        ], 200);
    }

    public function publishManagementFeedback(Request $request, $id)
    {
        $user = $request->user();
        // $this->authorize('update', [Assesment::class, $assesment]);
        $summary = AssesmentSummary::where([
            'assesment_id' => $id,
            'talent_id' => $request->talent
        ])->firstOrFail();

        $summary->manager_feedback = $request->feedback;
        $summary->manager_id = $user->id;
        $summary->save();

        return response()->json([
            'status' => true,
            // 'message' => "Assesment \"{$assesment->name}\" publish successfully.",
            'data' =>$summary
        ], 200);
    }
}

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

        $assesment = Assesment::where('id', $id)
                        ->with('summary')->firstOrFail();

        return response()->json([
            'status' => true,
            'message' => "Successful.",
            'data' => $assesment
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

        if($question->type == 'text') $answer->comment = $request->comment;
        // if($question->type == 'text') $answer->comment = $request->comment;
        // if($question->type == 'text') $answer->comment = $request->comment;
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
            'question_id' => 'required|exists:assesment_questions,id',
            'score' => 'required|integer|between:1,5'
        ];

        $searchData = $request->validate($rules);
        $searchData['manager_id'] = $user->id;
        $searchData['assesment_question_id'] = $searchData['question_id'];
        unset($searchData['question_id']);

        $question = Question::find($searchData['assesment_question_id']);
        $answer = ScoreSheet::firstOrCreate($searchData);

        $answer->comment = $request->comment;
        $answer->score = $request->score;

        $answer->save();

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

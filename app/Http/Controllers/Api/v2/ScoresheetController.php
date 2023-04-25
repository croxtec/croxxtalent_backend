<?php

namespace App\Http\Controllers\Api\v2;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AssesmentTalentAnswer as TalentAnswer;
use App\Models\AssesmentScoreSheet as ScoreSheet;
use App\Models\AssesmentQuestion as Question;

class ScoresheetController extends Controller
{
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
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
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
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}

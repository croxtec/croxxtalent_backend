<?php

namespace App\Http\Controllers\Api\v2;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AssesmentQuestion as Question;
use App\Models\Assesment;

class AssesmentQuestionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $rules = [
            'assesment_id' => 'required',
            'type' => 'required',
            'question' => 'required',
            'desctiption' => 'nullable',
            'option1' => 'nullable|max:50',
            'option2' => 'nullable|max:50',
            'option3' => 'nullable|max:50',
            'option4' => 'nullable|max:50',
        ];

        $validatedData = $request->validate($rules);
        // $question['assesment_id'] = $id;
        $question  = Question::create($validatedData);

        return response()->json([
            'status' => true,
            'message' => "Assesment Question created successfully.",
            'data' => Question::find($question->id)
        ], 201);
    }

    public function archive($id)
    {
        $question = Question::findOrFail($id);

        // $this->authorize('delete', [Question::class, $question]);

        $question->archived_at = now();
        $question->save();

        return response()->json([
            'status' => true,
            'message' => "Assesment \"{$question->question}\" archived successfully.",
            'data' => Question::find($question->id)
        ], 200);
    }

    /**
     * Unarchive the specified resource from archived storage.
     *
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function unarchive($id)
    {
        $question = Question::findOrFail($id);

        // $this->authorize('delete', [Question::class, $question]);

        $question->archived_at = null;
        $question->save();

        return response()->json([
            'status' => true,
            'message' => "Assesment \"{$question->question}\" unarchived successfully.",
            'data' => Question::find($question->id)
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $question = Question::findOrFail($id);

        // $this->authorize('delete', [Question::class, $question]);

        $name = $question->question;
        // check if the record is linked to other records
        $relatedRecordsCount = related_records_count(Question::class, $question);

        if ($relatedRecordsCount <= 0) {
            $question->delete();
            return response()->json([
                'status' => true,
                'message' => "Assesment \"{$name}\" deleted successfully.",
            ], 200);
        } else {
            return response()->json([
                'status' => false,
                'message' => "The \"{$name}\" record cannot be deleted because it is associated with {$relatedRecordsCount} other record(s). You can archive it instead.",
            ], 400);
        }
    }
}

<?php

namespace App\Http\Controllers\Api\v2\Operations;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AssesmentQuestion as Question;
use App\Models\Assesment;
use App\Models\Assessment\CompetencyQuestion;
use App\Models\Assessment\CroxxAssessment;
use App\Models\Assessment\EvaluationQuestion;
use App\Models\EvaluationQuestionBank as QuestionBank;
use App\Services\OpenAIService;
use App\Traits\ApiResponseTrait;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Response;

class AssessmentQuestionController extends Controller
{

    use ApiResponseTrait;
    protected $openAIService;

    public function __construct(OpenAIService $openAIService)
    {
        $this->openAIService = $openAIService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function generate(Request $request)
    {
        $rules = [
            'title' => 'required|string',
            'competencies' => 'required|array',
            'competencies.*' => 'string',
            'level' => 'required|max:50|in:beginner,intermediate,advance,expert',
            'total_question' => 'required|integer|between:1,10',
        ];

        $validatedData = $request->validate($rules);

        $questions = QuestionBank::whereIn('competency_name', $validatedData['competencies'])
                            ->where('level', $validatedData['level'])
                            ->limit($validatedData['total_question'])
                            ->get();

        if ($questions->count() < $validatedData['total_question']) {
            $generatedQuestions = $this->openAIService->generateAssessmentQuestion(
                $validatedData['title'],
                $validatedData['competencies'],
                $validatedData['level'],
                $validatedData['total_question']
            );

            // info($generatedQuestions);
            // Validate and store the generated questions
            foreach ($generatedQuestions as $question) {
                if (isset($question['competency_name']) && isset($question['question']) && isset($question['option1'])
                    && isset($question['option2']) && isset($question['answer'])) {
                    QuestionBank::create($question);
                }
            }

            // Refresh the questions after adding the newly generated ones
            $questions = QuestionBank::whereIn('competency_name', $validatedData['competencies'])
                            ->where('level', $validatedData['level'])
                            ->limit($validatedData['total_question'])
                            ->get();
        }

        return $this->successResponse(
            $questions,
            'services.questions.generated',
            [],
            201
        ); 

        // return response()->json([
        //     'status' => true,
        //     'message' => "Assessment questions generated successfully.",
        //     'data' => $questions,
        // ], 201);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    private function getValidationRules()
    {
        return [
            'assessment_id' => 'required',
            'category' => 'required|in:competency_evaluation,peer_review,experience',
            'type' => 'nullable',
            'question' => 'required|min:10',
            'option1' => 'required_if:category,competency_evaluation|max:150',
            'option2' => 'required_if:category,competency_evaluation|max:150',
            'option3' => 'nullable|max:150',
            'option4' => 'nullable|max:150',
            'answer' => 'required_if:category,competency_evaluation|in:option1,option2,option3,option4',
            'description' => 'nullable',
        ];
    }

    public function storeAssessment(Request $request)
    {
        $validatedData = $request->validate($this->getValidationRules());

        try {
            if ($validatedData['category'] === 'competency_evaluation') {
                $question = EvaluationQuestion::create($validatedData);
                $questionModel = EvaluationQuestion::find($question->id);
            } else {
                $question = CompetencyQuestion::create($validatedData);
                $questionModel = CompetencyQuestion::find($question->id);
            }

            return $this->successResponse(
                $questionModel,
                'services.questions.created',
                [],
                201
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'services.questions.create_error',
                ['error' => $e->getMessage()],
            );
        }
    }

    public function updateAssessment(Request $request, $id)
    {
        try {
            // Get authenticated user and find their assessment
            $user = $request->user();
            $employerId = $user->id;

            $assessment = CroxxAssessment::where('id', $request->assessment_id)
                ->where('employer_id', $employerId)
                ->firstOrFail();

            // Find question based on assessment category
            $question = null;
            if ($assessment->category === 'competency_evaluation') {
                $question = EvaluationQuestion::where('id', $id)
                    ->where('assessment_id', $assessment->id)
                    ->firstOrFail();
            } else {
                $question = CompetencyQuestion::where('id', $id)
                    ->where('assessment_id', $assessment->id)
                    ->firstOrFail();
            }

            $rules = $this->getValidationRules();
            $rules['assessment_id'] = 'sometimes|required';

            // Validate request data
            $validatedData = $request->validate($rules);

            // Update the question
            $question->update($validatedData);

            return $this->successResponse(
                $question->fresh(),
                'services.questions.updated'
            );
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse(
                'services.questions.not_found'
            );
            // return response()->json([
            //     'status' => false,
            //     'message' => "Assessment question not found.",
            // ], 400);
        } catch (\Exception $e) {
            return $this->errorResponse(
                'services.questions.create_error',
                ['error' => $e->getMessage()],
            );
            // return response()->json([
            //     'status' => false,
            //     'message' => "Failed to update assessment question.",
            //     'error' => $e->getMessage()
            // ], 500);
        }
    }


    public function archive($code ,$id)
    {
        $user = auth()->user();
        $employerId = $user->id;
        if (is_numeric($id)) {
            $assessment = CroxxAssessment::where('id', $code)
                ->where('employer_id', $employerId)->firstOrFail();
        } else {
            $assessment = CroxxAssessment::where('code', $code)
                ->where('employer_id', $employerId)->firstOrFail();
        }

        if ($assessment->category == 'competency_evaluation') {
            $question = EvaluationQuestion::findOrFail($id);
        } else {
            $question = CompetencyQuestion::findOrFail($id);
        }
        // $this->authorize('delete', [Question::class, $question]);

        $question->archived_at = now();
        $question->save();

        return $this->successResponse(
            $question,
            'services.questions.archived'
        );
    }

    /**
     * Unarchive the specified resource from archived storage.
     *
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function unarchive($code, $id)
    {
        $user = auth()->user();
        $employerId = $user->id;

        if (is_numeric($id)) {
            $assessment = CroxxAssessment::where('id', $code)
                ->where('employer_id', $employerId)->firstOrFail();
        } else {
            $assessment = CroxxAssessment::where('code', $code)
                ->where('employer_id', $employerId)->firstOrFail();
        }

        if ($assessment->category == 'competency_evaluation') {
            $question = EvaluationQuestion::findOrFail($id);
        } else {
            $question = CompetencyQuestion::findOrFail($id);
        }

        // $this->authorize('delete', [Question::class, $question]);

        $question->archived_at = null;
        $question->save();

        return $this->successResponse(
            $question,
            'services.questions.restored'
        );
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
            return $this->successResponse(
                null,
                'services.questions.deleted',
                ['name' => $name]
            );
        }
    
        return $this->errorResponse(
            'services.questions.delete_error',
            ['name' => $name, 'count' => $relatedRecordsCount],
            Response::HTTP_BAD_REQUEST
        );
    }
}

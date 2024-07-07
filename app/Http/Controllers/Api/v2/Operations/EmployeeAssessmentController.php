<?php

namespace App\Http\Controllers\Api\v2\Operations;

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Assessment\CroxxAssessment;
use App\Models\Assessment\AssignedEmployee;
use App\Models\AssesmentTalentAnswer as TalentAnswer;
use App\Models\Employee;
use App\Models\Supervisor;
use App\Models\Assessment\CompetencyQuestion;
use App\Models\Assessment\EvaluationQuestion;
use App\Models\Assessment\EmployerAssessmentFeedback;


class EmployeeAssessmentController extends Controller
{
     /**
     * Display the Employee resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function employee(Request $request, $code)
    {
        $user = $request->user();

        $employee = Employee::where('code', $code)->firstOrFail();

        if($user->type == 'talent'){
           if(!$this->validateEmployee($user,$employee)){
                return response()->json([
                    'status' => false,
                    'message' => 'Unautourized Access'
                ], 401);
           }
        }

        $assessments = DB::table('croxx_assessments')
                        ->join('assigned_employees', 'croxx_assessments.id', '=', 'assigned_employees.assessment_id')
                        ->where('croxx_assessments.employer_id', $employee->employer_id)
                        ->where('assigned_employees.employee_id', $employee->id)
                        ->select('croxx_assessments.*')
                        ->latest()
                        ->get();

       return response()->json([
            'status' => true,
            'message' => "",
            'data' => $assessments
        ], 200);
    }

    private function validateEmployee($user, $employee){
        // Get The current employee information
        $current_company = Employee::where('id', $user->default_company_id)
                    ->where('user_id', $user->id)->with('supervisor')->first();

        if($current_company->id === $employee->id){
            return true;
        }
        if($current_company->supervisor) {
            $supervisor =  $$current_company->supervisor;
            // info([$supervisor, $employee]);
            return true;
            if($supervisor->type == 'role' && $employee->department_role_id === $supervisor->department_role_id){
                return true;
            }
            if($supervisor->type == 'department' && $employee->job_code_id === $supervisor->department_id){
                return true;
            }
            return false;
        }
        return false;
    }

    public function feedbacks(Request $request, $code)
    {
        $user = $request->user();
        $supervisor = $request->input('supervisor', "no"); // Default to false if not provided

        $employee = Employee::where('code', $code)->firstOrFail();
        if($user->type == 'talent'){
           if(!$this->validateEmployee($user,$employee)){
                return response()->json([
                    'status' => false,
                    'message' => 'Unautourized Access'
                ], 401);
           }
        }

        if ($supervisor == "yes") {
            $feedbacks = EmployerAssessmentFeedback::where('supervisor_id', $employee->id)->get();
        } else {
            $feedbacks = EmployerAssessmentFeedback::where('employee_id', $employee->id)->get();
        }

        return response()->json([
            'status' => true,
            'message' => "",
            'data' => $feedbacks
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

        $rules = [
            'assessment_id' => 'required|exists:assesments,id',
            'question_id' => 'required',
            // 'question_id' => 'required|exists:assesment_questions,id'
        ];

        $searchData = $request->validate($rules);
        $assessment = CroxxAssessment::where('id', $searchData['assessment_id'])
                            ->where('is_published', 1)->firstOrFail();
        $searchData['assessment_question_id'] = $searchData['question_id'];
        unset($searchData['question_id']);

        if($assessment->type == 'company'){
            $employee = Employee::where('id', $user->default_company_id)
                     ->where('user_id', $user->id)->first();
            $searchData['employee_id'] = $employee->id;
        } else{
            $searchData['talent_id'] = $user->id;
        }

        if ($assessment->category == 'competency_evaluation') {
            $question = EvaluationQuestion::where('assessment_id', $assessment->id)
                            ->where('id', $searchData['assessment_question_id'])->firstOrFail();
        }

        if($assessment->category != 'competency_evaluation') {
            $question = CompetencyQuestion::where('assessment_id', $assessment->id)
                            ->where('id', $searchData['assessment_question_id'])->firstOrFail();
        }

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
            $answer->evaluation_result = ($question->answer  === $request->answer);
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

        $assessment = CroxxAssessment::where('id', $id)->where('is_published', 1)->firstOrFail();
        $employee = Employee::where('id', $user->default_company_id)->where('user_id', $user->id)->first();

        $feedback = EmployerAssessmentFeedback::firstOrCreate([
            'assessment_id' => $assessment->id,
            'employee_id' => $employee->id,
            'employer_user_id' => $assessment->employer_id
        ]);

        if(!$feedback->is_published){
            $feedback->employee_feedback = $request->feedback;
            $feedback->time_taken = $request->time_taken;
            $feedback->is_published = true;
            $feedback->save();
        }else{
            return response()->json([
                'status' => false,
                'message' => "Assessment already submited.",
                'data' => ""
            ], 400);
        }

        return response()->json([
            'status' => true,
            'message' => "Assessment submitted.",
            'data' =>$feedback
        ], 200);
    }

}

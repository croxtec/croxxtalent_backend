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
        $show = $request->input('show', "personal");
        $per_page = $request->input('per_page', 12);

        $employee = Employee::where('code', $code)->firstOrFail();

        if($user->type == 'talent'){
           if(!$this->validateEmployee($user,$employee)){
                return response()->json([
                    'status' => false,
                    'message' => 'Unautourized Access'
                ], 401);
           }
        }

        $assessments = CroxxAssessment::withCount('questions')
                        ->with('competencies')
                        ->join('assigned_employees', 'croxx_assessments.id', '=', 'assigned_employees.assessment_id')
                        ->where('croxx_assessments.employer_id', $employee?->employer_id)
                        ->when($show == 'personal', function($query) use ($employee){
                            $query->where('assigned_employees.employee_id', $employee?->id)
                                    ->where('assigned_employees.is_supervisor', 0);
                        })
                        ->when($show == 'supervisor', function($query) use ($employee){
                            $query->where('assigned_employees.employee_id', $employee?->id)
                                    ->where('assigned_employees.is_supervisor', 1);
                        })
                        ->select('croxx_assessments.*', 'assigned_employees.is_supervisor')
                        ->latest()
                        ->paginate($per_page);



        foreach ($assessments as $assessment) {
            $total_duration_seconds = $assessment->questions->sum('duration');
            $assessment->total_questions = $assessment->questions->count();

            $total_answered = TalentAnswer::where([
                'employee_id' => $employee?->id,
                'assessment_id' => $assessment->id
            ])->count();

            $assessment->percentage = ($total_answered / $assessment->total_questions) * 100;
            // Convert the total duration in minutes to hours, minutes, and seconds
            $minutes = floor($total_duration_seconds / 60);
            $seconds = $total_duration_seconds % 60;

            $estimated_time = sprintf('%d minutes %d seconds', $minutes, $seconds);
            $assessment->estimated_time = $estimated_time;

            unset($assessment->questions);

            $isSubmitted = EmployerAssessmentFeedback::where([
                'assessment_id' => $assessment->id,
                'employee_id' => $employee?->id,
                'employer_user_id' => $assessment?->employer_id,
                'is_published' => true
            ])->exists();

            $isFeedbacked = EmployerAssessmentFeedback::where([
                'assessment_id' => $assessment->id,
                'employee_id' => $employee?->id,
                'employer_user_id' => $assessment?->employer_id,
                'is_published' => true
            ])->exists();

            $assessment->is_feedback = $isFeedbacked;
            $assessment->is_submited = $isSubmitted;
        }

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

        if($current_company->id === $employee?->id){
            return true;
        }
        if($current_company->supervisor) {
            $supervisor =  $current_company->supervisor;
            // info([$supervisor, $employee]);
            return true;
            if($supervisor->type == 'role' && $employee?->department_role_id === $supervisor->department_role_id){
                return true;
            }
            if($supervisor->type == 'department' && $employee?->job_code_id === $supervisor->department_id){
                return true;
            }
            return false;
        }
        return false;
    }

    public function feedbacks(Request $request, $code)
    {
        $user = $request->user();

        $per_page = $request->input('per_page', 5);
        $show = $request->input('show', "personal");

        $employee = Employee::where('code', $code)->firstOrFail();
        if($user->type == 'talent'){
           if(!$this->validateEmployee($user,$employee)){
                return response()->json([
                    'status' => false,
                    'message' => 'Unautourized Access'
                ], 401);
           }
        }

        if ($show == "supervisor") {
            $feedbacks = EmployerAssessmentFeedback::where('supervisor_id', $employee?->id)
                ->with('employee', 'supervisor')->latest()->paginate($per_page);
        } else {
            $feedbacks = EmployerAssessmentFeedback::where('employee_id', $employee?->id)
                ->with('employee', 'supervisor')->latest()->paginate($per_page);
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
            'assessment_id' => 'required',
            'question_id' => 'required',
        ];

        $searchData = $request->validate($rules);
        $assessment = CroxxAssessment::where('id', $searchData['assessment_id'])
                            ->where('is_published', 1)->firstOrFail();

        $searchData['assessment_question_id'] = $searchData['question_id'];
        unset($searchData['question_id']);

        if($assessment->type == 'company' || $assessment->type == 'supervisor'){
            $employee = Employee::where('id', $user->default_company_id)
                     ->where('user_id', $user->id)->first();
            $searchData['employee_id'] = $employee?->id;

            $isPublished = EmployerAssessmentFeedback::where([
                'assessment_id' => $assessment->id,
                'employee_id' => $employee?->id,
                'employer_user_id' => $assessment?->employer_id,
                'is_published' => true
            ])->exists();

            if ($isPublished) {
                return response()->json([
                    'status' => false,
                    'message' => "Assessment already submitted.",
                    'data' => ""
                ], 400);
            }
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
            'employee_id' => $employee?->id,
            'employer_user_id' => $assessment?->employer_id
        ]);

        // $total_question =  $assessment->questions->count();
        // $total_score = $total_question * 4;

        // $talent_score = ScoreSheet::where([
        //    'employee_id' => $employee?->id,
        //    'assessment_id' =>  $assessment->id
        // ])->sum('score');
        // $score_average = ((int)$talent_score / $total_score) * 100;
        // $feedback->total_score = $total_score;
        // $feedback->talent_score = $talent_score;
        // $feedback->score_average = $score_average;

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

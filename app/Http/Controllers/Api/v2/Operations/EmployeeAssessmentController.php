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
use App\Models\Assessment\PeerReview;
use App\Models\Assessment\TalentAssessmentSummary;
use Illuminate\Support\Facades\Storage;
use App\Traits\ApiResponseTrait;
use App\Services\MediaService;
use Illuminate\Support\Facades\Log;

class EmployeeAssessmentController extends Controller
{

    protected $mediaService;

    public function __construct(MediaService $mediaService)
    {
        $this->mediaService = $mediaService;
    }

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
        $validation_result = validateEmployeeAccess($user, $employee);

            // If validation fails, return the response
            if ($validation_result !== true) {
                return $validation_result;
            }
        }

        // First, get distinct assessment IDs to prevent duplication
        $assessmentIds = DB::table('assigned_employees')
                        ->join('croxx_assessments', 'croxx_assessments.id', '=', 'assigned_employees.assessment_id')
                        ->where('croxx_assessments.employer_id', $employee?->employer_id)
                        ->when($show == 'personal', function($query) use ($employee){
                            $query->where('assigned_employees.employee_id', $employee?->id)
                                ->where('assigned_employees.is_supervisor', 0);
                        })
                        ->when($show == 'supervisor', function($query) use ($employee){
                            $query->where('assigned_employees.employee_id', $employee?->id)
                                ->where('assigned_employees.is_supervisor', 1);
                        })
                        ->distinct()
                        ->pluck('croxx_assessments.id');

        // Now fetch the actual assessment data using those IDs
        $assessments = CroxxAssessment::withCount('questions')
                        ->with('competencies')
                        ->whereIn('id', $assessmentIds)
                        ->latest()
                        ->paginate($per_page);

        foreach ($assessments as $assessment) {
            // Add assignment type info (is_supervisor flag)
            $assignmentInfo = DB::table('assigned_employees')
                                ->where('assessment_id', $assessment->id)
                                ->where('employee_id', $employee->id)
                                ->first();

            $assessment->is_supervisor = $assignmentInfo ? $assignmentInfo->is_supervisor : 0;

            // Rest of the assessment enrichment
            $total_duration_seconds = $assessment->questions->sum('duration');
            $assessment->total_questions = $assessment->questions->count() ?? 1;

            $total_answered = TalentAnswer::where([
                'employee_id' => $employee?->id,
                'assessment_id' => $assessment->id
            ])->count();

            $assessment->percentage = ($total_answered / ($assessment->total_questions ?? 1)) * 100;
            $minutes = floor($total_duration_seconds / 60);
            $seconds = $total_duration_seconds % 60;

            $estimated_time = sprintf('%d minutes %d seconds', $minutes, $seconds);
            $assessment->estimated_time = $estimated_time;

            unset($assessment->questions);

            if($assessment->category === 'peer_review') {
                // Get all peer review data with a single query and build collections
                $peerReviews = PeerReview::where('assessment_id', $assessment->id)
                                        ->where(function($query) use ($employee) {
                                            $query->where('employee_id', $employee->id)
                                                ->orWhere('reviewer_id', $employee->id);
                                        })
                                        ->with(['employee:id,name,job_code_id,department_role_id,photo_url,code',
                                                'reviewer:id,name,job_code_id,department_role_id,photo_url,code'])
                                        ->get();

                // Self assessment (I am reviewing myself)
                $assessment->self_assessment = $peerReviews->where('employee_id', $employee->id)
                                                        ->where('reviewer_id', $employee->id)
                                                        ->first();

                // People who are reviewing me (I am being reviewed by) - excluding self
                $assessment->reviewers = $peerReviews->where('employee_id', $employee->id)
                                                    ->where('reviewer_id', '!=', $employee->id)
                                                    ->map(function ($review) {
                                                        $reviewer = $review->reviewer;
                                                        $reviewer->status = $review->status;
                                                        $reviewer->completed_at = $review->completed_at;
                                                        return $reviewer;
                                                    })->values();
                // People I am reviewing (I am reviewer for) - excluding self
                $assessment->reviewees = $peerReviews->where('reviewer_id', $employee->id)
                                                    ->where('employee_id', '!=', $employee->id)
                                                    ->map(function ($review) {
                                                        $employee = $review->employee;
                                                        $employee->status = $review->status;
                                                        $employee->completed_at = $review->completed_at;
                                                        return $employee;
                                                    })->values();
            }

            $feedback = EmployerAssessmentFeedback::where([
                'assessment_id' => $assessment->id,
                'employee_id' => $employee?->id,
                'employer_user_id' => $assessment?->employer_id,
                'is_published' => true
            ])->first();

            $assessment->is_feedback = $feedback?->supervisor_id ? true : false;
            $assessment->is_submited = !is_null($feedback);
        }

        return response()->json([
            'status' => true,
            'message' => "",
            'data' => $assessments
        ], 200);
    }

    public function feedbacks(Request $request, $code)
    {
        $user = $request->user();

        $per_page = $request->input('per_page', 5);
        $show = $request->input('show', "personal");

        $employee = Employee::where('code', $code)->firstOrFail();
        if($user->type == 'talent'){
           $validation_result = validateEmployeeAccess($user, $employee);

            if ($validation_result !== true) {
                return $validation_result;
            }
        }

        if ($show == "supervisor") {
            $feedbacks = EmployerAssessmentFeedback::where('supervisor_id', $employee?->id)
                ->with('employee', 'supervisor', 'assessment')
                ->latest()->paginate($per_page);
        } else {
            $feedbacks = EmployerAssessmentFeedback::where('employee_id', $employee?->id)
                        ->where('supervisor_id', '!=', 0)
                        ->with('employee', 'supervisor','assessment')
                        ->latest()->paginate($per_page);
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

        $employee = null;
        $employerId = null;

        if($assessment->type == 'company' || $assessment->type == 'supervisor'){
            $employee = Employee::where('id', $user->default_company_id)
                     ->where('user_id', $user->id)->first();
            $searchData['employee_id'] = $employee?->id;
            $employerId = $employee?->employer_id ?? $assessment->employer_id;

            $isPublished = EmployerAssessmentFeedback::where([
                'assessment_id' => $assessment->id,
                'employee_id' => $employee?->id,
                'employer_user_id' => $assessment?->employer_id,
                'is_published' => true
            ])->exists();

             if ($isPublished) {
                return response()->json([
                    'status' => false,
                    'message' => 'Assessment feedback has already been published'
                ], 422);
            }
        } else{
            $searchData['talent_id'] = $user->id;
            TalentAssessmentSummary::firstOrCreate([
                'talent_id' => $user->id,
                'assessment_id' => $assessment->id
            ]);
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

        // Handle non-evaluation assessments (with file uploads)
        if($assessment->category != 'competency_evaluation') {
            $request->validate([
                'answer' => 'required|min:10|max:250',
                'files.*' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png,gif|max:10240', // 10MB max per file
                'file' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png,gif|max:10240', // Support both single and multiple files
            ]);

            $answer->comment = $request->answer;

            // Handle file uploads using the new Media system
            $uploadedMedia = $this->handleFileUploads($request, $answer, $user, $employee, $employerId, $assessment);
            
            // If there were uploads, update the legacy fields for backward compatibility
            // if (!empty($uploadedMedia)) {
            //     $answer->upload = $request->input('comment', 'Assessment response with attachments');
            //     $answer->document = $uploadedMedia->first()->file_url ?? null; // Store first file URL for legacy support
            // }
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

        if($assessment->type == 'company' || $assessment->type == 'supervisor'){
            $employee = Employee::where('id', $user->default_company_id)->where('user_id', $user->id)->first();
            $talentField = 'employee_id';
            $talent = $employee->id;
            $feedback = EmployerAssessmentFeedback::firstOrCreate([
                'assessment_id' => $assessment->id,
                'employee_id' => $employee?->id,
                'employer_user_id' => $assessment?->employer_id
            ]);
        }else{
            $talentField = 'talent_id';
            $talent = $user->id;
            $feedback = TalentAssessmentSummary::where([
                'talent_id' => $user->id,
                'assessment_id' => $assessment->id
            ])->first();
        }

        if($assessment->category == 'competency_evaluation'){
            $total_question =  $assessment->questions->count();
            $total_score = $total_question * 1;

            $assessment_score =  TalentAnswer::where([
                'assessment_id' => $assessment->id,
                $talentField => $talent,
            ])->sum('evaluation_result');

            $graded_score = ((int)$assessment_score / $total_score ?? 1) * 100;
            $graded_score = (int)$graded_score;

            // Determine which feedback message to use
            if ($graded_score >= 95) {
                $messageKey = 'services.feedbacks.score_messages.exceptional';
            } elseif ($graded_score >= 85) {
                $messageKey = 'services.feedbacks.score_messages.fantastic';
            } elseif ($graded_score >= 75) {
                $messageKey = 'services.feedbacks.score_messages.great_job';
            } elseif ($graded_score >= 65) {
                $messageKey = 'services.feedbacks.score_messages.good_effort';
            } elseif ($graded_score >= 55) {
                $messageKey = 'services.feedbacks.score_messages.getting_there';
            } elseif ($graded_score >= 45) {
                $messageKey = 'services.feedbacks.score_messages.decent_try';
            } elseif ($graded_score >= 35) {
                $messageKey = 'services.feedbacks.score_messages.keep_going';
            } elseif ($graded_score >= 25) {
                $messageKey = 'services.feedbacks.score_messages.learning_experience';
            } else {
                $messageKey = 'services.feedbacks.score_messages.persist';
            }

            $feedback->summary = __($messageKey, [
                'score' => $assessment_score,
                'total' => $total_score,
                'percentage' => $graded_score
            ]);
            $feedback->total_score = $total_score;

            if($assessment->type == 'company' || $assessment->type == 'supervisor'){
                $feedback->employee_score = $assessment_score;
            }else{
                $feedback->talent_score = $assessment_score;
            }
            $feedback->graded_score = round($graded_score);
        }

       if(!$feedback->is_published){
            $feedback->time_taken = $request->time_taken;
            $feedback->is_published = true;
            $feedback->save();
        } else {
            return $this->badRequestResponse('services.feedbacks.already_submitted');
        }

        return $this->successResponse(
            $feedback,
            'services.feedbacks.submitted'
        );
    }

     /**
     * Handle file uploads for assessment answers
     */
    protected function handleFileUploads($request, $answer, $user, $employee, $employerId, $assessment)
    {
        $uploadedMedia = collect();

        // Prepare upload options
        $uploadOptions = [
            'user_id' => $user->id,
            'employer_id' => $employerId,
            'employee_id' => $employee?->id,
        ];

        // Handle multiple files upload (new approach)
        if ($request->hasFile('files')) {
            $files = $request->file('files');
            $collection = "assessment_response_{$assessment->id}";
            
            try {
                $media = $answer->addMultipleMedia($files, $collection, $uploadOptions);
                $uploadedMedia = $uploadedMedia->merge($media);
                
                Log::info('Multiple assessment files uploaded', [
                    'answer_id' => $answer->id,
                    'assessment_id' => $assessment->id,
                    'file_count' => count($files),
                    'user_id' => $user->id
                ]);
            } catch (Exception $e) {
                Log::error('Failed to upload multiple assessment files', [
                    'answer_id' => $answer->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        // Handle single file upload (backward compatibility)
        if ($request->hasFile('file') && $request->file('file')->isValid()) {
            $file = $request->file('file');
            $collection = "assessment_response_{$assessment->id}";
            
            try {
                $media = $answer->addMedia($file, $collection, $uploadOptions);
                $uploadedMedia->push($media);
                
                Log::info('Single assessment file uploaded', [
                    'answer_id' => $answer->id,
                    'assessment_id' => $assessment->id,
                    'file_name' => $file->getClientOriginalName(),
                    'user_id' => $user->id
                ]);
            } catch (Exception $e) {
                Log::error('Failed to upload single assessment file', [
                    'answer_id' => $answer->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return $uploadedMedia;
    }

}

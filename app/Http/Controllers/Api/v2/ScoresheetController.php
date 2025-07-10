<?php

namespace App\Http\Controllers\Api\v2;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Notification;

use App\Models\Employee;
use App\Models\Supervisor;
use App\Models\Assessment\CompetencyQuestion;
use App\Models\Assessment\CroxxAssessment;
use App\Models\Assessment\AssignedEmployee;
use App\Models\Assessment\EmployeeLearningPath;
use App\Models\Assessment\EmployerAssessmentFeedback;
use App\Http\Resources\AssignedEmployeeResouce;
use App\Models\Assessment\PeerReview;
use App\Notifications\AssessmentFeedbackNotification;
use App\Models\Assessment\TalentAssessmentSummary;
use App\Models\Training\CroxxTraining;

class ScoresheetController extends Controller
{
    public function employeeList(Request $request, $id) {
        $user = $request->user();

        if (is_numeric($id)) {
            $assessment = CroxxAssessment::where('id', $id)->where('is_published', 1)->firstOrFail();
        } else {
            $assessment = CroxxAssessment::where('code', $id)->where('is_published', 1)->firstOrFail();
        }

        $user_type = $user->type;
        $per_page = $request->input('per_page', 100);
        $sort_by = $request->input('sort_by', 'created_at');
        $sort_dir = $request->input('sort_dir', 'desc');
        $search = $request->input('search');
        $archived = $request->input('archived');
        $supervisor = $request->input('supervisor', 0);

        $assessment->competencies;
        if($user->type == "employer") {
            $supervisor = $supervisor == 'yes' ? 1 : 0;
            $archived = $archived == 'yes' ? true : ($archived == 'no' ? false : null);
        }

        // Different handling based on assessment category
        if ($assessment->category === 'peer_review') {
            // For peer reviews, we want to organize by reviewee with their reviewers
            $query = PeerReview::where('assessment_id', $assessment->id)
                ->when($search, function($query) use ($search) {
                    $query->whereHas('employee', function($q) use ($search) {
                        $q->where('name', 'LIKE', "%{$search}%");
                    })
                    ->orWhereHas('reviewer', function($q) use ($search) {
                        $q->where('name', 'LIKE', "%{$search}%");
                    });
                })
                ->with(['employee', 'reviewer'])
                ->orderBy($sort_by, $sort_dir);

            if ($per_page === 'all' || $per_page <= 0) {
                $results = $query->get();
                $paginatedResults = new \Illuminate\Pagination\LengthAwarePaginator($results, $results->count(), -1);
            } else {
                $paginatedResults = $query->paginate($per_page);
            }

            // Reorganize data to group by reviewee
            $revieweesMap = [];
            foreach ($paginatedResults as $review) {
                $employeeId = $review->employee_id;

                if (!isset($revieweesMap[$employeeId])) {
                    $revieweesMap[$employeeId] = [
                        'employee' => $review->employee,
                        'reviewers' => [],
                        'completion' => [
                            'total' => 0,
                            'completed' => 0
                        ]
                    ];
                }

                // Add reviewer with their review status
                $revieweesMap[$employeeId]['reviewers'][] = [
                    'reviewer' => $review->reviewer,
                    'status' => $review->status,
                    'due_date' => $review->due_date,
                    'completed_at' => $review->completed_at
                ];

                $revieweesMap[$employeeId]['completion']['total']++;
                if ($review->status === 'completed') {
                    $revieweesMap[$employeeId]['completion']['completed']++;
                }
            }

            // Convert to array for the response
            $summaries = array_values($revieweesMap);

            return response()->json([
                'status' => true,
                'message' => "Successful.",
                'data' => [
                    'summaries' => $summaries,
                    'assessment' => $assessment,
                    'type' => 'peer_review'
                ]
            ], 200);
        } else {
            // Original code for standard assessments
            $summaries = AssignedEmployee::where('assessment_id', $assessment->id)
                ->where('is_supervisor', $supervisor)
                ->when($archived, function ($query) use ($archived) {
                    if ($archived !== null) {
                        if ($archived === true) {
                            $query->whereNotNull('archived_at');
                        } else {
                            $query->whereNull('archived_at');
                        }
                    }
                })->when($search, function($query) use ($search) {
                    $query->where('assessment_id', 'LIKE', "%{$search}%");
                })
                ->with('employee')
                ->orderBy($sort_by, $sort_dir);

            if ($per_page === 'all' || $per_page <= 0) {
                $results = $summaries->get();
                $summaries = new \Illuminate\Pagination\LengthAwarePaginator($results, $results->count(), -1);
            } else {
                $summaries = $summaries->paginate($per_page);
            }

            foreach ($summaries as $summary) {
                $feedback = EmployerAssessmentFeedback::where([
                    'assessment_id' => $summary->assessment_id,
                    'employee_id' => $summary->employee_id,
                    'employer_user_id' => $assessment->employer_id
                ])->first();

                $summary->is_submitted = $feedback ? true : false;
                $summary->feedback = $feedback;
            }

            return response()->json([
                'status' => true,
                'message' => "",
                'data' => [
                    'summaries' => $summaries,
                    'assessment' => $assessment,
                    'type' => 'standard'
                ]
            ], 200);
        }
    }

    public function employeeListOld(Request $request, $id){
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

        $assessment->competencies;
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
        // ->select([''])
        ->with('employee')
        ->orderBy($sort_by, $sort_dir);

        if ($per_page === 'all' || $per_page <= 0 ) {
            $results = $summaries->get();
            $summaries = new \Illuminate\Pagination\LengthAwarePaginator($results, $results->count(), -1);
        } else {
            $summaries = $summaries->paginate($per_page);
        }

        foreach ($summaries as $summary) {
            $feedback = EmployerAssessmentFeedback::where([
                'assessment_id' => $summary->assessment_id,
                'employee_id' => $summary->employee_id,
                'employer_user_id' => $assessment->employer_id
            ])->first();

            // if($feedback){
            //     if(is_numeric($feedback->time_taken)){
            //         $timetaken = intval($feedback->time_taken);
            //         $minutes = floor($timetaken / 60);
            //         $seconds = $timetaken % 60;

            //         $feedback->estimated_time = sprintf('%d minutes %d seconds', $minutes, $seconds);
            //     }
            // }

            $summary->is_submited = $feedback ? true : false;
            $summary->feedback = $feedback;
        }

        return response()->json([
            'status' => true,
            'message' => "",
            'data' => compact('summaries','assessment')
        ], 200);
    }

    public function assessmentResult(Request $request, $code, $talent)
    {
        $user = $request->user();
        $assessment = $this->resolveAssessment($code);
        [$talentField, $talentId] = $this->resolveTalentIdentifier($talent, $user);

        $assessment->load(['questions' => function ($query) use ($assessment, $talentField, $talentId) {
            $query->with([
                'response' => fn($q) => $q->where([
                    $talentField => $talentId,
                    'assessment_id' => $assessment->id
                ]),
                'result' => fn($q) => $q->when($assessment->category != 'competency_evaluation',
                    fn($q) => $q->where([
                        $talentField => $talentId,
                        'assessment_id' => $assessment->id
                    ])
                )
            ]);
        }]);

        return response()->json([
            'status' => true,
            'message' => "",
            'data' => compact('assessment')
        ], 200);
    }

    private function resolveAssessment($code)
    {
        return is_numeric($code)
            ? CroxxAssessment::where('id', $code)->where('is_published', 1)->firstOrFail()
            : CroxxAssessment::where('code', $code)->where('is_published', 1)->firstOrFail();
    }

    private function resolveTalentIdentifier($talent, $user)
    {
        if (is_numeric($talent)) {
            return ['talent_id', $user->id];
        }

        $employee = Employee::where('code', $talent)->firstOrFail();
        return ['employee_id', $employee->id];
    }

    public function viewAssessmentFeedback(Request $request, $code, $talent){
        $user = $request->user();
        $employee = Employee::where('code', $talent)->first();

        $assessment = $this->resolveAssessment($code);

        if (is_numeric($talent)) {

            $feedback = TalentAssessmentSummary::where([
                'talent_id' => $user->id,
                'assessment_id' => $assessment->id
            ])->first();

        } else {
            $feedback = EmployerAssessmentFeedback::where([
                'assessment_id' => $assessment->id,
                'employee_id' => $employee->id,
                'employer_user_id' => $assessment->employer_id
            ])->first();

            $resources = CroxxTraining::join('employee_learning_paths', 'croxx_trainings.id', '=', 'employee_learning_paths.training_id')
                            ->where('employee_learning_paths.employee_id', $employee?->id)
                            ->where('employee_learning_paths.assessment_feedback_id', $feedback?->id)
                            // ->with(['learning' => function ($query) use ($employee) {
                            //     $query->where('employee_learning_paths.employee_id', $employee->id);
                            // }])
                            ->select('croxx_trainings.*')
                            ->get();

            if ($feedback) {
                $feedback->resources = $resources;
            }
        }

        return response()->json([
            'status' => true,
            'message' => "",
            'data' => compact('feedback','assessment')
        ], 200);
    }

   public function gradeAssessmentScoreSheet(Request $request, $id)
    {
        $user = $request->user();
        $assessment = $this->resolveAssessment($id);

        $validator = Validator::make($request->all(), [
            'employee_code' => 'required',
            'question_id' => 'required',
            'score' => 'required|integer|between:0,4',
            'comment' => 'nullable|max:256'
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        try {
            $employee = Employee::where('code', $request->employee_code)->firstOrFail();
            $question = CompetencyQuestion::where('assessment_id', $assessment->id)
                            ->where('id', $request->question_id)->firstOrFail();

            if($assessment->category !== 'peer_review') {
                $feedback = EmployerAssessmentFeedback::where([
                    'assessment_id' => $assessment->id,
                    'employee_id' => $employee->id,
                    'employer_user_id' => $assessment->employer_id,
                    'is_published' => true
                ])->firstOrFail();
            } else {
                $feedback = PeerReview::where([
                    'assessment_id' => $assessment->id,
                    'employee_id' => $user->default_company_id,
                    'reviewer_id' => $employee->id,
                ])->firstOrFail();
            }

            $score = ScoreSheet::firstOrCreate(
                [
                    'assessment_id' => $assessment->id,
                    'employee_id' => $employee->id,
                ],[
                    'score' => $request->score,
                    'assessment_question_id' => $question->id,
                    'comment' => $request->comment ?? '',
                    'supervisor_id' => $user->default_company_id
                ]
            );

            if($assessment->category !== 'peer_review') {
                $employee_score = ScoreSheet::where([
                'employee_id' => $employee->id,
                'assessment_id' => $assessment->id
                ])->sum('score');

                $total_question = $assessment->questions->count();
                $total_score = $total_question * 4;
                $graded_score = ((int)$employee_score / $total_score) * 100;

                $feedback->total_score = $total_score;
                $feedback->employee_score = (int)$employee_score;
                $feedback->graded_score = round($graded_score);
                $feedback->save();
            }

            return $this->successResponse(
                $feedback,
                'services.assessment.graded'
            );

        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('services.assessment.not_found');
        } catch (\Exception $e) {
            return $this->errorResponse(
                'services.assessment.grade_error',
                ['error' => $e->getMessage()]
            );
        }
    }

    public function publishPeerReviewAssessment(Request $request, $id)
    {
        $user = $request->user();
        $assessment = $this->resolveAssessment($id);

        $validator = Validator::make($request->all(), [
            'employee_code' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        try {
            $employee = Employee::where('code', $request->employee_code)->firstOrFail();
            $peerReview = PeerReview::where([
                'assessment_id' => $assessment->id,
                'employee_id' => $user->default_company_id,
                'reviewer_id' => $employee->id,
            ])->firstOrFail();

            if($peerReview->status == 'pending') {
                $peerReview->status = 'completed';
                $peerReview->completed_at = now();
                $peerReview->save();

                // Notification::send($employee->talent, new AssessmentFeedbackNotification($assessment, $employee));
                return $this->successResponse(
                    $peerReview,
                    'services.peer_review.published'
                );
            }

            return $this->badRequestResponse('services.peer_review.already_published');

        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('services.assessment.not_found');
        } catch (\Exception $e) {
            return $this->errorResponse(
                'services.peer_review.publish_error',
                ['error' => $e->getMessage()]
            );
        }
    }

    public function publishSupervisorFeedback(Request $request, $id)
    {
        $user = $request->user();
        $assessment = $this->resolveAssessment($id);

        $validator = Validator::make($request->all(), [
            'employee_code' => 'required',
            'feedback' => 'required|string|min:10|max:512',
            'goal_id' => 'nullable|exists:goals,id',
            'learning_path' => 'nullable|array',
            'learning_path.*' => 'nullable|integer'
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        try {
            $employee = Employee::where('code', $request->employee_code)->firstOrFail();
            $feedback = EmployerAssessmentFeedback::where([
                'assessment_id' => $assessment->id,
                'employee_id' => $employee->id,
                'employer_user_id' => $assessment->employer_id
            ])->firstOrFail();

            if(!$feedback->supervisor_id) {
                if($request->learning_path) {
                    foreach ($request->learning_path as $path) {
                        EmployeeLearningPath::firstOrCreate([
                            'assessment_feedback_id' => $feedback->id,
                            'employee_id' => $employee->id,
                            'employer_user_id' => $assessment->employer_id,
                            'training_id' => $path
                        ]);
                    }
                }

                $scoreMessageKey = $this->getScoreMessageKey($feedback->graded_score);
                $message = __("services.supervisor_feedback.score_messages.$scoreMessageKey", [
                    'score' => $feedback->employee_score,
                    'total' => $feedback->total_score,
                    'percentage' => $feedback->graded_score
                ]);

                $feedback->summary = $message;
                $feedback->supervisor_id = $user->default_company_id;
                $feedback->supervisor_feedback = $request->feedback;
                $feedback->goal_id = $request->goal_id;
                $feedback->save();

                Notification::send($employee->talent, new AssessmentFeedbackNotification($assessment, $employee));

                return $this->successResponse(
                    $feedback,
                    'services.supervisor_feedback.published'
                );
            }

            return $this->badRequestResponse('services.supervisor_feedback.already_published');

        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('services.assessment.not_found');
        } catch (\Exception $e) {
            return $this->errorResponse(
                'services.supervisor_feedback.publish_error',
                ['error' => $e->getMessage()]
            );
        }
    }

    protected function getScoreMessageKey($percentage)
    {
        if ($percentage >= 90) return 'exceptional';
        if ($percentage >= 75) return 'excellent';
        if ($percentage >= 60) return 'good';
        if ($percentage >= 45) return 'average';
        if ($percentage >= 30) return 'below_average';
        return 'poor';
    }
}

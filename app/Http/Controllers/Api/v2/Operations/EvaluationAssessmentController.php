<?php

namespace App\Http\Controllers\Api\v2\Operations;

use App\Helpers\AssessmentNotificationHelper;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Http\Requests\EvaluationAssessmentRequest;
use App\Models\Assessment\EvaluationQuestion;
use App\Models\Assessment\CroxxAssessment;
use App\Models\Assessment\AssignedEmployee;
use App\Models\Employee;
use App\Models\Supervisor;
use App\Traits\ApiResponseTrait;

class EvaluationAssessmentController extends Controller
{

    // use ApiResponseTrait;
     protected $mediaService;

    public function __construct(MediaService $mediaService)
    {
        $this->mediaService = $mediaService;
    }
    
    public function store(EvaluationAssessmentRequest $request)
    {
        DB::beginTransaction();

        try {
            $user = $request->user();
            $validatedData = $request->validated();
            $validatedData['code'] = $user->id . md5(time());

            $validatedData['user_id'] = $user->id;
            $validatedData['employer_id'] = $user->id;

            $employee = null;
            $employerId = $user->id; // Default employer ID

            if ($validatedData['type'] == 'company') {
                $competency_ids = $validatedData['competency_ids'];
                unset($validatedData['competency_ids']);
            }

            if ($validatedData['type'] == 'supervisor') {
                $employee = Supervisor::where('supervisor_id', $validatedData['supervisor_id'])->firstOrFail();
                $validatedData['employer_id'] = $employee->employer_id;
                $validatedData['user_id'] = $validatedData['supervisor_id'];
                $employerId = $employee->employer_id;
                $competency_ids = $validatedData['competency_ids'];
                unset($validatedData['competency_ids']);
            }

            // Create assessment
            $assessment = CroxxAssessment::create($validatedData);

            if ($validatedData['type'] == 'supervisor' || $validatedData['type'] == 'company') {
                $assessment->competencies()->attach($competency_ids);
            }

            // Create questions with image handling
            $questions = $validatedData['questions'];
            foreach ($questions as $index => $questionData) {
                $questionData['assessment_id'] = $assessment->id;
                $questionModel = EvaluationQuestion::create($questionData);

                $this->handleQuestionImages($request, $questionModel, $index, $user, $employerId);
            }

            // Create assigned employees
            $employeeInstances = [];
            $supervisorInstances = [];

            // Assign Employee and Supervisor for company and supervisor
            if ($validatedData['type'] == 'supervisor' || $validatedData['type'] == 'company') {
                // Create assigned employees
                $employees = $validatedData['employees'];
                foreach ($employees as $employeeId) {
                    $assignedEmployee = AssignedEmployee::create([
                        'assessment_id' => $assessment->id,
                        'employee_id' => $employeeId,
                        'is_supervisor' => false
                    ]);
                    $employeeInstances[] = $assignedEmployee;
                }

                if ($validatedData['type'] == 'company') {
                    // Create assigned supervisors
                    $supervisors = $validatedData['supervisors'];
                    foreach ($supervisors as $supervisor) {
                        $assignedEmployee = AssignedEmployee::create([
                            'assessment_id' => $assessment->id,
                            'employee_id' => $supervisor,
                            'is_supervisor' => true
                        ]);
                        $supervisorInstances[] = $assignedEmployee;
                    }
                }
            }

            // Attach Supervisor to
            if ($validatedData['type'] == 'supervisor') {
                $assignedEmployee = AssignedEmployee::create([
                    'assessment_id' => $assessment->id,
                    'employee_id' => $validatedData['supervisor_id'],
                    'is_supervisor' => true
                ]);

                $employeeInstances[] = $assignedEmployee;
            }

            // Send Notification
            AssessmentNotificationHelper::notifyAssignedUsers($employeeInstances, $supervisorInstances, $assessment);

            // Commit the transaction
            DB::commit();

            // Load the assessment with questions and their media
            $assessmentWithMedia = CroxxAssessment::with(['evaluationQuestions.media'])->find($assessment->id);

            return $this->successResponse(
                $assessmentWithMedia,
                'services.assessment.created',
                [],
                201
            );

        } catch (\Exception $e) {
            // Rollback the transaction on error
            DB::rollBack();

            return $this->errorResponse(
                'services.assessment.store_error',
                [],
                400,
                $e->getMessage()
            );
        }
    }

    protected function handleQuestionImages($request, $questionModel, $questionIndex, $user, $employerId)
    {

        // Handle single image for backward compatibility
        if ($request->hasFile("questions.{$questionIndex}.image")) {
            $image = $request->file("questions.{$questionIndex}.image");
            $this->uploadQuestionImages($questionModel, [$image], $user, $employerId);
        }

    }

     /**
     * Upload images for a question
     */
    protected function uploadQuestionImages($questionModel, $images, $user, $employerId)
    {
        if (!is_array($images)) {
            $images = [$images];
        }

        $uploadOptions = [
            'user_id' => $user->id,
            'employer_id' => $employerId,
            'employee_id' => null,
        ];

        $collection = 'question_images';

        try {
            $uploadedMedia = $questionModel->addMultipleMedia($images, $collection, $uploadOptions);
            
            Log::info('Question images uploaded', [
                'question_id' => $questionModel->id,
                'assessment_id' => $questionModel->assessment_id,
                'image_count' => count($images),
                'user_id' => $user->id
            ]);

            return $uploadedMedia;
        } catch (Exception $e) {
            Log::error('Failed to upload question images', [
                'question_id' => $questionModel->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
}

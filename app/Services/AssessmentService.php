<?php

namespace App\Services;

use App\Helpers\AssessmentNotificationHelper;
use App\Http\Requests\ExperienceAssessmentRequest;
use App\Models\Assessment\AssignedEmployee;
use App\Models\Assessment\CompetencyQuestion;
use App\Models\Assessment\CroxxAssessment;
use App\Models\Assessment\PeerReview;

use App\Models\Supervisor;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AssessmentService
{
    protected $mediaService;

    public function __construct(MediaService $mediaService)
    {
        $this->mediaService = $mediaService;
    }

    public function store(ExperienceAssessmentRequest $request)
    {
        DB::beginTransaction();

        try {
            $user = $request->user();
            $validatedData = $request->validated();
            $validatedData['code'] = $user->id . md5(time());

            // Separate competency IDs from the rest of the data
            $competency_ids = $validatedData['competency_ids'];
            unset($validatedData['competency_ids']);

            // Set employer and user IDs based on assessment type
            $this->setAssessmentOwnership($validatedData, $user);

            // Create the assessment
            $assessment = CroxxAssessment::create($validatedData);
            $assessment->competencies()->attach($competency_ids);

            // Create questions with document handling
            $this->createAssessmentQuestions($assessment, $validatedData['questions'], $request, $user);
            
            // Arrays to track assigned users for notifications
            $assignedReviewees = [];
            $assignedReviewers = [];

            // Handle employee assignments based on assessment category
            if ($validatedData['category'] === 'peer_review') {
                $this->handlePeerReviewAssignments($assessment, $validatedData['employees_reviewers']);
            } else {
                $this->handleStandardAssignments($assessment, $validatedData);
            }

            AssessmentNotificationHelper::notifyAssignedUsers($assignedReviewees, $assignedReviewers, $assessment);

            DB::commit();

            return CroxxAssessment::with(['competencies', 'questions.media'])->find($assessment->id);

        } catch (\Exception $e) {
           throw $e;
        }
    }

    private function setAssessmentOwnership(array &$data, $user)
    {
        switch ($data['type']) {
            case 'company':
                $data['user_id'] = $user->id;
                $data['employer_id'] = $user->id;
                break;
            case 'supervisor':
                $supervisor = Supervisor::where('supervisor_id', $data['supervisor_id'])->firstOrFail();
                $data['employer_id'] = $supervisor->employer_id;
                $data['user_id'] = $data['supervisor_id'];
                break;
            default:
                $data['user_id'] = $user->id;
                break;
        }
    }

    private function createAssessmentQuestions($assessment, array $questions, $request, $user)
    {
        foreach ($questions as $index => $questionData) {
            $question = CompetencyQuestion::create([
                'assessment_id' => $assessment->id,
                'question' => $questionData['question'],
                'description' => $questionData['description'] ?? null,
            ]);

            $this->handleQuestionDocument($request, $question, $index, $user, $assessment->employer_id);
        }
    }

    /**
     * Handle single document upload for a specific question
     */
    private function handleQuestionDocument($request, $questionModel, $questionIndex, $user, $employerId)
    {
        // Handle single document upload
        if ($request->hasFile("questions.{$questionIndex}.document")) {
            $document = $request->file("questions.{$questionIndex}.document");
            
            $uploadOptions = [
                'user_id' => $user->id,
                'employer_id' => $employerId,
                'employee_id' => null,
            ];

            $collection = 'question_documents';

            try {
                $uploadedMedia = $questionModel->addMedia($document, $collection, $uploadOptions);
                
                Log::info('Question document uploaded', [
                    'question_id' => $questionModel->id,
                    'assessment_id' => $questionModel->assessment_id,
                    'document_name' => $document->getClientOriginalName(),
                    'user_id' => $user->id
                ]);

                return $uploadedMedia;
            } catch (Exception $e) {
                Log::error('Failed to upload question document', [
                    'question_id' => $questionModel->id,
                    'error' => $e->getMessage()
                ]);
                throw $e;
            }
        }
    }

    private function handlePeerReviewAssignments($assessment, array $employeesReviewers)
    {
        $dueDate = Carbon::now()->addDays(14); // Default 14 days for peer reviews

        foreach ($employeesReviewers as $entry) {
            $employeeId = $entry['employee_id'];
            $reviewers = $entry['reviewers'];

            // Create assigned record for reviewee
            $assignedReviewees[] = AssignedEmployee::create([
                'assessment_id' => $assessment->id,
                'employee_id' => $employeeId,
                'is_reviewer' => false,
            ]);

            // Create peer review records
            foreach ($reviewers as $reviewerId) {
                PeerReview::create([
                    'assessment_id' => $assessment->id,
                    'employee_id' => $employeeId,
                    'reviewer_id' => $reviewerId,
                    'status' => 'pending',
                    'due_date' => $dueDate,
                ]);

                // Also mark reviewer as assigned
                $assignedReviewers[] = AssignedEmployee::create([
                    'assessment_id' => $assessment->id,
                    'employee_id' => $reviewerId,
                    'is_reviewer' => true,
                ]);
            }
        }
    }

    private function handleStandardAssignments($assessment, array $data)
    {
        if (!in_array($data['type'], ['company', 'supervisor'])) {
            return;
        }

        // Assign employees
        if (isset($data['employees'])) {
            foreach ($data['employees'] as $empId) {
                $assignedReviewees[] = AssignedEmployee::create([
                    'assessment_id' => $assessment->id,
                    'employee_id' => $empId,
                    'is_supervisor' => false,
                ]);
            }
        }

        // Assign supervisors for company assessments
        if ($data['type'] === 'company' && isset($data['supervisors'])) {
            foreach ($data['supervisors'] as $supId) {
                $assignedReviewers[] = AssignedEmployee::create([
                    'assessment_id' => $assessment->id,
                    'employee_id' => $supId,
                    'is_supervisor' => true,
                ]);
            }
        }

        // Assign supervisor for supervisor-type assessments
        if ($data['type'] === 'supervisor') {
            $assignedReviewers[] =  AssignedEmployee::create([
                'assessment_id' => $assessment->id,
                'employee_id' => $data['supervisor_id'],
                'is_supervisor' => true,
            ]);
        }
    }
}
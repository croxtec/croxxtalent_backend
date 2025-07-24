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

            // Create questions
            $this->createAssessmentQuestions($assessment, $validatedData['questions']);
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

            return CroxxAssessment::with(['competencies', 'questions'])->find($assessment->id);

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

    private function createAssessmentQuestions($assessment, array $questions)
    {
        foreach ($questions as $question) {
            CompetencyQuestion::create([
                'assessment_id' => $assessment->id,
                'question' => $question['question'],
                'description' => $question['description'] ?? null,
            ]);
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

    // private function setAssessmentOwnership(array &$data, $user)
    // {
    //     $ownershipMap = [
    //         'company' => [
    //             'user_id' => $user->id,
    //             'employer_id' => $user->id
    //         ],
    //         'supervisor' => function($data) {
    //             $supervisor = Supervisor::where('supervisor_id', $data['supervisor_id'])->firstOrFail();
    //             return [
    //                 'employer_id' => $supervisor->employer_id,
    //                 'user_id' => $data['supervisor_id']
    //             ];
    //         }
    //     ];

    //     $ownership = $ownershipMap[$data['type']] ?? ['user_id' => $user->id];

    //     if (is_callable($ownership)) {
    //         $ownership = $ownership($data);
    //     }

    //     foreach ($ownership as $key => $value) {
    //         $data[$key] = $value;
    //     }
    // }
}

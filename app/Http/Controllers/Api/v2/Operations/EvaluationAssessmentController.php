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

class EvaluationAssessmentController extends Controller
{
    public function store(EvaluationAssessmentRequest $request)
    {
        DB::beginTransaction();

        try {
            $user = $request->user();
            $validatedData = $request->validated();
            $validatedData['code'] = $user->id . md5(time());

            $validatedData['user_id'] = $user->id;
            $validatedData['employer_id'] = $user->id;

            if ($validatedData['type'] == 'company') {
                $competency_ids = $validatedData['competency_ids'];
                unset($validatedData['competency_ids']);
            }

            if ($validatedData['type'] == 'supervisor') {
                $employee = Supervisor::where('supervisor_id', $validatedData['supervisor_id'])->firstOrFail();
                $validatedData['employer_id'] = $employee->employer_id;
                $validatedData['user_id'] = $validatedData['supervisor_id'];
                $competency_ids = $validatedData['competency_ids'];
                unset($validatedData['competency_ids']);
            }

            // Create assessment
            $assessment = CroxxAssessment::create($validatedData);

            if ($validatedData['type'] == 'supervisor' || $validatedData['type'] == 'company') {
                $assessment->competencies()->attach($competency_ids);
            }

            // Create questions
            $questions = $validatedData['questions'];
            foreach ($questions as $question) {
                $question['assessment_id'] = $assessment->id;
                EvaluationQuestion::create($question);
            }

              // Create assigned employees
              $employeeInstances = [];
              $supervisorInstances = [];

            // Assign Employee and Supervisor for company and supervisor
            if ($validatedData['type'] == 'supervisor' || $validatedData['type'] == 'company') {
                // Create assigned employees
                $employees = $validatedData['employees'];
                foreach ($employees as $employee) {
                    $assignedEmployee = AssignedEmployee::create([
                        'assessment_id' => $assessment->id,
                        'employee_id' => $employee,
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

            return response()->json([
                'status' => true,
                'message' => "Assessment created successfully.",
                'data' => CroxxAssessment::find($assessment->id),
            ], 201);

        } catch (\Exception $e) {
            // Rollback the transaction on error
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => "Could not complete request. " . $e->getMessage(),
            ], 400);
        }
    }
}

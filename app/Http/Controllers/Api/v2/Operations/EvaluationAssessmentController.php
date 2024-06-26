<?php

namespace App\Http\Controllers\Api\v2\Operations;

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
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(EvaluationAssessmentRequest $request)
    {
        DB::beginTransaction();

        try {
            $user = $request->user();
            $validatedData = $request->validated();
            $validatedData['code'] = $user->id . md5(time());

            if ($validatedData['type'] == 'company') {
                $validatedData['user_id'] = $user->id;
                $validatedData['employer_id'] = $user->id;
            }

            if ($validatedData['type'] == 'supervisor') {
                $employee = Supervisor::where('supervisor_id', $validatedData['supervisor_id'])->firstOrFail();
                $validatedData['employer_id'] = $employee->employer_id;
                $validatedData['user_id'] = $validatedData['supervisor_id'];
            }

            // Create assessment
            $assessment = CroxxAssessment::create($validatedData);

            // Create questions
            $questions = $validatedData['questions'];
            foreach ($questions as $question) {
                $question['assessment_id'] = $assessment->id;
                EvaluationQuestion::create($question);
            }

            // Create assigned employees
            $employees = $validatedData['employees'];
            foreach ($employees as $employee) {
                AssignedEmployee::create([
                    'assessment_id' => $assessment->id,
                    'employee_id' => $employee,
                ]);
            }

            if ($validatedData['type'] == 'company') {
                // Create assigned supervisors
                $supervisors = $validatedData['supervisors'];
                foreach ($supervisors as $supervisor) {
                    AssignedEmployee::create([
                        'assessment_id' => $assessment->id,
                        'employee_id' => $supervisor,
                        'is_supervisor' => true
                    ]);
                }
            }

            if ($validatedData['type'] == 'supervisor') {
                AssignedEmployee::create([
                    'assessment_id' => $assessment->id,
                    'employee_id' => $validatedData['supervisor_id'],
                    'is_supervisor' => true
                ]);
            }


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

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}

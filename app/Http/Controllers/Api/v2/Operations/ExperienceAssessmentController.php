<?php

namespace App\Http\Controllers\Api\v2\Operations;

use App\Helpers\AssessmentNotificationHelper;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use App\Notifications\AssessmentPublishedNotification;

use App\Models\Employee;
use App\Models\Supervisor;
use App\Models\Assessment\CompetencyQuestion;
use App\Models\Assessment\EvaluationQuestion;
use App\Http\Requests\ExperienceAssessmentRequest;
use App\Models\Assessment\CroxxAssessment;
use App\Models\Assessment\AssignedEmployee;


class ExperienceAssessmentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $user_type = $user->type;
        $per_page = $request->input('per_page', 10);
        $sort_by = $request->input('sort_by', 'created_at');
        $sort_dir = $request->input('sort_dir', 'desc');
        $search = $request->input('search');
        $archived = $request->input('archived');
        $department = $request->input('department');
        $datatable_draw = $request->input('draw'); // if any

        $archived = $archived == 'yes' ? true : ($archived == 'no' ? false : null);

        $assessment = CroxxAssessment::withCount('questions')
            ->with('competencies')
            ->when($user_type == 'employer', function($query) use ($user){
                $query->where('employer_id', $user->id);
            })
            ->when($department,function ($query) use ($department) {
                if ($department !== null  && is_numeric($department)) {
                   $query->where('department_id', $department);
                }
            })
            ->when($archived ,function ($query) use ($archived) {
            if ($archived !== null ) {
                if ($archived === true ) {
                    $query->whereNotNull('archived_at');
                } else {
                    $query->whereNull('archived_at');
                }
            }
        })
        ->where( function($query) use ($search) {
            $query->where('code', 'LIKE', "%{$search}%");
        })->with('department', 'department_role')
        ->orderBy($sort_by, $sort_dir);

        if ($per_page === 'all' || $per_page <= 0 ) {
            $results = $assessment->get();
            $assessment = new \Illuminate\Pagination\LengthAwarePaginator($results, $results->count(), -1);
        } else {
            $assessment = $assessment->paginate($per_page);
        }

        foreach ($assessment as $record) {
            $record->total_questions = $record->questions->count();
            $total_duration_seconds = $record->questions->sum('duration');
            // Convert the total duration in minutes to hours, minutes, and seconds
            $minutes = floor($total_duration_seconds / 60);
            $seconds = $total_duration_seconds % 60;

            $estimated_time = sprintf('%d minutes', $minutes);

            $record->estimated_time = $estimated_time;

            unset($record->questions);
        }

        $response = collect([
            'status' => true,
            'data' => $assessment,
            'message' => ""
        ]);

        return response()->json($response, 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(ExperienceAssessmentRequest $request)
    {

         // Start a transaction
         DB::beginTransaction();

         try {
             $user = $request->user();
             $validatedData = $request->validated();
             $validatedData['code'] = $user->id . md5(time());
             $competency_ids = $validatedData['competency_ids'];
             unset($validatedData['competency_ids']);

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
            $assessment->competencies()->attach($competency_ids);

            // Create questions
            $questions = $validatedData['questions'];
            foreach ($questions as $question) {
                $question['assessment_id'] = $assessment->id;
                CompetencyQuestion::create($question);
            }

            // Create assigned employees
            $employeeInstances = [];
            $supervisorInstances = [];

            if ($validatedData['type'] == 'supervisor' || $validatedData['type'] == 'company') {
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

    // private function notifyAssignedUsers($employeeInstances, $supervisorInstances, $assessment)
    // {
    //     // Notify employees
    //     if (!empty($employeeInstances)) {
    //         $employees = collect();

    //         foreach ($employeeInstances as $assignedEmployee) {
    //             $employee = Employee::find($assignedEmployee->employee_id);
    //             if ($employee) {
    //                 $employees->push($employee);
    //             }
    //         }

    //         if ($employees->isNotEmpty()) {
    //             // Send batch notifications to employees
    //             foreach ($employees as $employee) {
    //                 if($employee->talent)  Notification::send($employee->talent, new AssessmentPublishedNotification($assessment, $employee, 'employee'));
    //             }
    //         }
    //     }

    //     // Notify supervisors
    //     if (!empty($supervisorInstances)) {
    //         $supervisors = collect();

    //         foreach ($supervisorInstances as $assignedEmployee) {
    //             $supervisor = Employee::find($assignedEmployee->employee_id);
    //             if ($supervisor) {
    //                 $supervisors->push($supervisor);
    //             }
    //         }

    //         if ($supervisors->isNotEmpty()) {
    //             // Send batch notifications to supervisors
    //             foreach ($supervisors as $supervisor) {
    //                if($supervisor->talent) Notification::send($supervisor->talent, new AssessmentPublishedNotification($assessment, $supervisor, 'supervisor'));
    //             }
    //         }
    //     }
    // }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        $user = $request->user();
        $user_type = $user->type;
        $employerId = ($user_type == 'employer') ? $user->id: null;

        // Validate Employee Access
        if($user_type == 'talent'){
            $employee = Employee::where('user_id', $user->id)->where('id', $user->default_company_id)->firstOrFail();
            $employerId = $employee->employer_id;
        }

        if (is_numeric($id)) {
            $assessment = CroxxAssessment::where('id', $id)->where('employer_id', $employerId)->firstOrFail();
        } else {
            $assessment = CroxxAssessment::where('code', $id)->where('employer_id', $employerId)->firstOrFail();
        }

         // Confirm if employee is assigned
        if($user_type == 'talent'){
            $isAssigned = AssignedEmployee::where('employee_id', $employee->id)
                ->where('assessment_id', $assessment->id)->first();

            if(!$isAssigned){
                return response()->json([
                    'status' => false,
                    'message' => 'Unautourized Access'
                ], 401);
            }
        }

        if ($assessment->category == 'competency_evaluation') {
           $questions = EvaluationQuestion::where('assessment_id', $assessment->id)
                    ->whereNull('archived_at')->get();
        } else {
            $questions = CompetencyQuestion::where('assessment_id', $assessment->id)
                    ->whereNull('archived_at')->get();
        }

        $assessment->competencies;
        $assessment->questions = $questions;

       return response()->json([
            'status' => true,
            'message' => "",
            'data' => $assessment
        ], 200);
    }


    public function talent(Request $request, $id)
    {
        $user = $request->user();
        $user_type = $user->type;
        $add = $request->input('add');
        // Confirm if employee is assigned
        if($user_type !== 'talent'){
            return response()->json([
                'status' => false,
                'message' => 'Unautourized Access'
            ], 401);
        }

        if (is_numeric($id)) {
            $assessment = CroxxAssessment::where('id', $id)->whereIn('category', ['competency_evaluation'])->firstOrFail();
        } else {
            $assessment = CroxxAssessment::where('code', $id)->whereIn('category', ['competency_evaluation'])->firstOrFail();
        }

        if ($assessment->category == 'competency_evaluation') {
           $questions = EvaluationQuestion::where('assessment_id', $assessment->id)
                    ->whereNull('archived_at')->get();
        } else {
            $questions = CompetencyQuestion::where('assessment_id', $assessment->id)
                    ->whereNull('archived_at')->get();
        }

        $assessment->questions = $questions;

        return response()->json([
            'status' => true,
            'message' => "",
            'data' => $assessment
        ], 200);
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
        $user = $request->user();
        // $this->authorize('update', [CroxxAssessment::class, $assessment]);

        if (is_numeric($id)) {
            $assessment = CroxxAssessment::where('id', $id)->where('employer_id', $user->id)->firstOrFail();
        } else {
            $assessment = CroxxAssessment::where('code', $id)->where('employer_id', $user->id)->firstOrFail();
        }

        // $request->validate([
        //     'questions' => 'required|array'
        // ]);

        // $questions = $request->questions;
        // foreach ($questions as $question) {
        //     $question['assessment_id'] = $assessment->id;
        //     CompetencyQuestion::create($question);
        // }

        return response()->json([
            'status' => true,
            'message' => "",
            'data' => $assessment
        ], 200);

    }

    public function publish(Request $request, $id)
    {
        $user = $request->user();
        // $this->authorize('update', [CroxxAssessment::class, $assessment]);

        if (is_numeric($id)) {
            $assessment = CroxxAssessment::where('id', $id)->where('employer_id', $user->id)->firstOrFail();
        } else {
            $assessment = CroxxAssessment::where('code', $id)->where('employer_id', $user->id)->firstOrFail();
        }

        if($assessment->is_published != true){
            $assessment->is_published = true;
            $assessment->save();
        }

        return response()->json([
            'status' => true,
            'message' => "Assessment \"{$assessment->name}\" publish successfully.",
            'data' => CroxxAssessment::find($assessment->id)
        ], 200);
    }

    public function archive($id)
    {
        $assessment = CroxxAssessment::findOrFail($id);

        // $this->authorize('delete', [CroxxAssessment::class, $assessment]);

        $assessment->archived_at = now();
        $assessment->save();

        return response()->json([
            'status' => true,
            'message' => "Assessment \"{$assessment->name}\" archived successfully.",
            'data' => CroxxAssessment::find($assessment->id)
        ], 200);
    }

    /**
     * Unarchive the specified resource from archived storage.
     *
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function unarchive($id)
    {
        $assessment = CroxxAssessment::findOrFail($id);

        // $this->authorize('delete', [CroxxAssessment::class, $assessment]);

        $assessment->archived_at = null;
        $assessment->save();

        return response()->json([
            'status' => true,
            'message' => "Assessment \"{$assessment->name}\" unarchived successfully.",
            'data' => CroxxAssessment::find($assessment->id)
        ], 200);
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

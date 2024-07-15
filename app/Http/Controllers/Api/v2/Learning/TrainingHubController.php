<?php

namespace App\Http\Controllers\Api\v2\Learning;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\Training\CroxxTraining;
use App\Models\Assessment\EmployeeLearningPath;
use App\Models\Training\CroxxLesson as Lesson;


class TrainingHubController extends Controller
{

      /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function employee(Request $request, $code)
    {
        $user = $request->user();
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

        $trainings = CroxxTraining::
                        join('employee_learning_paths', 'croxx_trainings.id', '=', 'employee_learning_paths.training_id')
                        ->where('croxx_trainings.employer_id', $employee->employer_id)
                        ->where('employee_learning_paths.employee_id', $employee->id)
                        ->latest()
                        ->select('croxx_trainings.*')
                        ->paginate($per_page);


        return response()->json([
            'status' => true,
            'message' => "",
            'data' => $trainings
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
            $supervisor =  $current_company->supervisor;
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


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $per_page = $request->input('per_page', 12);
        $employeeIds = Employee::where('user_id', $user->id)->pluck('id')->toArray();

        $trainings = CroxxTraining::paginate($per_page);

        return response()->json([
            'status' => true,
            'message' => "",
            'data' => $trainings
        ], 200);
    }

    public function recommended(Request $request)
    {
        $user = $request->user();
        $per_page = $request->input('per_page', 12);
        $employeeIds = Employee::where('user_id', $user->id)->pluck('id')->toArray();

        $trainings = CroxxTraining::paginate($per_page);

        return response()->json([
            'status' => true,
            'message' => "",
            'data' => $trainings
        ], 200);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function paths(Request $request)
    {
        $user = $request->user();
        $per_page = $request->input('per_page', 12);
        $employeeIds = Employee::where('user_id', $user->id)->pluck('employer_id')->toArray();

        $trainings = CroxxTraining::join('employee_learning_paths', 'croxx_trainings.id', '=', 'employee_learning_paths.training_id')
                        ->whereIn('employee_learning_paths.employer_user_id', $employeeIds)
                        ->latest()
                        ->select('croxx_trainings.*')
                        ->paginate($per_page);

        return response()->json([
            'status' => true,
            'message' => "",
            'data' => $trainings
        ], 200);
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $code)
    {
        $user = $request->user();
        $course = CroxxTraining::where('code', $code)
                        ->with('author')->firstOrFail();
        $course_type = $course->type;
        $company = $request->input('employee', $user?->default_company_id ?? null);


        if($course_type == 'company'){
            $employee = Employee::where('user_id', $user->id)->where('id',$company)->firstOrFail();
            // info($employee);
            $learning = EmployeeLearningPath::where([
                // 'employee_id' => $employee->id,
                'employer_user_id' => $employee->employer_id,
                'training_id' => $course->id
            ])->firstOrFail();

            $course->learning = $learning;
        }

        $course->review_lessons;
        $percentage = ($learning->current_lesson / $course->total_lessons) * 100;
        $course->percentage = round($percentage);

        return response()->json([
            'status' => true,
            'message' => "",
            'data' => $course
        ], 200);
    }

    public function lesson(Request $request, $code, $alias)
    {
        $user = $request->user();
        $course = CroxxTraining::where('code', $code)->firstOrFail();
        $lesson = Lesson::where('training_id', $course->id)->where('alias',$alias)->firstOrFail();
        $course_type = $course->type;
        $company = $request->input('employee', $user?->default_company_id ?? null);

        if($course_type == 'company'){
            $employee = Employee::where('user_id', $user->id)->where('id',$company)->firstOrFail();
            // info($employee);
            $learning = EmployeeLearningPath::where([
                // 'employee_id' => $employee->id,
                'employer_user_id' => $employee->employer_id,
                'training_id' => $course->id
            ])->firstOrFail();

            // $learning->current_lesson = 1;
            // $learning->save();
            $course->learning = $learning;
        }

        $course->review_lessons;
        $percentage = ($learning->current_lesson / $course->total_lessons) * 100;
        $course->percentage = round($percentage);

        return response()->json([
            'status' => true,
            'message' => "",
            'data' => compact('lesson','course')
        ], 200);
    }




}

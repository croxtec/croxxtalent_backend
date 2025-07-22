<?php

namespace App\Http\Controllers\Api\v2;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\GoalRequest;
use App\Models\Goal;
use App\Models\Employee;
use App\Notifications\SupervisorGoalNotification;
use Illuminate\Support\Carbon;

class GoalController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = $request->user();
        // $this->authorize('view-any', Goal::class);
        $user_type = $request->input('user_type', 'career');
        $per_page = $request->input('per_page', 5);
        $sort_by = $request->input('sort_by', 'created_at');
        $sort_dir = $request->input('sort_dir', 'desc');
        $search = $request->input('search');
        $archived = $request->input('archived');
        $datatable_draw = $request->input('draw');
        $month = $request->input('month', 'current');
        $period = null;

        if($month == 'current'){
           $period = [now()->startOfMonth(), now()->endOfMonth()];
        }else if($month === 'all'){
            $period = null;
        }

        $archived = $archived == 'yes' ? true : ($archived == 'no' ? false : null);

        $goals = Goal::where( function ($query) use ($archived) {
            if ($archived !== null ) {
                if ($archived === true ) {
                    $query->whereNotNull('archived_at');
                } else {
                    $query->whereNull('archived_at');
                }
            }
        })->when($user_type == 'career', function($query) use ($user){
            $query->where('user_id', $user->id);
        })
        ->when($period, function($query) use($period){
            $query->whereBetween('created_at', $period);
        })
        ->where( function($query) use ($search) {
            $query->where('title', 'LIKE', "%{$search}%");
        })->orderBy($sort_by, $sort_dir);

        if ($per_page === 'all' || $per_page <= 0 ) {
            $results = $goals->get();
            $goals = new \Illuminate\Pagination\LengthAwarePaginator($results, $results->count(), -1);
        } else {
            $goals = $goals->paginate($per_page);
        }

        $response = collect([
            'status' => true,
            'message' => ""
        ])->merge($goals)->merge(['draw' => $datatable_draw]);
        return response()->json($response, 200);
    }


    public function overview(Request $request){
        $user = $request->user();
        $user_type = $request->input('user_type', 'career');
        $period = [now()->startOfMonth(), now()->endOfMonth()];

        $totalDone = 0;
        $totalMissed = 0;
        $totalPending = 0;

        $goals = Goal::when($user_type == 'career', function($query) use ($user){
            $query->where('user_id', $user->id);
        })->whereNull('archived_at')
        ->when($period, function($query) use($period){
            $query->whereBetween('created_at', $period);
        })
        ->select(['id','user_id', 'type','status'])->get();

        foreach ($goals as $goal) {
            switch ($goal->status) {
                case 'done':
                    $totalDone++;
                    break;
                case 'missed':
                    $totalMissed++;
                    break;
                case 'pending':
                    $totalPending++;
                    break;
            }
        }

        // Calculate percentage of done tasks against missed tasks
        $totalCompletion = $totalDone + $totalMissed;

        // Calculate percentage of done tasks against total completed tasks
        $percentageDone = ($totalCompletion > 0) ? ($totalDone / $totalCompletion) * 100 : 0;

        // Retrieve pending tasks
        $pendingTasks = $goals->where('status', 'pending')->count();

        $performance = [
            'totalDone' => $totalDone,
            'totalMissed' => $totalMissed,
            'totalPending' => $totalPending,
            'percentageDone' => $percentageDone,
            'inProgress' => $pendingTasks,
        ];

        $response = collect([
            'status' => true,
            'message' => "Successful.",
            'performance' => $performance
        ]);

        return response()->json($response, 200);
    }

    public function calendarOverview(Request $request){
        $user = $request->user();
        $user_type = $request->input('user_type', 'career');
        // $period = [Carbon::now()->subMonth(), Carbon::now()]; // Example period, adjust as needed
        $period = [now()->startOfMonth(), now()->endOfMonth()];


        $goals = Goal::whereNull('archived_at')
            ->where('user_id', $user->id)
            ->when($period, function($query) use($period){
                $query->whereBetween('created_at', $period);
            })
            ->orderBy('reminder_date') // Sort by reminder_date
            ->get();

        $calendar = $goals->map(function($goal) {
            return [
                'id' => $goal->id,
                'title' => $goal->title,
                'status' => $goal->status,
                'start' => Carbon::parse($goal->reminder_date)->format('Y-m-d H:i'),
                'end' => Carbon::parse($goal->period)->format('Y-m-d H:i'),
            ];
        });

        $response = collect([
            'status' => true,
            'message' => "",
            'data' => $calendar
        ]);

        return response()->json($response, 200);

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(GoalRequest $request)
    {
        $user = $request->user();

        // Retrieve the validated input data...
        $validatedData = $request->validated();
        $reminderOffsets = [
            '5 Minutes before' => '-5 minutes',
            '10 Minutes before' => '-10 minutes',
            '15 Minutes before' => '-15 minutes',
            '30 Minutes before' => '-30 minutes',
            '1 Hour before' => '-1 hour',
            '2 Hours before' => '-2 hours',
            '3 Hours before' => '-3 hours',
            '6 Hours before' => '-6 hours',
            '1 Day before' => '-1 day',
            '2 Days before' => '-2 days',
            '3 Days before' => '-3 days',
        ];

        $period = Carbon::createFromFormat('Y-m-d H:i', $validatedData['period']);
        $reminder = $validatedData['reminder'];
        $reminderOffset = $reminderOffsets[$reminder];
        $validatedData['reminder_date'] = $period->copy()->modify($reminderOffset);

         try {
            if($validatedData['type'] == 'career'){
                $validatedData['user_id'] = $user->id;
                $goal = Goal::create($validatedData);
            }

            if($validatedData['type'] == 'supervisor'){
                // Set the current company and employee based on the supervisor_code and employee_code
                $current_company = Employee::where('id', $user->default_company_id)
                                ->where('user_id', $user->id)->with('supervisor')->first();
            
                $employee = Employee::where('code', $validatedData['employee_code'])->first();

                $validatedData['supervisor_id'] = $current_company->id;
                $validatedData['employee_id'] = $employee->id;
                $validatedData['employer_id'] = $current_company->employer_id;
                $validatedData['user_id'] = $validatedData['supervisor_id'];

                $goal = Goal::create($validatedData);

                if($employee->talent) {
                    $employee->talent->notify(new SupervisorGoalNotification($goal, $current_company, $employee));
                }
            }

            return $this->successResponse(
                Goal::find($goal->id),
                'talent.goals.created'
            );

        } catch (\Exception $e) {
            return $this->errorResponse(
                'talent.goals.create_error',
                ['error' => $e->getMessage()]
            );
        }
    }



    public function show(Request $request, $id)
    {
        $user = $request->user();

        // if($user->type == 'talent'){
        //     $employee = Employee::where('code', $code)->firstOrFail();
        //     $current_company = Employee::where('id', $user->default_company_id)
        //     ->where('user_id', $user->id)->with('supervisor')->firstOrFail();

        //     if(!$this->validateEmployee($user,$employee)){
        //          return response()->json([
        //              'status' => false,
        //              'message' => 'Unautourized Access'
        //          ], 401);
        //     }
        //  }

        $goal = Goal::find($id);



        return response()->json([
            'status' => true,
            'data' => $goal,
            'message' => "",
        ], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function employee(Request $request, $code)
    {
        $user = $request->user();

        $employee = Employee::where('code', $code)->firstOrFail();

        if($user->type == 'talent'){
           $validation_result = validateEmployeeAccess($user, $employee);
            if ($validation_result !== true) {
                return $validation_result;
            }
        }

        $goals = Goal::where('employee_id', $employee->id)
                        ->where('employer_id', $employee->employer_id)
                        ->with(['supervisor'])
                        ->get();


        return response()->json([
            'status' => true,
            'message' => "",
            'data' => $goals
        ], 200);
    }

     // Employee self-assessment - Only for company/supervisor goals
    public function employeeSubmit(Request $request, $id)
    {
        $request->validate([
            'employee_status' => 'required|in:done,missed',
            'employee_comment' => 'nullable|string|max:1000',
        ]);

        try {
            $user = $request->user();
            $goal = Goal::findOrFail($id);

            // Only allow employee submit for supervisor/company goals
            if($goal->type === 'career') {
                return $this->errorResponse(
                    'goal.invalid_submit_method',
                    ['message' => 'Career goals should use the update method'],
                    400
                );
            }

            // Verify the user is the employee assigned to this goal
            $employee = Employee::where('user_id', $user->id)->first();
            if (!$employee || $goal->employee_id !== $employee->id) {
                return $this->errorResponse('unauthorized', [], 403);
            }

            // Can only submit if status is pending
            if ($goal->status !== 'pending') {
                return $this->errorResponse('goal.already_submitted', [], 400);
            }

            $goal->update([
                'employee_status' => $request->employee_status,
                'employee_comment' => $request->employee_comment,
                'status' => 'employee_submitted',
                'employee_submitted_at' => now(),
            ]);

            // Notify supervisor
            if ($goal->supervisor && $goal->supervisor->talent) {
                $goal->supervisor->talent->notify(new EmployeeGoalSubmissionNotification($goal, $employee));
            }

            return $this->successResponse(
                Goal::find($goal->id),
                'goal.employee_submitted'
            );

        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('goal.not_found');
        } catch (\Exception $e) {
            return $this->errorResponse(
                'goal.submission_error',
                ['error' => $e->getMessage()]
            );
        }
    }

    // Updated supervisor review method
    public function supervisorReview(Request $request, $id)
    {
        $request->validate([
            'supervisor_status' => 'required|in:done,missed',
            'supervisor_comment' => 'nullable|string|max:1000',
            'action' => 'required|in:approve,reject', // approve employee assessment or override
        ]);

        try {
            $user = $request->user();
            $goal = Goal::findOrFail($id);

            // Verify the user is the supervisor
            $supervisor = Employee::where('user_id', $user->id)->first();
            if (!$supervisor || $goal->supervisor_id !== $supervisor->id) {
                return $this->errorResponse('unauthorized', [], 403);
            }

            // Can only review if employee has submitted
            if ($goal->status !== 'employee_submitted') {
                return $this->errorResponse('goal.not_ready_for_review', [], 400);
            }

            $finalStatus = $request->supervisor_status;
            
            $goal->update([
                'supervisor_status' => $request->supervisor_status,
                'supervisor_comment' => $request->supervisor_comment,
                'status' => $finalStatus,
                'supervisor_reviewed_at' => now(),
            ]);

            // Notify employee of supervisor's decision
            if ($goal->employee && $goal->employee->talent) {
                $goal->employee->talent->notify(new SupervisorReviewNotification($goal, $supervisor));
            }

            return $this->successResponse(
                Goal::find($goal->id),
                'goal.supervisor_reviewed'
            );

        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('goal.not_found');
        } catch (\Exception $e) {
            return $this->errorResponse(
                'goal.review_error',
                ['error' => $e->getMessage()]
            );
        }
    }

    /**
     * Update :  Only for career goals (personal goals)
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    
    public function update(GoalRequest $request, $id)
    {
        try {
            $user = $request->user();
            $validatedData = $request->validated();
            $goal = Goal::findOrfail($id);

            // Only allow direct updates for career goals
            if($goal->type !== 'career') {
                return $this->errorResponse(
                    'talent.goals.invalid_update_method',
                    ['message' => 'Company goals must use the submit/review workflow'],
                    400
                );
            }

            // Verify user owns this career goal
            if($goal->user_id !== $user->id) {
                return $this->errorResponse('unauthorized', [], 403);
            }

            // Only update if status is pending
            if($goal->status === 'pending'){
                $goal->status = $validatedData['status'];
                $goal->save();
            } else {
                return $this->errorResponse(
                    'goal.already_completed',
                    ['message' => 'Goal has already been marked as completed'],
                    400
                );
            }

            return $this->successResponse(
                Goal::find($goal->id),
                'goals.updated'
            );

        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('talent.goals.not_found');
        } catch (\Exception $e) {
            return $this->errorResponse(
                'talent.goals.update_error',
                ['error' => $e->getMessage()]
            );
        }
    }



    /**
     * Archive the specified resource from active list.
     *
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function archive($id)
    {
        $goal = Goal::findOrFail($id);

        // $this->authorize('delete', [Goal::class, $goal]);

        $goal->archived_at = now();
        $goal->save();

        return response()->json([
            'status' => true,
            'message' => "Goal has been archived successfully.",
            'data' => Goal::find($goal->id)
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
        $goal = Goal::findOrFail($id);

        // $this->authorize('delete', [Goal::class, $goal]);

        $goal->archived_at = null;
        $goal->save();

        return response()->json([
            'status' => true,
            'message' => "Goal has been unarchived successfully.",
            'data' => Goal::find($goal->id)
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

      // New method to get goals pending employee submission
    public function pendingEmployeeGoals(Request $request)
    {
        $user = $request->user();
        $employee = Employee::where('user_id', $user->id)->first();

        if (!$employee) {
            return $this->errorResponse('employee.not_found', [], 404);
        }

        $goals = Goal::where('employee_id', $employee->id)
                    ->where('status', 'pending')
                    ->where('period', '<=', now()) // Only show goals that are due
                    ->with(['supervisor'])
                    ->get();

        return response()->json([
            'status' => true,
            'message' => "",
            'data' => $goals
        ], 200);
    }

    // New method to get goals pending supervisor review
    public function pendingSupervisorReview(Request $request)
    {
        $user = $request->user();
        $supervisor = Employee::where('user_id', $user->id)->first();

        if (!$supervisor) {
            return $this->errorResponse('supervisor.not_found', [], 404);
        }

        $goals = Goal::where('supervisor_id', $supervisor->id)
                    ->where('status', 'employee_submitted')
                    ->with(['employee'])
                    ->get();

        return response()->json([
            'status' => true,
            'message' => "",
            'data' => $goals
        ], 200);
    }
}

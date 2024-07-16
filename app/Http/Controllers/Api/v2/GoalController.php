<?php

namespace App\Http\Controllers\Api\v2;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\GoalRequest;
use App\Models\Goal;
use App\Models\Employee;
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
            'message' => "Successful."
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
            'message' => "Successful.",
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

        if($validatedData['type'] == 'career'){
            $validatedData['user_id'] = $user->id;
        }

        if($validatedData['type'] == 'supervisor'){
            $current_company = Employee::where('id', $user->default_company_id)
                             ->where('user_id', $user->id)->with('supervisor')->first();
            $employee = Employee::where('code', $validatedData['employee_code'])->first();
            $validatedData['supervisor_id'] = $current_company->id;
            $validatedData['employee_id'] = $employee->id;

            $validatedData['employer_id'] = $current_company->employer_id;
            $validatedData['user_id'] = $validatedData['supervisor_id'];
        }


        $goal = Goal::create($validatedData);

        if($goal){
            return response()->json([
                'status' => true,
                'message' => "New future goal  created successfully.",
                'data' => Goal::find($goal->id)
            ], 201);
        } else {
            return response()->json([
                'status' => false,
                'message' => "Could not complete request.",
            ], 400);
        }
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
           if(!$this->validateEmployee($user,$employee)){
                return response()->json([
                    'status' => false,
                    'message' => 'Unautourized Access'
                ], 401);
           }
        }

        $goals = Goal::where('employee_id', $employee->id)
                        ->where('employer_id', $employee->employer_id)
                        ->get();


        return response()->json([
            'status' => true,
            'message' => "",
            'data' => $goals
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
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(GoalRequest $request, $id)
    {
        $employer = $request->user();
        $validatedData = $request->validated();
        $goal = Goal::findOrfail($id);
        if($goal->status === 'pending'){
            $goal->status = $validatedData['status'];
            $goal->save();
        }
        return response()->json([
            'status' => true,
            'message' => "Goal updated successfully.",
            'data' => Goal::find($goal->id)
        ], 201);
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
}

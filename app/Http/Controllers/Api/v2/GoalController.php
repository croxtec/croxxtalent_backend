<?php

namespace App\Http\Controllers\Api\v2;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\GoalRequest;
use App\Models\Goal;
use App\Models\Employee;

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
        $per_page = $request->input('per_page', 100);
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
        if($validatedData['type'] == 'career'){
            $validatedData['user_id'] = $user->id;
        }

        if($validatedData['type'] == 'supervisor'){
            $employee = Employee::where('id', $validatedData['supervisor_id'])->first();
            $validatedData['employer_id'] = $employee->employer_id;
            $validatedData['user_id'] = $employee->employer_id;
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
            'message' => "Employee \"{$employee->name}\" updated successfully.",
            'data' => Goal::find($employee->id)
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

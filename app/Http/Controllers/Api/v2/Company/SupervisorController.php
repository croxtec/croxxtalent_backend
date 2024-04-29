<?php

namespace App\Http\Controllers\Api\v2\Company;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Supervisor;
use App\Models\Employee;
use App\Http\Requests\SupervisorRequest;

class SupervisorController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $employer = $request->user();
        // $this->authorize('view-any', Employee::class);

        $per_page = $request->input('per_page', 100);
        $sort_by = $request->input('sort_by', 'created_at');
        $sort_dir = $request->input('sort_dir', 'desc');
        $search = $request->input('search');
        $archived = $request->input('archived');
        $datatable_draw = $request->input('draw'); // if any

        $archived = $archived == 'yes' ? true : ($archived == 'no' ? false : null);

        $supervisor = Supervisor::where('employer_id', $employer->id)
                        ->when( $archived ,function ($query) use ($archived) {
                            if ($archived !== null ) {
                                if ($archived === true ) {
                                    $query->whereNotNull('archived_at');
                                } else {
                                    $query->whereNull('archived_at');
                                }
                            }
                        })->with('employee', 'department', 'department_role')
                        ->orderBy($sort_by, $sort_dir);

        if ($per_page === 'all' || $per_page <= 0 ) {
            $results = $supervisor->get();
            $supervisor = new \Illuminate\Pagination\LengthAwarePaginator($results, $results->count(), -1);
        } else {
            $supervisor = $supervisor->paginate($per_page);
        }

        $response = collect([
            'status' => true,
            "data" => $supervisor,
            'message' => ""
        ])->merge($supervisor)
          ->merge(['draw' => $datatable_draw]);
        return response()->json($response, 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(SupervisorRequest $request)
    {
        $employer = $request->user();

        $validatedData = $request->validated();
        $validatedData['employer_id'] = $employer->id;

        $isSupervisor = Supervisor::where('supervisor_id', $validatedData['supervisor_id'])
                             ->where('employer_id', $employer->id)->first();

        if(!$isSupervisor){

            $supervisor =  Supervisor::create($validatedData);

            // Send Email
            return response()->json([
                'status' => true,
                'message' => "Supervisor added successfully.",
                'data' => Employee::find($validatedData['supervisor_id'])
            ], 201);
        }  else{
            return response()->json([
                "status" => false,
                "message" => 'Supervisor already exist'
            ], 422);
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

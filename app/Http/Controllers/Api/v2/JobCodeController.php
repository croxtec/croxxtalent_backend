<?php

namespace App\Http\Controllers\Api\v2;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\EmployerJobcode as JobCode;


class JobCodeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // $this->authorize('view-any', Cv::class);

        $per_page = $request->input('per_page', 100);
        $sort_by = $request->input('sort_by', 'created_at');
        $sort_dir = $request->input('sort_dir', 'asc');
        $search = $request->input('search');
        $datatable_draw = $request->input('draw'); // if any

        $job_code = JobCode::where('employer_id', $user->id)
        ->when($search, function($query) use ($search) {
            $query->where('id', 'LIKE', "%{$search}%");
        })->orderBy($sort_by, $sort_dir);

        if ($per_page === 'all' || $per_page <= 0 ) {
            $results = $job_code->get();
            $job_code = new \Illuminate\Pagination\LengthAwarePaginator($results, $results->count(), -1);
        } else {
            $job_code = $job_code->paginate($per_page);
        }

        $response = collect([
            'status' => true,
            'message' => "Successful."
        ])->merge($job_code)->merge(['draw' => $datatable_draw]);
        return response()->json($response, 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $user = $request->user();
        $rules = [
            'job_code' => 'required'
        ];

        $validatedData = $request->validate($rules);
        $validatedData['employer_id'] = $user->id;

        $job_code = JobCode::create($validatedData);

       if ($job_code) {
            return response()->json([
                'status' => true,
                'message' => "Job Code \"{$job_code->job_code}\" created successfully.",
                'data' => JobCode::find($job_code->id)
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
        $user = $request->user();
        $job_code = JobCode::findOrFail($id);

        return response()->json([
            'status' => true,
            'message' => "Successful.",
            'data' => $job_code
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
        $rules = [
            'job_code' => 'required',
            'manager1_id' => 'integer'
        ];

        $validatedData = $request->validate($rules);

        $job_code = JobCode::findOrFail($id);

        $job_code->update($request->all());

        return response()->json([
            'status' => true,
            'message' => "Job Code updated successfully.",
            'data' =>  JobCode::findOrFail($id)
        ], 201);
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

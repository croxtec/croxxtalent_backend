<?php

namespace App\Http\Controllers\Api\v2\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\JobTitleRequest;
use App\Models\JobTitle;

class JobTitleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $this->authorize('view-any', JobTitle::class);

        $per_page = $request->input('per_page', 100);
        $sort_by = $request->input('sort_by', 'name');
        $sort_dir = $request->input('sort_dir', 'asc');
        $search = $request->input('search');
        $archived = $request->input('archived');
        $datatable_draw = $request->input('draw'); // if any
        $industry = $request->input('industry');

        $archived = $archived == 'yes' ? true : ($archived == 'no' ? false : null);

        $jobTitles = JobTitle::where( function ($query) use ($archived) {
            if ($archived !== null ) {
                if ($archived === true ) {
                    $query->whereNotNull('archived_at');
                } else {
                    $query->whereNull('archived_at');
                }
            }
        })->where( function($query) use ($search) {
            $query->where('name', 'LIKE', "%{$search}%");
        })
        ->when($industry, function($query) use ($industry){
            $query->where('industry_id', $industry);
        })
        ->orderBy($sort_by, $sort_dir);

        if ($per_page === 'all' || $per_page <= 0 ) {
            $results = $jobTitles->get();
            $jobTitles = new \Illuminate\Pagination\LengthAwarePaginator($results, $results->count(), -1);
        } else {
            $jobTitles = $jobTitles->paginate($per_page);
        }

        foreach($jobTitles as $job){
            $job->industry;
        }

        $response = collect([
            'status' => true,
            'message' => "Successful."
        ])->merge($jobTitles)->merge(['draw' => $datatable_draw]);
        return response()->json($response, 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Models\Http\Requests\JobTitleRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(JobTitleRequest $request)
    {
        // Authorization is declared in the Form Request

        // Retrieve the validated input data...
        $validatedData = $request->validated();
        $jobTitle = JobTitle::create($validatedData);
        if ($jobTitle) {
            return response()->json([
                'status' => true,
                'message' => "Job title \"{$jobTitle->name}\" created successfully.",
                'data' => JobTitle::find($jobTitle->id)
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
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $jobTitle = JobTitle::findOrFail($id);

        $this->authorize('view', [JobTitle::class, $jobTitle]);

        return response()->json([
            'status' => true,
            'message' => "Successful.",
            'data' => $jobTitle
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Models\Http\Requests\JobTitleRequest  $request
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function update(JobTitleRequest $request, $id)
    {
        // Authorization is declared in the JobTitleRequest

        // Retrieve the validated input data....
        $validatedData = $request->validated();
        $jobTitle = JobTitle::findOrFail($id);
        $jobTitle->update($validatedData);
        return response()->json([
            'status' => true,
            'message' => "Job title \"{$jobTitle->name}\" updated successfully.",
            'data' => JobTitle::find($jobTitle->id)
        ], 200);
    }

    /**
     * Archive the specified resource from active list.
     *
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function archive($id)
    {
        $jobTitle = JobTitle::findOrFail($id);

        $this->authorize('delete', [JobTitle::class, $jobTitle]);

        $jobTitle->archived_at = now();
        $jobTitle->save();

        return response()->json([
            'status' => true,
            'message' => "Job title \"{$jobTitle->name}\" archived successfully.",
            'data' => JobTitle::find($jobTitle->id)
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
        $jobTitle = JobTitle::findOrFail($id);

        $this->authorize('delete', [JobTitle::class, $jobTitle]);

        $jobTitle->archived_at = null;
        $jobTitle->save();

        return response()->json([
            'status' => true,
            'message' => "Job title \"{$jobTitle->name}\" unarchived successfully.",
            'data' => JobTitle::find($jobTitle->id)
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $jobTitle = JobTitle::findOrFail($id);

        $this->authorize('delete', [JobTitle::class, $jobTitle]);

        $name = $jobTitle->name;
        // check if the record is linked to other records
        $relatedRecordsCount = related_records_count(JobTitle::class, $jobTitle);

        if ($relatedRecordsCount <= 0) {
            $jobTitle->delete();
            return response()->json([
                'status' => true,
                'message' => "Job title \"{$name}\" deleted successfully.",
            ], 200);
        } else {
            return response()->json([
                'status' => false,
                'message' => "The \"{$name}\" record cannot be deleted because it is associated with {$relatedRecordsCount} other record(s). You can archive it instead.",
            ], 400);
        }
    }
}

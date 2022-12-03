<?php

namespace App\Http\Controllers\Api\v1\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\CourseOfStudyRequest;
use App\Models\CourseOfStudy;

class CourseOfStudyController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $this->authorize('view-any', CourseOfStudy::class);

        $per_page = $request->input('per_page', 100);
        $sort_by = $request->input('sort_by', 'name');
        $sort_dir = $request->input('sort_dir', 'asc');
        $search = $request->input('search');
        $archived = $request->input('archived');
        $datatable_draw = $request->input('draw'); // if any

        $archived = $archived == 'yes' ? true : ($archived == 'no' ? false : null);
        
        $courseOfStudies = CourseOfStudy::where( function ($query) use ($archived) {
            if ($archived !== null ) {
                if ($archived === true ) {
                    $query->whereNotNull('archived_at');
                } else {
                    $query->whereNull('archived_at');
                }                 
            }
        })->where( function($query) use ($search) {
            $query->where('name', 'LIKE', "%{$search}%");
        })->orderBy($sort_by, $sort_dir);

        if ($per_page === 'all' || $per_page <= 0 ) {
            $results = $courseOfStudies->get();
            $courseOfStudies = new \Illuminate\Pagination\LengthAwarePaginator($results, $results->count(), -1);
        } else {
            $courseOfStudies = $courseOfStudies->paginate($per_page);
        }
        
        $response = collect([
            'status' => true, 
            'message' => "Successful."
        ])->merge($courseOfStudies)->merge(['draw' => $datatable_draw]);
        return response()->json($response, 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Models\Http\Requests\CourseOfStudyRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CourseOfStudyRequest $request)
    {
        // Authorization is declared in the Form Request

        // Retrieve the validated input data...
        $validatedData = $request->validated();
        $courseOfStudy = CourseOfStudy::create($validatedData);
        if ($courseOfStudy) {
            return response()->json([
                'status' => true, 
                'message' => "Course of study \"{$courseOfStudy->name}\" created successfully.",
                'data' => CourseOfStudy::find($courseOfStudy->id)
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
        $courseOfStudy = CourseOfStudy::findOrFail($id);

        $this->authorize('view', [CourseOfStudy::class, $courseOfStudy]);
        
        return response()->json([
            'status' => true, 
            'message' => "Successful.",
            'data' => $courseOfStudy
        ], 200);        
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Models\Http\Requests\CourseOfStudyRequest  $request
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function update(CourseOfStudyRequest $request, $id)
    {
        // Authorization is declared in the CourseOfStudyRequest

        // Retrieve the validated input data....
        $validatedData = $request->validated();
        $courseOfStudy = CourseOfStudy::findOrFail($id);
        $courseOfStudy->update($validatedData);
        return response()->json([
            'status' => true, 
            'message' => "Course of study \"{$courseOfStudy->name}\" updated successfully.",
            'data' => CourseOfStudy::find($courseOfStudy->id)
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
        $courseOfStudy = CourseOfStudy::findOrFail($id);

        $this->authorize('delete', [CourseOfStudy::class, $courseOfStudy]);

        $courseOfStudy->archived_at = now();
        $courseOfStudy->save();

        return response()->json([
            'status' => true, 
            'message' => "Course of study \"{$courseOfStudy->name}\" archived successfully.",
            'data' => CourseOfStudy::find($courseOfStudy->id)
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
        $courseOfStudy = CourseOfStudy::findOrFail($id);

        $this->authorize('delete', [CourseOfStudy::class, $courseOfStudy]);

        $courseOfStudy->archived_at = null;
        $courseOfStudy->save();

        return response()->json([
            'status' => true, 
            'message' => "Course of study \"{$courseOfStudy->name}\" unarchived successfully.",
            'data' => CourseOfStudy::find($courseOfStudy->id)
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
        $courseOfStudy = CourseOfStudy::findOrFail($id);

        $this->authorize('delete', [CourseOfStudy::class, $courseOfStudy]);

        $name = $courseOfStudy->name;
        // check if the record is linked to other records
        $relatedRecordsCount = related_records_count(CourseOfStudy::class, $courseOfStudy);

        if ($relatedRecordsCount <= 0) {
            $courseOfStudy->delete();
            return response()->json([
                'status' => true, 
                'message' => "Course of study \"{$name}\" deleted successfully.",
            ], 200);
        } else {
            return response()->json([
                'status' => false, 
                'message' => "The \"{$name}\" record cannot be deleted because it is associated with {$relatedRecordsCount} other record(s). You can archive it instead.",
            ], 400);
        }              
    }
}

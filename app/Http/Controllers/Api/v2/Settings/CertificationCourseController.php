<?php

namespace App\Http\Controllers\Api\v1\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\CertificationCourseRequest;
use App\Models\CertificationCourse;

class CertificationCourseController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $this->authorize('view-any', CertificationCourse::class);

        $per_page = $request->input('per_page', 100);
        $sort_by = $request->input('sort_by', 'name');
        $sort_dir = $request->input('sort_dir', 'asc');
        $search = $request->input('search');
        $archived = $request->input('archived');
        $datatable_draw = $request->input('draw'); // if any

        $archived = $archived == 'yes' ? true : ($archived == 'no' ? false : null);
        
        $certificationCourses = CertificationCourse::where( function ($query) use ($archived) {
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
            $results = $certificationCourses->get();
            $certificationCourses = new \Illuminate\Pagination\LengthAwarePaginator($results, $results->count(), -1);
        } else {
            $certificationCourses = $certificationCourses->paginate($per_page);
        }
        
        $response = collect([
            'status' => true, 
            'message' => "Successful."
        ])->merge($certificationCourses)->merge(['draw' => $datatable_draw]);
        return response()->json($response, 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Models\Http\Requests\CertificationCourseRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CertificationCourseRequest $request)
    {
        // Authorization is declared in the Form Request

        // Retrieve the validated input data...
        $validatedData = $request->validated();
        $certificationCourse = CertificationCourse::create($validatedData);
        if ($certificationCourse) {
            return response()->json([
                'status' => true, 
                'message' => "Certification course \"{$certificationCourse->name}\" created successfully.",
                'data' => CertificationCourse::find($certificationCourse->id)
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
        $certificationCourse = CertificationCourse::findOrFail($id);

        $this->authorize('view', [CertificationCourse::class, $certificationCourse]);
        
        return response()->json([
            'status' => true, 
            'message' => "Successful.",
            'data' => $certificationCourse
        ], 200);        
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Models\Http\Requests\CertificationCourseRequest  $request
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function update(CertificationCourseRequest $request, $id)
    {
        // Authorization is declared in the CertificationCourseRequest

        // Retrieve the validated input data....
        $validatedData = $request->validated();
        $certificationCourse = CertificationCourse::findOrFail($id);
        $certificationCourse->update($validatedData);
        return response()->json([
            'status' => true, 
            'message' => "Certification course \"{$certificationCourse->name}\" updated successfully.",
            'data' => CertificationCourse::find($certificationCourse->id)
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
        $certificationCourse = CertificationCourse::findOrFail($id);

        $this->authorize('delete', [CertificationCourse::class, $certificationCourse]);

        $certificationCourse->archived_at = now();
        $certificationCourse->save();

        return response()->json([
            'status' => true, 
            'message' => "Certification course \"{$certificationCourse->name}\" archived successfully.",
            'data' => CertificationCourse::find($certificationCourse->id)
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
        $certificationCourse = CertificationCourse::findOrFail($id);

        $this->authorize('delete', [CertificationCourse::class, $certificationCourse]);

        $certificationCourse->archived_at = null;
        $certificationCourse->save();

        return response()->json([
            'status' => true, 
            'message' => "Certification course \"{$certificationCourse->name}\" unarchived successfully.",
            'data' => CertificationCourse::find($certificationCourse->id)
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
        $certificationCourse = CertificationCourse::findOrFail($id);

        $this->authorize('delete', [CertificationCourse::class, $certificationCourse]);

        $name = $certificationCourse->name;
        // check if the record is linked to other records
        $relatedRecordsCount = related_records_count(CertificationCourse::class, $certificationCourse);

        if ($relatedRecordsCount <= 0) {
            $certificationCourse->delete();
            return response()->json([
                'status' => true, 
                'message' => "Certification course \"{$name}\" deleted successfully.",
            ], 200);
        } else {
            return response()->json([
                'status' => false, 
                'message' => "The \"{$name}\" record cannot be deleted because it is associated with {$relatedRecordsCount} other record(s). You can archive it instead.",
            ], 400);
        }              
    }
}

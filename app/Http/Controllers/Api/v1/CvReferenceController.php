<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Requests\CvReferenceRequest;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Cv;
use App\Models\CvReference;

class CvReferenceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  string  $cv_id
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, $cv_id)
    {
        $cv = Cv::findOrFail($cv_id);

        $this->authorize('view-any', Cv::class);

        $per_page = $request->input('per_page', 100);
        $sort_by = $request->input('sort_by', 'name');
        $sort_dir = $request->input('sort_dir', 'asc');
        $search = $request->input('search');
        $datatable_draw = $request->input('draw'); // if any

        $cvReferences = CvReference::where('cv_id', $cv->id)
        ->where( function($query) use ($search) {
            $query->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('company', 'LIKE', "%{$search}%");
        })->orderBy($sort_by, $sort_dir);

        if ($per_page === 'all' || $per_page <= 0 ) {
            $results = $cvReferences->get();
            $cvReferences = new \Illuminate\Pagination\LengthAwarePaginator($results, $results->count(), -1);
        } else {
            $cvReferences = $cvReferences->paginate($per_page);
        }
        
        $response = collect([
            'status' => true, 
            'message' => "Successful."
        ])->merge($cvReferences)->merge(['draw' => $datatable_draw]);
        return response()->json($response, 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Models\Http\Requests\CvReferenceRequest  $request
     * @param  string  $cv_id
     * @return \Illuminate\Http\Response
     */
    public function store(CvReferenceRequest $request, $cv_id)
    {
        $cv = Cv::findOrFail($cv_id);

        // Authorization was declared in the Form Request

        // Retrieve the validated input data...
        $validatedData = $request->validated(); 
        $validatedData['cv_id'] = $cv->id;
        $cvReference = CvReference::create($validatedData);
        if ($cvReference) {
            return response()->json([
                'status' => true, 
                'message' => "Reference created successfully.",
                'data' => $cvReference
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
     * @param  string  $cv_id
     * @param  string  $cv_reference_id
     * @return \Illuminate\Http\Response
     */
    public function show($cv_id, $cv_reference_id)
    {
        $cv = Cv::findOrFail($cv_id);
        $cvReference = CvReference::findOrFail($cv_reference_id);
        if ($cv->id != $cvReference->cv_id) {
            return response()->json([
                'status' => false, 
                'message' => "Unrelated request.",
            ], 400);
        }
        $this->authorize('view', [Cv::class, $cv]);
        
        return response()->json([
            'status' => true,
            'message' => "Successful.",
            'data' => $cv
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Models\Http\Requests\CvReferenceRequest  $request
     * @param  string  $cv_id
     * @param  string  $cv_reference_id
     * @return \Illuminate\Http\Response
     */
    public function update(CvReferenceRequest $request, $cv_id, $cv_reference_id)
    {
        $cv = Cv::findOrFail($cv_id);
        $cvReference = CvReference::findOrFail($cv_reference_id);
        if ($cv->id != $cvReference->cv_id) {
            return response()->json([
                'status' => false, 
                'message' => "Unrelated request.",
            ], 400);
        }

        // Authorization was declared in the Form Request

        // Retrieve the validated input data....
        $validatedData = $request->validated();
        $cvReference->update($validatedData);
        return response()->json([
            'status' => true, 
            'message' => "Reference updated successfully.",
            'data' => CvReference::findOrFail($cvReference->id)
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  string  $id
     * @param  string  $cv_reference_id
     * @return \Illuminate\Http\Response
     */
    public function destroy($cv_id, $cv_reference_id)
    {
        $cv = Cv::findOrFail($cv_id);
        $cvReference = CvReference::findOrFail($cv_reference_id);
        if ($cv->id != $cvReference->cv_id) {
            return response()->json([
                'status' => false, 
                'message' => "Unrelated request.",
            ], 400);
        }

        $this->authorize('delete', [Cv::class, $cv]);

        $cvReference->delete();
        return response()->json([
            'status' => true, 
            'message' => "Reference deleted successfully.",
        ], 200);              
    }
}

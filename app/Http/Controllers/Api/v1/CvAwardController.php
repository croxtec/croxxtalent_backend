<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Requests\CvAwardRequest;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Cv;
use App\Models\CvAward;

class CvAwardController extends Controller
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
        $sort_by = $request->input('sort_by', 'date');
        $sort_dir = $request->input('sort_dir', 'desc');
        $search = $request->input('search');
        $datatable_draw = $request->input('draw'); // if any

        $cvAwards = CvAward::where('cv_id', $cv->id)
        ->where( function($query) use ($search) {
            $query->where('title', 'LIKE', "%{$search}%")
                    ->orWhere('organization', 'LIKE', "%{$search}%");
        })->orderBy($sort_by, $sort_dir);

        if ($per_page === 'all' || $per_page <= 0 ) {
            $results = $cvAwards->get();
            $cvAwards = new \Illuminate\Pagination\LengthAwarePaginator($results, $results->count(), -1);
        } else {
            $cvAwards = $cvAwards->paginate($per_page);
        }
        
        $response = collect([
            'status' => true, 
            'message' => "Successful."
        ])->merge($cvAwards)->merge(['draw' => $datatable_draw]);
        return response()->json($response, 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Models\Http\Requests\CvAwardRequest  $request
     * @param  string  $cv_id
     * @return \Illuminate\Http\Response
     */
    public function store(CvAwardRequest $request, $cv_id)
    {
        $cv = Cv::findOrFail($cv_id);

        // Authorization was declared in the Form Request

        // Retrieve the validated input data...
        $validatedData = $request->validated(); 
        $validatedData['cv_id'] = $cv->id;
        $cvAward = CvAward::create($validatedData);
        if ($cvAward) {
            return response()->json([
                'status' => true, 
                'message' => "Award created successfully.",
                'data' => $cvAward
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
     * @param  string  $cv_award_id
     * @return \Illuminate\Http\Response
     */
    public function show($cv_id, $cv_award_id)
    {
        $cv = Cv::findOrFail($cv_id);
        $cvAward = CvAward::findOrFail($cv_award_id);
        if ($cv->id != $cvAward->cv_id) {
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
     * @param  \App\Models\Http\Requests\CvAwardRequest  $request
     * @param  string  $cv_id
     * @param  string  $cv_award_id
     * @return \Illuminate\Http\Response
     */
    public function update(CvAwardRequest $request, $cv_id, $cv_award_id)
    {
        $cv = Cv::findOrFail($cv_id);
        $cvAward = CvAward::findOrFail($cv_award_id);
        if ($cv->id != $cvAward->cv_id) {
            return response()->json([
                'status' => false, 
                'message' => "Unrelated request.",
            ], 400);
        }

        // Authorization was declared in the Form Request

        // Retrieve the validated input data....
        $validatedData = $request->validated();
        $cvAward->update($validatedData);
        return response()->json([
            'status' => true, 
            'message' => "Award updated successfully.",
            'data' => CvAward::findOrFail($cvAward->id)
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  string  $id
     * @param  string  $cv_award_id
     * @return \Illuminate\Http\Response
     */
    public function destroy($cv_id, $cv_award_id)
    {
        $cv = Cv::findOrFail($cv_id);
        $cvAward = CvAward::findOrFail($cv_award_id);
        if ($cv->id != $cvAward->cv_id) {
            return response()->json([
                'status' => false, 
                'message' => "Unrelated request.",
            ], 400);
        }

        $this->authorize('delete', [Cv::class, $cv]);

        $cvAward->delete();
        return response()->json([
            'status' => true, 
            'message' => "Award deleted successfully.",
        ], 200);              
    }
}

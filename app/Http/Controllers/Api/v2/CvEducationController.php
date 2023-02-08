<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Requests\CvEducationRequest;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Cv;
use App\Models\CvEducation;

class CvEducationController extends Controller
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
        $sort_by = $request->input('sort_by', 'start_date');
        $sort_dir = $request->input('sort_dir', 'desc');
        $search = $request->input('search');
        $current = $request->input('current');
        $datatable_draw = $request->input('draw'); // if any

        $current = $current == 'yes' ? true : ($current == 'no' ? false : null);

        $cvEducations = CvEducation::where('cv_id', $cv->id)
        ->where( function ($query) use ($current) {
            if ($current !== null ) {
                $query->where('is_current', $current);                 
            }
        })->where( function($query) use ($search) {
            $query->where('school', 'LIKE', "%{$search}%");
        })->orderBy($sort_by, $sort_dir);

        if ($per_page === 'all' || $per_page <= 0 ) {
            $results = $cvEducations->get();
            $cvEducations = new \Illuminate\Pagination\LengthAwarePaginator($results, $results->count(), -1);
        } else {
            $cvEducations = $cvEducations->paginate($per_page);
        }
        
        $response = collect([
            'status' => true, 
            'message' => "Successful."
        ])->merge($cvEducations)->merge(['draw' => $datatable_draw]);
        return response()->json($response, 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Models\Http\Requests\CvEducationRequest  $request
     * @param  string  $cv_id
     * @return \Illuminate\Http\Response
     */
    public function store(CvEducationRequest $request, $cv_id)
    {
        $cv = Cv::findOrFail($cv_id);

        // Authorization was declared in the Form Request

        // Retrieve the validated input data...
        $validatedData = $request->validated(); 
        $validatedData['cv_id'] = $cv->id;
        $cvEducation = CvEducation::create($validatedData);
        if ($cvEducation) {
            return response()->json([
                'status' => true, 
                'message' => "Education created successfully.",
                'data' => $cvEducation
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
     * @param  string  $cv_education_id
     * @return \Illuminate\Http\Response
     */
    public function show($cv_id, $cv_education_id)
    {
        $cv = Cv::findOrFail($cv_id);
        $cvEducation = CvEducation::findOrFail($cv_education_id);
        if ($cv->id != $cvEducation->cv_id) {
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
     * @param  \App\Models\Http\Requests\CvEducationRequest  $request
     * @param  string  $cv_id
     * @param  string  $cv_education_id
     * @return \Illuminate\Http\Response
     */
    public function update(CvEducationRequest $request, $cv_id, $cv_education_id)
    {
        $cv = Cv::findOrFail($cv_id);
        $cvEducation = CvEducation::findOrFail($cv_education_id);
        if ($cv->id != $cvEducation->cv_id) {
            return response()->json([
                'status' => false, 
                'message' => "Unrelated request.",
            ], 400);
        }

        // Authorization was declared in the Form Request

        // Retrieve the validated input data....
        $validatedData = $request->validated();
        $cvEducation->update($validatedData);
        return response()->json([
            'status' => true, 
            'message' => "Education updated successfully.",
            'data' => CvEducation::findOrFail($cvEducation->id)
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  string  $id
     * @param  string  $cv_education_id
     * @return \Illuminate\Http\Response
     */
    public function destroy($cv_id, $cv_education_id)
    {
        $cv = Cv::findOrFail($cv_id);
        $cvEducation = CvEducation::findOrFail($cv_education_id);
        if ($cv->id != $cvEducation->cv_id) {
            return response()->json([
                'status' => false, 
                'message' => "Unrelated request.",
            ], 400);
        }

        $this->authorize('delete', [Cv::class, $cv]);

        $cvEducation->delete();
        return response()->json([
            'status' => true, 
            'message' => "Education deleted successfully.",
        ], 200);              
    }
}

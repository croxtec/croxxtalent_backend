<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Requests\CvLanguageRequest;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Cv;
use App\Models\CvLanguage;

class CvLanguageController extends Controller
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
        $sort_by = $request->input('sort_by', 'created_at');
        $sort_dir = $request->input('sort_dir', 'asc');
        $search = $request->input('search');
        $datatable_draw = $request->input('draw'); // if any

        $cvLanguages = CvLanguage::where('cv_id', $cv->id)
        ->where( function($query) use ($search) {
            $query->where('id', 'LIKE', "%{$search}%");
        })->orderBy($sort_by, $sort_dir);

        if ($per_page === 'all' || $per_page <= 0 ) {
            $results = $cvLanguages->get();
            $cvLanguages = new \Illuminate\Pagination\LengthAwarePaginator($results, $results->count(), -1);
        } else {
            $cvLanguages = $cvLanguages->paginate($per_page);
        }
        
        $response = collect([
            'status' => true, 
            'message' => "Successful."
        ])->merge($cvLanguages)->merge(['draw' => $datatable_draw]);
        return response()->json($response, 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Models\Http\Requests\CvLanguageRequest  $request
     * @param  string  $cv_id
     * @return \Illuminate\Http\Response
     */
    public function store(CvLanguageRequest $request, $cv_id)
    {
        $cv = Cv::findOrFail($cv_id);

        // Authorization was declared in the Form Request

        // Retrieve the validated input data...
        $validatedData = $request->validated(); 
        $validatedData['cv_id'] = $cv->id;
        $cvLanguage = CvLanguage::updateOrCreate(
            ['cv_id' => $validatedData['cv_id'], 'language_id' => $validatedData['language_id']],
            $validatedData
        );
        if ($cvLanguage) {
            return response()->json([
                'status' => true, 
                'message' => "Language created successfully.",
                'data' => $cvLanguage
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
     * @param  string  $cv_language_id
     * @return \Illuminate\Http\Response
     */
    public function show($cv_id, $cv_language_id)
    {
        $cv = Cv::findOrFail($cv_id);
        $cvLanguage = CvLanguage::findOrFail($cv_language_id);
        if ($cv->id != $cvLanguage->cv_id) {
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
     * @param  \App\Models\Http\Requests\CvLanguageRequest  $request
     * @param  string  $cv_id
     * @param  string  $cv_language_id
     * @return \Illuminate\Http\Response
     */
    public function update(CvLanguageRequest $request, $cv_id, $cv_language_id)
    {
        $cv = Cv::findOrFail($cv_id);
        $cvLanguage = CvLanguage::findOrFail($cv_language_id);
        if ($cv->id != $cvLanguage->cv_id) {
            return response()->json([
                'status' => false, 
                'message' => "Unrelated request.",
            ], 400);
        }

        // Authorization was declared in the Form Request

        // Retrieve the validated input data....
        $validatedData = $request->validated();
        $cvLanguage->update($validatedData);
        return response()->json([
            'status' => true, 
            'message' => "Language updated successfully.",
            'data' => CvLanguage::findOrFail($cvLanguage->id)
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  string  $id
     * @param  string  $cv_language_id
     * @return \Illuminate\Http\Response
     */
    public function destroy($cv_id, $cv_language_id)
    {
        $cv = Cv::findOrFail($cv_id);
        $cvLanguage = CvLanguage::findOrFail($cv_language_id);
        if ($cv->id != $cvLanguage->cv_id) {
            return response()->json([
                'status' => false, 
                'message' => "Unrelated request.",
            ], 400);
        }

        $this->authorize('delete', [Cv::class, $cv]);

        $cvLanguage->delete();
        return response()->json([
            'status' => true, 
            'message' => "Language deleted successfully.",
        ], 200);              
    }
}

<?php

namespace App\Http\Controllers\Api\v1\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\LanguageRequest;
use App\Models\Language;

class LanguageController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $this->authorize('view-any', Language::class);

        $per_page = $request->input('per_page', 100);
        $sort_by = $request->input('sort_by', 'name');
        $sort_dir = $request->input('sort_dir', 'asc');
        $search = $request->input('search');
        $archived = $request->input('archived');
        $datatable_draw = $request->input('draw'); // if any

        $archived = $archived == 'yes' ? true : ($archived == 'no' ? false : null);
        
        $languages = Language::where( function ($query) use ($archived) {
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
            $results = $languages->get();
            $languages = new \Illuminate\Pagination\LengthAwarePaginator($results, $results->count(), -1);
        } else {
            $languages = $languages->paginate($per_page);
        }
        
        $response = collect([
            'status' => true, 
            'message' => "Successful."
        ])->merge($languages)->merge(['draw' => $datatable_draw]);
        return response()->json($response, 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Models\Http\Requests\LanguageRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(LanguageRequest $request)
    {
        // Authorization is declared in the Form Request

        // Retrieve the validated input data...
        $validatedData = $request->validated();
        $language = Language::create($validatedData);
        if ($language) {
            return response()->json([
                'status' => true, 
                'message' => "Language \"{$language->name}\" created successfully.",
                'data' => Language::find($language->id)
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
        $language = Language::findOrFail($id);

        $this->authorize('view', [Language::class, $language]);
        
        return response()->json([
            'status' => true, 
            'message' => "Successful.",
            'data' => $language
        ], 200);        
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Models\Http\Requests\LanguageRequest  $request
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function update(LanguageRequest $request, $id)
    {
        // Authorization is declared in the LanguageRequest

        // Retrieve the validated input data....
        $validatedData = $request->validated();
        $language = Language::findOrFail($id);
        $language->update($validatedData);
        return response()->json([
            'status' => true, 
            'message' => "Language \"{$language->name}\" updated successfully.",
            'data' => Language::find($language->id)
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
        $language = Language::findOrFail($id);

        $this->authorize('delete', [Language::class, $language]);

        $language->archived_at = now();
        $language->save();

        return response()->json([
            'status' => true, 
            'message' => "Language \"{$language->name}\" archived successfully.",
            'data' => Language::find($language->id)
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
        $language = Language::findOrFail($id);

        $this->authorize('delete', [Language::class, $language]);

        $language->archived_at = null;
        $language->save();

        return response()->json([
            'status' => true, 
            'message' => "Language \"{$language->name}\" unarchived successfully.",
            'data' => Language::find($language->id)
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
        $language = Language::findOrFail($id);

        $this->authorize('delete', [Language::class, $language]);

        $name = $language->name;
        // check if the record is linked to other records
        $relatedRecordsCount = related_records_count(Language::class, $language);

        if ($relatedRecordsCount <= 0) {
            $language->delete();
            return response()->json([
                'status' => true, 
                'message' => "Language \"{$name}\" deleted successfully.",
            ], 200);
        } else {
            return response()->json([
                'status' => false, 
                'message' => "The \"{$name}\" record cannot be deleted because it is associated with {$relatedRecordsCount} other record(s). You can archive it instead.",
            ], 400);
        }              
    }
}

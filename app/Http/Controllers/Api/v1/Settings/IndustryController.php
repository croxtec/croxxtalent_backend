<?php

namespace App\Http\Controllers\Api\v1\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\IndustryRequest;
use App\Models\Industry;

class IndustryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $this->authorize('view-any', Industry::class);

        $per_page = $request->input('per_page', 100);
        $sort_by = $request->input('sort_by', 'name');
        $sort_dir = $request->input('sort_dir', 'asc');
        $search = $request->input('search');
        $archived = $request->input('archived');
        $datatable_draw = $request->input('draw'); // if any

        $archived = $archived == 'yes' ? true : ($archived == 'no' ? false : null);
        
        $industries = Industry::where( function ($query) use ($archived) {
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
            $results = $industries->get();
            $industries = new \Illuminate\Pagination\LengthAwarePaginator($results, $results->count(), -1);
        } else {
            $industries = $industries->paginate($per_page);
        }
        
        $response = collect([
            'status' => true, 
            'message' => "Successful."
        ])->merge($industries)->merge(['draw' => $datatable_draw]);
        return response()->json($response, 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Models\Http\Requests\IndustryRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(IndustryRequest $request)
    {
        // Authorization is declared in the Form Request

        // Retrieve the validated input data...
        $validatedData = $request->validated();
        $industry = Industry::create($validatedData);
        if ($industry) {
            return response()->json([
                'status' => true, 
                'message' => "Industry \"{$industry->name}\" created successfully.",
                'data' => Industry::find($industry->id)
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
        $industry = Industry::findOrFail($id);

        $this->authorize('view', [Industry::class, $industry]);
        
        return response()->json([
            'status' => true, 
            'message' => "Successful.",
            'data' => $industry
        ], 200);        
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Models\Http\Requests\IndustryRequest  $request
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function update(IndustryRequest $request, $id)
    {
        // Authorization is declared in the IndustryRequest

        // Retrieve the validated input data....
        $validatedData = $request->validated();
        $industry = Industry::findOrFail($id);
        $industry->update($validatedData);
        return response()->json([
            'status' => true, 
            'message' => "Industry \"{$industry->name}\" updated successfully.",
            'data' => Industry::find($industry->id)
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
        $industry = Industry::findOrFail($id);

        $this->authorize('delete', [Industry::class, $industry]);

        $industry->archived_at = now();
        $industry->save();

        return response()->json([
            'status' => true, 
            'message' => "Industry \"{$industry->name}\" archived successfully.",
            'data' => Industry::find($industry->id)
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
        $industry = Industry::findOrFail($id);

        $this->authorize('delete', [Industry::class, $industry]);

        $industry->archived_at = null;
        $industry->save();

        return response()->json([
            'status' => true, 
            'message' => "Industry \"{$industry->name}\" unarchived successfully.",
            'data' => Industry::find($industry->id)
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
        $industry = Industry::findOrFail($id);

        $this->authorize('delete', [Industry::class, $industry]);

        $name = $industry->name;
        // check if the record is linked to other records
        $relatedRecordsCount = related_records_count(Industry::class, $industry);

        if ($relatedRecordsCount <= 0) {
            $industry->delete();
            return response()->json([
                'status' => true, 
                'message' => "Industry \"{$name}\" deleted successfully.",
            ], 200);
        } else {
            return response()->json([
                'status' => false, 
                'message' => "The \"{$name}\" record cannot be deleted because it is associated with {$relatedRecordsCount} other record(s). You can archive it instead.",
            ], 400);
        }              
    }
}

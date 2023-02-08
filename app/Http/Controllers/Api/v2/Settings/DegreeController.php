<?php

namespace App\Http\Controllers\Api\v1\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\DegreeRequest;
use App\Models\Degree;

class DegreeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $this->authorize('view-any', Degree::class);

        $per_page = $request->input('per_page', 100);
        $sort_by = $request->input('sort_by', 'name');
        $sort_dir = $request->input('sort_dir', 'asc');
        $search = $request->input('search');
        $archived = $request->input('archived');
        $datatable_draw = $request->input('draw'); // if any

        $archived = $archived == 'yes' ? true : ($archived == 'no' ? false : null);
        
        $degrees = Degree::where( function ($query) use ($archived) {
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
            $results = $degrees->get();
            $degrees = new \Illuminate\Pagination\LengthAwarePaginator($results, $results->count(), -1);
        } else {
            $degrees = $degrees->paginate($per_page);
        }
        
        $response = collect([
            'status' => true, 
            'message' => "Successful."
        ])->merge($degrees)->merge(['draw' => $datatable_draw]);
        return response()->json($response, 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Models\Http\Requests\DegreeRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(DegreeRequest $request)
    {
        // Authorization is declared in the Form Request

        // Retrieve the validated input data...
        $validatedData = $request->validated();
        $degree = Degree::create($validatedData);
        if ($degree) {
            return response()->json([
                'status' => true, 
                'message' => "Degree \"{$degree->name}\" created successfully.",
                'data' => Degree::find($degree->id)
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
        $degree = Degree::findOrFail($id);

        $this->authorize('view', [Degree::class, $degree]);
        
        return response()->json([
            'status' => true, 
            'message' => "Successful.",
            'data' => $degree
        ], 200);        
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Models\Http\Requests\DegreeRequest  $request
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function update(DegreeRequest $request, $id)
    {
        // Authorization is declared in the DegreeRequest

        // Retrieve the validated input data....
        $validatedData = $request->validated();
        $degree = Degree::findOrFail($id);
        $degree->update($validatedData);
        return response()->json([
            'status' => true, 
            'message' => "Degree \"{$degree->name}\" updated successfully.",
            'data' => Degree::find($degree->id)
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
        $degree = Degree::findOrFail($id);

        $this->authorize('delete', [Degree::class, $degree]);

        $degree->archived_at = now();
        $degree->save();

        return response()->json([
            'status' => true, 
            'message' => "Degree \"{$degree->name}\" archived successfully.",
            'data' => Degree::find($degree->id)
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
        $degree = Degree::findOrFail($id);

        $this->authorize('delete', [Degree::class, $degree]);

        $degree->archived_at = null;
        $degree->save();

        return response()->json([
            'status' => true, 
            'message' => "Degree \"{$degree->name}\" unarchived successfully.",
            'data' => Degree::find($degree->id)
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
        $degree = Degree::findOrFail($id);

        $this->authorize('delete', [Degree::class, $degree]);

        $name = $degree->name;
        // check if the record is linked to other records
        $relatedRecordsCount = related_records_count(Degree::class, $degree);

        if ($relatedRecordsCount <= 0) {
            $degree->delete();
            return response()->json([
                'status' => true, 
                'message' => "Degree \"{$name}\" deleted successfully.",
            ], 200);
        } else {
            return response()->json([
                'status' => false, 
                'message' => "The \"{$name}\" record cannot be deleted because it is associated with {$relatedRecordsCount} other record(s). You can archive it instead.",
            ], 400);
        }              
    }
}

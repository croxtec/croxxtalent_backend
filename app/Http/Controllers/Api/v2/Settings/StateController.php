<?php

namespace App\Http\Controllers\Api\v1\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\StateRequest;
use App\Models\State;

class StateController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $this->authorize('view-any', State::class);

        $per_page = $request->input('per_page', 100);
        $sort_by = $request->input('sort_by', 'sort_order');
        $sort_dir = $request->input('sort_dir', 'asc');
        $search = $request->input('search');
        $archived = $request->input('archived');
        $datatable_draw = $request->input('draw'); // if any

        $archived = $archived == 'yes' ? true : ($archived == 'no' ? false : null);

        $states = State::where( function ($query) use ($archived) {
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
            $results = $states->get();
            $states = new \Illuminate\Pagination\LengthAwarePaginator($results, $results->count(), -1);
        } else {
            $states = $states->paginate($per_page);
        }
        
        $response = collect([
            'status' => true, 
            'message' => "Successful."
        ])->merge($states)->merge(['draw' => $datatable_draw]);
        return response()->json($response, 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Models\Http\Requests\StateRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StateRequest $request)
    {
        // Authorization is declared in the Form Request

        // Retrieve the validated input data...
        $validatedData = $request->validated();
        $state = State::create($validatedData);
        if ($state) {
            return response()->json([
                'status' => true, 
                'message' => "State \"{$state->name}\" created successfully.",
                'data' => State::find($state->id)
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
        $state = State::findOrFail($id);

        $this->authorize('view', [State::class, $state]);
        
        return response()->json([
            'status' => true, 
            'message' => "Successful.",
            'data' => $state
        ], 200);        
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Models\Http\Requests\StateRequest  $request
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function update(StateRequest $request, $id)
    {
        // Authorization is declared in the StateRequest

        // Retrieve the validated input data....
        $validatedData = $request->validated();
        $state = State::findOrFail($id);
        $state->update($validatedData);
        return response()->json([
            'status' => true, 
            'message' => "State \"{$state->name}\" updated successfully.",
            'data' => State::find($state->id)
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
        $state = State::findOrFail($id);

        $this->authorize('delete', [State::class, $state]);

        $state->archived_at = now();
        $state->save();

        return response()->json([
            'status' => true, 
            'message' => "State \"{$state->name}\" archived successfully.",
            'data' => State::find($state->id)
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
        $state = State::findOrFail($id);

        $this->authorize('delete', [State::class, $state]);

        $state->archived_at = null;
        $state->save();

        return response()->json([
            'status' => true, 
            'message' => "State \"{$state->name}\" unarchived successfully.",
            'data' => State::find($state->id)
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
        $state = State::findOrFail($id);

        $this->authorize('delete', [State::class, $state]);

        $name = $state->name;
        // check if the record is linked to other records
        $relatedRecordsCount = related_records_count(State::class, $state);

        if ($relatedRecordsCount <= 0) {
            $state->delete();
            return response()->json([
                'status' => true, 
                'message' => "State \"{$name}\" deleted successfully.",
            ], 200);
        } else {
            return response()->json([
                'status' => false, 
                'message' => "The \"{$name}\" record cannot be deleted because it is associated with {$relatedRecordsCount} other record(s). You can archive it instead.",
            ], 400);
        }              
    }
}

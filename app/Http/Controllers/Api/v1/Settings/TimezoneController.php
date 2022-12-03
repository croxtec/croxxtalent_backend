<?php

namespace App\Http\Controllers\Api\v1\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\TimezoneRequest;
use App\Models\Timezone;

class TimezoneController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $this->authorize('view-any', Timezone::class);

        $per_page = $request->input('per_page', 100);
        $sort_by = $request->input('sort_by', 'sort_order');
        $sort_dir = $request->input('sort_dir', 'asc');
        $search = $request->input('search');
        $archived = $request->input('archived');
        $datatable_draw = $request->input('draw'); // if any

        $archived = $archived == 'yes' ? true : ($archived == 'no' ? false : null);

        $timezones = Timezone::where( function ($query) use ($archived) {
            if ($archived !== null ) {
                if ($archived === true ) {
                    $query->whereNotNull('archived_at');
                } else {
                    $query->whereNull('archived_at');
                }                 
            }
        })->where( function($query) use ($search) {
            $query->where('name', 'ILIKE', "%{$search }%");
        })->orderBy($sort_by, $sort_dir)
            ->paginate($per_page);
        
        $response = collect([
            'status' => true, 
            'message' => "Successful."
        ])->merge($timezones)->merge(['draw' => $datatable_draw]);
        return response()->json($response, 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Models\Http\Requests\TimezoneRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(TimezoneRequest $request)
    {
        // Authorization is declared in the Form Request

        // Retrieve the validated input data...
        $validatedData = $request->validated();
        $timezone = Timezone::create($validatedData);
        if ($timezone) {
            return response()->json([
                'status' => true, 
                'message' => "Timezone \"{$timezone->name}\" created successfully.",
                'data' => Timezone::find($timezone->id)
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
        $timezone = Timezone::findOrFail($id);

        $this->authorize('view', [Timezone::class, $timezone]);
        
        return response()->json([
            'status' => true, 
            'message' => "Successful.",
            'data' => $timezone
        ], 200);        
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Models\Http\Requests\TimezoneRequest  $request
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function update(TimezoneRequest $request, $id)
    {
        // Authorization is declared in the TimezoneRequest

        // Retrieve the validated input data....
        $validatedData = $request->validated();
        $timezone = Timezone::findOrFail($id);
        $timezone->update($validatedData);
        return response()->json([
            'status' => true, 
            'message' => "Timezone \"{$timezone->name}\" updated successfully.",
            'data' => Timezone::find($timezone->id)
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
        $timezone = Timezone::findOrFail($id);

        $this->authorize('delete', [Timezone::class, $timezone]);

        $timezone->archived_at = now();
        $timezone->save();

        return response()->json([
            'status' => true, 
            'message' => "Timezone \"{$timezone->name}\" archived successfully.",
            'data' => Timezone::find($timezone->id)
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
        $timezone = Timezone::findOrFail($id);

        $this->authorize('delete', [Timezone::class, $timezone]);

        $timezone->archived_at = null;
        $timezone->save();

        return response()->json([
            'status' => true, 
            'message' => "Timezone \"{$timezone->name}\" unarchived successfully.",
            'data' => Timezone::find($timezone->id)
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
        $timezone = Timezone::findOrFail($id);

        $this->authorize('delete', [Timezone::class, $timezone]);

        $name = $timezone->name;
        // check if the record is linked to other records
        $relatedRecordsCount = related_records_count(Timezone::class, $timezone);

        if ($relatedRecordsCount <= 0) {
            $timezone->delete();
            return response()->json([
                'status' => true, 
                'message' => "Timezone \"{$name}\" deleted successfully.",
            ], 200);
        } else {
            return response()->json([
                'status' => false, 
                'message' => "The \"{$name}\" record cannot be deleted because it is associated with {$relatedRecordsCount} other record(s). You can archive it instead.",
            ], 400);
        }              
    }
}

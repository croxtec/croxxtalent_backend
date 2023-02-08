<?php

namespace App\Http\Controllers\Api\v1\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\CurrencyRequest;
use App\Models\Currency;

class CurrencyController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $this->authorize('view-any', Currency::class);

        $per_page = $request->input('per_page', 100);
        $sort_by = $request->input('sort_by', 'sort_order');
        $sort_dir = $request->input('sort_dir', 'asc');
        $search = $request->input('search');
        $archived = $request->input('archived');
        $datatable_draw = $request->input('draw'); // if any

        $archived = $archived == 'yes' ? true : ($archived == 'no' ? false : null);

        $currencies = Currency::where( function ($query) use ($archived) {
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
        ])->merge($currencies)->merge(['draw' => $datatable_draw]);
        return response()->json($response, 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Models\Http\Requests\CurrencyRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CurrencyRequest $request)
    {
        // Authorization is declared in the Form Request

        // Retrieve the validated input data...
        $validatedData = $request->validated();
        $currency = Currency::create($validatedData);
        if ($currency) {
            return response()->json([
                'status' => true, 
                'message' => "Currency \"{$currency->name}\" created successfully.",
                'data' => Currency::find($currency->id)
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
        $currency = Currency::findOrFail($id);

        $this->authorize('view', [Currency::class, $currency]);
        
        return response()->json([
            'status' => true, 
            'message' => "Successful.",
            'data' => $currency
        ], 200);        
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Models\Http\Requests\CurrencyRequest  $request
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function update(CurrencyRequest $request, $id)
    {
        // Authorization is declared in the CurrencyRequest

        // Retrieve the validated input data....
        $validatedData = $request->validated();
        $currency = Currency::findOrFail($id);
        $currency->update($validatedData);
        return response()->json([
            'status' => true, 
            'message' => "Currency \"{$currency->name}\" updated successfully.",
            'data' => Currency::find($currency->id)
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
        $currency = Currency::findOrFail($id);

        $this->authorize('delete', [Currency::class, $currency]);

        if ($currency->is_base) {
            return response()->json([
                'status' => true, 
                'message' => "The \"{$name}\" record cannot be archived because it is currently set as the base currency.",
            ], 400);
        }

        $currency->archived_at = now();
        $currency->save();

        return response()->json([
            'status' => true, 
            'message' => "Currency \"{$currency->name}\" archived successfully.",
            'data' => Currency::find($currency->id)
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
        $currency = Currency::findOrFail($id);

        $this->authorize('delete', [Currency::class, $currency]);

        $currency->archived_at = null;
        $currency->save();

        return response()->json([
            'status' => true, 
            'message' => "Currency \"{$currency->name}\" unarchived successfully.",
            'data' => Currency::find($currency->id)
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
        $currency = Currency::findOrFail($id);

        $this->authorize('delete', [Currency::class, $currency]);

        $name = $currency->name;
        // check if the record is linked to other records
        $relatedRecordsCount = related_records_count(Currency::class, $currency);

        if ($currency->is_base) {
            return response()->json([
                'status' => true, 
                'message' => "The \"{$name}\" record cannot be deleted because it is currently set as the base currency.",
            ], 400);
        }

        if ($relatedRecordsCount <= 0) {
            $currency->delete();
            return response()->json([
                'status' => true, 
                'message' => "Currency \"{$name}\" deleted successfully.",
            ], 200);
        } else {
            return response()->json([
                'status' => false, 
                'message' => "The \"{$name}\" record cannot be deleted because it is associated with {$relatedRecordsCount} other record(s). You can archive it instead.",
            ], 400);
        }              
    }


    /**
     * Set as base currency
     *
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function setBase($id)
    {
        $currency = Currency::findOrFail($id);

        $this->authorize('update', [Currency::class, $currency]);

        // remove the current base
        $baseCurrency = Currency::where('is_base', true)
                                ->update(['is_base' => false]);

        // set new base
        $currency->update(['is_base' => true, 'rate' => 1]);

        return response()->json([
            'status' => true, 
            'message' => "\"{$currency->name}\" has been sent as base currency.",
        ], 200);  
    }
}

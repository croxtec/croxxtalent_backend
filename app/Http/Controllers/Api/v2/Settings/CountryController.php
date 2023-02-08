<?php

namespace App\Http\Controllers\Api\v1\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\CountryRequest;
use App\Models\Country;
use App\Models\State;

class CountryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $this->authorize('view-any', Country::class);

        $per_page = $request->input('per_page', 100);
        $sort_by = $request->input('sort_by', 'name');
        $sort_dir = $request->input('sort_dir', 'asc');
        $search = $request->input('search');
        $archived = $request->input('archived');
        $datatable_draw = $request->input('draw'); // if any

        $archived = $archived == 'yes' ? true : ($archived == 'no' ? false : null);
        
        $countries = Country::where( function ($query) use ($archived) {
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
            $results = $countries->get();
            $countries = new \Illuminate\Pagination\LengthAwarePaginator($results, $results->count(), -1);
        } else {
            $countries = $countries->paginate($per_page);
        }
        
        $response = collect([
            'status' => true, 
            'message' => "Successful."
        ])->merge($countries)->merge(['draw' => $datatable_draw]);
        return response()->json($response, 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Models\Http\Requests\CountryRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CountryRequest $request)
    {
        // Authorization is declared in the Form Request

        // Retrieve the validated input data...
        $validatedData = $request->validated();
        $country = Country::create($validatedData);
        if ($country) {
            return response()->json([
                'status' => true, 
                'message' => "Country \"{$country->name}\" created successfully.",
                'data' => Country::find($country->id)
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
        if (strlen($id) == 2 && !is_int($id)) {
            $country = Country::where('code', $id)->firstOrFail();
        } else {
            $country = Country::findOrFail($id);
        }

        $this->authorize('view', [Country::class, $country]);
        
        return response()->json([
            'status' => true, 
            'message' => "Successful.",
            'data' => $country
        ], 200);        
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Models\Http\Requests\CountryRequest  $request
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function update(CountryRequest $request, $id)
    {
        // Authorization is declared in the CountryRequest

        // Retrieve the validated input data....
        $validatedData = $request->validated();
        
        if (strlen($id) == 2 && !is_int($id)) {
            $country = Country::where('code', $id)->firstOrFail();
        } else {
            $country = Country::findOrFail($id);
        }

        $country->update($validatedData);
        return response()->json([
            'status' => true, 
            'message' => "Country \"{$country->name}\" updated successfully.",
            'data' => Country::find($country->id)
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
        if (strlen($id) == 2 && !is_int($id)) {
            $country = Country::where('code', $id)->firstOrFail();
        } else {
            $country = Country::findOrFail($id);
        }

        $this->authorize('delete', [Country::class, $country]);

        $country->archived_at = now();
        $country->save();

        return response()->json([
            'status' => true, 
            'message' => "Country \"{$country->name}\" archived successfully.",
            'data' => Country::find($country->id)
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
        if (strlen($id) == 2 && !is_int($id)) {
            $country = Country::where('code', $id)->firstOrFail();
        } else {
            $country = Country::findOrFail($id);
        }

        $this->authorize('delete', [Country::class, $country]);

        $country->archived_at = null;
        $country->save();

        return response()->json([
            'status' => true, 
            'message' => "Country \"{$country->name}\" unarchived successfully.",
            'data' => Country::find($country->id)
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
        if (strlen($id) == 2 && !is_int($id)) {
            $country = Country::where('code', $id)->firstOrFail();
        } else {
            $country = Country::findOrFail($id);
        }

        $this->authorize('delete', [Country::class, $country]);

        $name = $country->name;
        // check if the record is linked to other records
        $relatedRecordsCount = related_records_count(Country::class, $country);

        if ($relatedRecordsCount <= 0) {
            $country->delete();
            return response()->json([
                'status' => true, 
                'message' => "Country \"{$name}\" deleted successfully.",
            ], 200);
        } else {
            return response()->json([
                'status' => false, 
                'message' => "The \"{$name}\" record cannot be deleted because it is associated with {$relatedRecordsCount} other record(s). You can archive it instead.",
            ], 400);
        }              
    }

    /**
     * Display a listing of the country's states.
     *
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function states(Request $request, $id)
    {
        if (strlen($id) == 2 && !is_int($id)) {
            $country = Country::where('code', $id)->firstOrFail();
        } else {
            $country = Country::findOrFail($id);
        }

        $this->authorize('view-any', State::class);

        $per_page = $request->input('per_page', 100);
        $sort_by = $request->input('sort_by', 'sort_order');
        $sort_dir = $request->input('sort_dir', 'asc');
        $search = $request->input('search');
        $archived = $request->input('archived');
        $datatable_draw = $request->input('draw'); // if any

        $archived = $archived == 'yes' ? true : ($archived == 'no' ? false : null);
        
        $states = State::where('country_code', $country->code)
        ->where( function ($query) use ($archived) {
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
}

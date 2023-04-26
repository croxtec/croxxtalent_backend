<?php

namespace App\Http\Controllers\Api\v2;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Professional;

class ProfessionalController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        // $this->authorize('view-any', Professional::class);

        $per_page = $request->input('per_page', 100);
        $sort_by = $request->input('sort_by', 'name');
        $sort_dir = $request->input('sort_dir', 'asc');
        $search = $request->input('search');
        $archived = $request->input('archived');
        $datatable_draw = $request->input('draw'); // if any

        $archived = $archived == 'yes' ? true : ($archived == 'no' ? false : null);

        $professionals = Professional::where( function ($query) use ($archived) {
            if ($archived !== null ) {
                if ($archived === true ) {
                    $query->whereNotNull('archived_at');
                } else {
                    $query->whereNull('archived_at');
                }
            }
        })->when( $search , function($query) use ($search) {
            $query->where('name', 'LIKE', "%{$search}%");
        })->orderBy($sort_by, $sort_dir);

        if ($per_page === 'all' || $per_page <= 0 ) {
            $results = $professionals->get();
            $professionals = new \Illuminate\Pagination\LengthAwarePaginator($results, $results->count(), -1);
        } else {
            $professionals = $professionals->paginate($per_page);
        }

        $response = collect([
            'status' => true,
            'message' => "Successful."
        ])->merge($professionals)->merge(['draw' => $datatable_draw]);
        return response()->json($response, 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
         $rules = [
            'email' => 'required|email|exists:users,email',
            'name' => 'required|max:50',
            'domain_id' => 'required',
            'core_id' => 'required',
         ];

         $validatedData = $request->validate($rules);
         $user = User::whereEmail($validatedData['email'])->first();
         $validatedData['user_id'] = $user->id;

         $professional = Professional::create($validatedData);
         if ($professional) {
             return response()->json([
                 'status' => true,
                 'message' => "Professional \"{$professional->name}\" created successfully.",
                 'data' => Professional::find($professional->id)
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
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $professional = Professional::findOrFail($id);

        // $this->authorize('view', [Professional::class, $professional]);

        return response()->json([
            'status' => true,
            'message' => "Successful.",
            'data' => $professional
        ], 200);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $rules = [
            'email' => 'required|email',
            'name' => 'required|max:50',
            'domain_id' => 'required',
            'core_id' => 'required',
        ];

        // Retrieve the validated input data....
        $validatedData = $request->validate($rules);
        $professional = Professional::findOrFail($id);
        $professional->update($validatedData);
        return response()->json([
            'status' => true,
            'message' => "Professional \"{$professional->name}\" updated successfully.",
            'data' => Professional::find($professional->id)
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
        $professional = Professional::findOrFail($id);

        // $this->authorize('delete', [Professional::class, $professional]);

        $professional->archived_at = now();
        $professional->save();

        return response()->json([
            'status' => true,
            'message' => "professional$professional \"{$professional->name}\" archived successfully.",
            'data' => Professional::find($professional->id)
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
        $professional = Professional::findOrFail($id);

        // $this->authorize('delete', [Professional::class, $professional]);

        $professional->archived_at = null;
        $professional->save();

        return response()->json([
            'status' => true,
            'message' => "professional$professional \"{$professional->name}\" unarchived successfully.",
            'data' => Professional::find($professional->id)
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
        $professional = Professional::findOrFail($id);

        // $this->authorize('delete', [Professional::class, $professional]);

        $name = $professional->name;
        // check if the record is linked to other records
        $relatedRecordsCount = related_records_count(Professional::class, $professional);

        if ($relatedRecordsCount <= 0) {
            $professional->delete();
            return response()->json([
                'status' => true,
                'message' => "professional$professional \"{$name}\" deleted successfully.",
            ], 200);
        } else {
            return response()->json([
                'status' => false,
                'message' => "The \"{$name}\" record cannot be deleted because it is associated with {$relatedRecordsCount} other record(s). You can archive it instead.",
            ], 400);
        }
    }
}

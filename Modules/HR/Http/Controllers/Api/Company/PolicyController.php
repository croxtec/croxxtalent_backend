<?php

namespace Modules\HR\Http\Controllers\Api\Company;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\HR\Entities\Policy;
use Modules\HR\Http\Requests\PolicyRequest;

class PolicyController extends Controller
{
    public array $data = [];

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        // $this->authorize('view-any', Policy::class);

        $per_page = $request->input('per_page', 100);
        $sort_by = $request->input('sort_by', 'created_at');
        $sort_dir = $request->input('sort_dir', 'desc');
        $search = $request->input('search');
        $archived = $request->input('archived');
        $datatable_draw = $request->input('draw');

        $archived = $archived == 'yes' ? true : ($archived == 'no' ? false : null);

        $policies = Policy::where( function ($query) use ($archived) {
            if ($archived !== null ) {
                // if ($archived === true ) {
                //     $query->whereNotNull('archived_at');
                // } else {
                //     $query->whereNull('archived_at');
                // }
            }
        })
        ->where('company_id', $user->id)
        ->where( function($query) use ($search) {
            $query->where('Policy_name', 'LIKE', "%{$search}%");
        })->orderBy($sort_by, $sort_dir);

        if ($per_page === 'all' || $per_page <= 0 ) {
            $results = $policies->get();
            $policies = new \Illuminate\Pagination\LengthAwarePaginator($results, $results->count(), -1);
        } else {
            $policies = $policies->paginate($per_page);
        }

        $response = collect([
            'status' => true,
            'message' => "Successful."
        ])->merge($policies)->merge(['draw' => $datatable_draw]);

        return response()->json($response, 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PolicyRequest $request): JsonResponse
    {
        $company = $request->user();

        $validatedData = $request->validated();
        $validatedData['company_id'] = $company->id;

        $policy = Policy::create($validatedData);

        if($policy){
            return response()->json([
                'status' => true,
                'message' => "Policy created successfully.",
                'data' => Policy::find($policy->id)
            ], 201);

        } else {
            return response()->json([
                'status' => false,
                'message' => "Could not complete request.",
            ], 400);
        }
    }

    /**
     * Show the specified resource.
     */
    public function show($id): JsonResponse
    {
        $policy = Policy::findOrFail($id);

        return response()->json([
            'status' => true,
            'message' => "Successful.",
            'data' => $policy
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PolicyRequest $request, $id): JsonResponse
    {
        $validatedData = $request->validated();
        $policy = Policy::findOrFail($id);
        $policy->update($validatedData);

        return response()->json([
            'status' => true,
            'message' => "Policy updated successfully.",
            'data' => Policy::find($policy->id)
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id): JsonResponse
    {
        $policy = Policy::findOrFail($id);

        $name = $policy->Policy_name;

        $relatedRecordsCount = related_records_count(Policy::class, $policy);

        if ($relatedRecordsCount <= 0) {
            $policy->delete();
            return response()->json([
                'status' => true,
                'message' => "Policy deleted successfully.",
            ], 200);
        } else {
            return response()->json([
                'status' => false,
                'message' => "The \"{$name}\" record cannot be deleted because it is associated with {$relatedRecordsCount} other record(s). You can archive it instead.",
            ], 400);
        }
    }
}

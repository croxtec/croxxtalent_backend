<?php

namespace Modules\HR\Http\Controllers\Api\Company;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\HR\Entities\Holiday;
use Modules\HR\Http\Requests\HolidayRequest;

class HolidayController extends Controller
{
    public array $data = [];

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        // $this->authorize('view-any', Holiday::class);

        $per_page = $request->input('per_page', 100);
        $sort_by = $request->input('sort_by', 'created_at');
        $sort_dir = $request->input('sort_dir', 'desc');
        $search = $request->input('search');
        $archived = $request->input('archived');
        $datatable_draw = $request->input('draw');

        $archived = $archived == 'yes' ? true : ($archived == 'no' ? false : null);

        $holidays = Holiday::where( function ($query) use ($archived) {
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
            $query->where('holiday_name', 'LIKE', "%{$search}%");
        })->orderBy($sort_by, $sort_dir);

        if ($per_page === 'all' || $per_page <= 0 ) {
            $results = $holidays->get();
            $holidays = new \Illuminate\Pagination\LengthAwarePaginator($results, $results->count(), -1);
        } else {
            $holidays = $holidays->paginate($per_page);
        }

        $response = collect([
            'status' => true,
            'message' => "Successful."
        ])->merge($holidays)->merge(['draw' => $datatable_draw]);

        return response()->json($response, 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(HolidayRequest $request): JsonResponse
    {
        $company = $request->user();

        $validatedData = $request->validated();
        $validatedData['company_id'] = $company->id;

        $holiday = Holiday::create($validatedData);

        if($holiday){
            return response()->json([
                'status' => true,
                'message' => "Holiday created successfully.",
                'data' => Holiday::find($holiday->id)
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
        $holiday = Holiday::findOrFail($id);

        return response()->json([
            'status' => true,
            'message' => "Successful.",
            'data' => $holiday
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(HolidayRequest $request, $id): JsonResponse
    {
        $validatedData = $request->validated();
        $holiday = Holiday::findOrFail($id);
        $holiday->update($validatedData);

        return response()->json([
            'status' => true,
            'message' => "Holiday updated successfully.",
            'data' => Holiday::find($holiday->id)
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id): JsonResponse
    {
        $holiday = Holiday::findOrFail($id);

        $name = $holiday->holiday_name;

        $relatedRecordsCount = related_records_count(Holiday::class, $holiday);

        if ($relatedRecordsCount <= 0) {
            $holiday->delete();
            return response()->json([
                'status' => true,
                'message' => "holiday deleted successfully.",
            ], 200);
        } else {
            return response()->json([
                'status' => false,
                'message' => "The \"{$name}\" record cannot be deleted because it is associated with {$relatedRecordsCount} other record(s). You can archive it instead.",
            ], 400);
        }
    }
}

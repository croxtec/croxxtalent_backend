<?php

namespace Modules\HR\Http\Controllers\Api\Company;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\HR\Entities\Holiday;

class HolidayController extends Controller
{
    public array $data = [];

    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $holidays = Holiday::where('company_id', auth()->user()->company_id)
        ->orderBy('holiday_date', 'asc')
        ->get();

        // return response()->json($holidays);
        return response()->json($this->data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        //
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'holiday_date' => 'required|date',
            'type' => 'required|in:public,optional,restricted',
            'applicable_to' => 'nullable|array',
            'is_recurring' => 'nullable|boolean',
        ]);

        Holiday::create([
            'name' => $validated['name'],
            'holiday_date' => $validated['holiday_date'],
            'type' => $validated['type'],
            'company_id' => auth()->user()->company_id,
            'applicable_to' => $validated['applicable_to'] ?? null,
            'is_recurring' => $validated['is_recurring'] ?? false,
        ]);

        return response()->json($this->data);
    }

    /**
     * Show the specified resource.
     */
    public function show($id): JsonResponse
    {
        //

        return response()->json($this->data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id): JsonResponse
    {
        //

        return response()->json($this->data);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id): JsonResponse
    {
        //

        return response()->json($this->data);
    }
}

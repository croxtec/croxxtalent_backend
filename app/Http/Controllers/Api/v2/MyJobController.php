<?php

namespace App\Http\Controllers\Api\v2;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Cv;
use App\Models\AppliedJob;
use App\Models\SavedJob;
use App\Models\Notification;

class MyJobController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function indexApplied(Request $request)
    {
        $user = $request->user();

        $per_page = $request->input('per_page', 100);
        $sort_by = $request->input('sort_by', 'created_at');
        $sort_dir = $request->input('sort_dir', 'desc');
        $search = $request->input('search');
        $archived = $request->input('archived');
        $datatable_draw = $request->input('draw'); // if any

        $archived = $archived == 'yes' ? true : ($archived == 'no' ? false : null);

        $jobApplied = AppliedJob::where('talent_user_id', $user->id)
              ->where( function ($query) use ($archived) {
            if ($archived !== null ) {
                if ($archived === true ) {
                    $query->whereNotNull('archived_at');
                } else {
                    $query->whereNull('archived_at');
                }
            }
        })->orderBy($sort_by, $sort_dir);

        if ($per_page === 'all' || $per_page <= 0 ) {
            $results = $jobApplied->get();
            $jobApplied = new \Illuminate\Pagination\LengthAwarePaginator($results, $results->count(), -1);
        } else {
            $jobApplied = $jobApplied->paginate($per_page);
        }

        $response = collect([
            'status' => true,
            'message' => "Successful."
        ])->merge($jobApplied)->merge(['draw' => $datatable_draw]);
        return response()->json($response, 200);
    }

    public function unapplyJob(Request $request, $id)
    {
        $user = $request->user();

        $appliedJob = AppliedJob::where('campaign_id', $id)->where('talent_user_id', $user->id)->first();
        if ($appliedJob) {
            // Log::info($appliedJob);
            $appliedJob->delete();
            return response()->json([
                'status' => true,
                'message' => "Job application removed successfully.",
            ], 201);
        } else {
            return response()->json([
                'status' => false,
                'message' => "Could not complete request.",
            ], 400);
        }
    }

    public function indexSaved(Request $request)
    {
        $user = $request->user();

        $per_page = $request->input('per_page', 100);
        $sort_by = $request->input('sort_by', 'created_at');
        $sort_dir = $request->input('sort_dir', 'desc');
        $search = $request->input('search');
        $archived = $request->input('archived');
        $datatable_draw = $request->input('draw'); // if any

        $archived = $archived == 'yes' ? true : ($archived == 'no' ? false : null);

        $jobApplied = SavedJob::where('talent_user_id', $user->id)
              ->where( function ($query) use ($archived) {
            if ($archived !== null ) {
                if ($archived === true ) {
                    $query->whereNotNull('archived_at');
                } else {
                    $query->whereNull('archived_at');
                }
            }
        })->orderBy($sort_by, $sort_dir);

        if ($per_page === 'all' || $per_page <= 0 ) {
            $results = $jobApplied->get();
            $jobApplied = new \Illuminate\Pagination\LengthAwarePaginator($results, $results->count(), -1);
        } else {
            $jobApplied = $jobApplied->paginate($per_page);
        }

        $response = collect([
            'status' => true,
            'message' => "."
        ])->merge($jobApplied)->merge(['draw' => $datatable_draw]);
        return response()->json($response, 200);
    }

    public function destroySaved(Request $request, $id)
    {
        $user = $request->user();

        $appliedJob = AppliedJob::where('campaign_id', $id)->where('talent_user_id', $user->id)->first();
        if ($appliedJob) {
            // Log::info($appliedJob);
            $appliedJob->delete();
            return response()->json([
                'status' => true,
                'message' => "Job application removed successfully.",
            ], 201);
        } else {
            return response()->json([
                'status' => false,
                'message' => "Could not complete request.",
            ], 400);
        }
    }

}

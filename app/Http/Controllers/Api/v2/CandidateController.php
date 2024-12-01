<?php

namespace App\Http\Controllers\API\v2;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Http\Requests\JobInvitationRequest;
use App\Models\JobInvitation;
use App\Mail\TalentJobInvitation;
use Illuminate\Support\Facades\Notification;
use App\Mail\TalentJobInvitationAccepted;
use App\Mail\TalentJobInvitationRejected;
use App\Models\AppliedJob;
use App\Models\Campaign;
use App\Notifications\JobInvitationNotification;

class CandidateController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, $id)
    {
        $employer = $request->user();

        $per_page = $request->input('per_page', 25);
        $sort_by = $request->input('sort_by', 'created_at');
        $sort_dir = $request->input('sort_dir', 'desc');
        $search = $request->input('search');
        $archived = $request->input('archived');
        $rating = $request->input('rating', 0);
        $datatable_draw = $request->input('draw');

        $campaign_field = is_numeric($id) ? 'campaign_id' : 'code';
        $archived = $archived === 'yes' ? true : ($archived === 'no' ? false : null);

        // Query with relationships
        $jobApplied = AppliedJob::where($campaign_field, $id)
            ->where(function ($query) use ($archived) {
                if ($archived !== null) {
                    $archived ? $query->whereNotNull('archived_at') : $query->whereNull('archived_at');
                }
            })
            ->when($rating, function ($query) use ($rating) {
                $query->where('rating', $rating);
            })
            ->orderBy($sort_by, $sort_dir);

        if ($per_page === 'all' || $per_page <= 0) {
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



    public function rateCandidate(Request $request, $id){
        $user = $request->user();
        $applied = AppliedJob::findOrFail($id);

        $request->validate([
            'rating' => 'required|integer|between:1,2',
        ]);

        $applied->rating = $request->rating;
        $applied->save();

        return response()->json([
            'status' => true,
            'message' => "Candidate has been reviewed",
            'data' => AppliedJob::find($id)
        ], 201);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function invite(JobInvitationRequest $request)
    {
        // Authorization is declared in the Form Request
        $employer = $request->user();
        // Retrieve the validated input data...
        $validatedData = $request->validated();

        $appliedJob = AppliedJob::where('campaign_id', $validatedData['campaign_id'])
                        ->where('talent_user_id', $validatedData['talent_user_id'])
                        ->first();

        $validatedData['employer_user_id'] = $employer->id;
        $validatedData['talent_cv_id'] = $appliedJob->talent_cv_id;

        // Avoid Duplicate
        $jobInvitation = JobInvitation::firstOrCreate($validatedData);

        if ($jobInvitation) {

            if ($appliedJob) {
                $appliedJob->rating = 3;
                $appliedJob->save();
            }

            // Send Laravel notification to the talent
            Notification::send($jobInvitation->talentUser, new JobInvitationNotification($jobInvitation));

            return response()->json([
                'status' => true,
                'message' => "An invitation has been sent to {$jobInvitation->talentCv->name}.",
                'data' => JobInvitation::find($jobInvitation->id)
            ], 201);
        } else {
            return response()->json([
                'status' => false,
                'message' => "Could not complete request.",
            ], 400);
        }
    }

    //  Interview Result
    public function result(Request $request, $id){

        $request->validate([
            'score' => 'required|integer|between:1,5',
        ]);

        $jobInvitation = JobInvitation::findOrFail($id);
        $jobInvitation->score = $request->score;
        $jobInvitation->save();

        if ($jobInvitation) {
            // send email notification
            return response()->json([
                'status' => true,
                // 'message' => "An invitation has been sent to {$jobInvitation->talentCv->name}.",
                'data' => JobInvitation::find($id)
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
    public function withdraw($id)
    {
        $jobApplied = AppliedJob::findOrFail($id);
        // $this->authorize('update', [AppliedJob::class, $jobApplied]);

        $display_name = $jobApplied->talentCv->name;
        if ($jobApplied->status != 2) {
            $jobApplied->status = 2;
            $jobApplied->save();
        }

        return response()->json([
            'status' => true,
            'message' => "Job application has been withdraw successfully.",
            'data' => AppliedJob::find($jobApplied->id)
        ], 200);
    }

    public function withdrawMultiple(Request $request)
    {

    }



}

<?php

namespace App\Http\Controllers\API\v2;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Http\Requests\JobInvitationRequest;
use App\Models\JobInvitation;
use App\Mail\TalentJobInvitation;
use App\Mail\TalentJobInvitationAccepted;
use App\Mail\TalentJobInvitationRejected;
use App\Models\AppliedJob;
use App\Models\Campaign;

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

        $campaign_field = is_numeric($id) ? 'id' : 'code';
        $archived = $archived === 'yes' ? true : ($archived === 'no' ? false : null);

        $query = Campaign::with(['appliedJobs' => function ($query) use ($archived, $rating, $sort_by, $sort_dir) {
                $query->when($archived !== null, function ($query) use ($archived) {
                    if ($archived) {
                        $query->whereNotNull('archived_at');
                    } else {
                        $query->whereNull('archived_at');
                    }
                })
                ->when($rating, function ($query) use ($rating) {
                    $query->where('rating', $rating);
                })
                ->orderBy($sort_by, $sort_dir)
                ->with('talentUser');
            }])
            ->where($campaign_field, $id);

        if ($per_page === 'all' || $per_page <= 0) {
            $campaigns = $query->get();
            $campaignsPaginated = new \Illuminate\Pagination\LengthAwarePaginator($campaigns, $campaigns->count(), -1);
        } else {
            $campaignsPaginated = $query->paginate($per_page);
        }

        $response = collect([
            'status' => true,
            'message' => "Successful."
        ])->merge($campaignsPaginated)->merge(['draw' => $datatable_draw]);

        return response()->json($response, 200);
    }



    public function rateCandidate(Request $request, $id){
        $user = $request->user();
        $applied = AppliedJob::findOrFail($id);

        $request->validate([
            'rating' => 'required|integer|between:1,3',
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

        // Retrieve the validated input data...
        $validatedData = $request->validated();
        // unset($validatedData['interview_at']);
        // Avoid Dublicate

        $jobInvitation = JobInvitation::firstOrCreate($validatedData);
        if ($jobInvitation) {
            // send email notification
            if ($jobInvitation->talentCv->email) {
                if (config('mail.queue_send')) {
                    Mail::to($jobInvitation->talentCv->email)->queue(new TalentJobInvitation($jobInvitation));
                } else {
                    Mail::to($jobInvitation->talentCv->email)->send(new TalentJobInvitation($jobInvitation));
                }
            }
            // Send Push notification
            $display_name = $jobInvitation->employerUser->display_name;
            $notification = new Notification();
            $notification->user_id = $request->talent_user_id;
            $notification->action = '/my-job';
            $notification->title = 'Job Invitation';
            $notification->message = "You have a new job invitation/offer from <b>$display_name</b>.";
            $notification->save();
            event(new NewNotification($notification->user_id,$notification));
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

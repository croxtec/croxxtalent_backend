<?php

namespace App\Http\Controllers\API\v2;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\JobInvitation;
use App\Models\AppliedJob;

class CandidateController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, $id)
    {
        $user = $request->user();

        $per_page = $request->input('per_page', 100);
        $sort_by = $request->input('sort_by', 'created_at');
        $sort_dir = $request->input('sort_dir', 'desc');
        $search = $request->input('search');
        $archived = $request->input('archived');
        $rate = $request->input('rate');
        $datatable_draw = $request->input('draw'); // if any

        $archived = $archived == 'yes' ? true : ($archived == 'no' ? false : null);

        $jobApplied = AppliedJob::where('campaign_id', $id)
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

    public function rateCV(Request $request){
        $user = $request->user();
        $applied = AppliedJob::findOrFail($id);

        $validator = Validator::make($request->all(),[
            'campaign_id' => 'required',
            'rating' => 'required',
            // 'talent_cv_id' => 'required',
        ]);


        $applied->update($request->all());

        return response()->json([
            'status' => true,
            'message' => "Your Job Application has been submitted.",
            'data' => AppliedJob::find($appliedJob->id)
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
         // Set interview time
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
            // $display_name = $jobInvitation->employerUser->display_name;
            // $notification = new Notification();
            // $notification->user_id = $request->talent_user_id;
            // $notification->action = '/my-job';
            // $notification->title = 'Job Invitation';
            // $notification->message = "You have a new job invitation/offer from <b>$display_name</b>.";
            // $notification->save();
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
    public function result(Request $request){

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

        $this->authorize('update', [AppliedJob::class, $jobApplied]);

        $display_name = $jobApplied->talentCv->name;
        if ($jobApplied->status != 'withdraw') {
            $jobApplied->status = 'withdraw';
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

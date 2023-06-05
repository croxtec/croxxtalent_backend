<?php

namespace App\Http\Controllers\Api\v2;

use App\Events\NewNotification;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Http\Requests\JobInvitationRequest;
use App\Models\JobInvitation;
use App\Mail\TalentJobInvitation;
use App\Mail\TalentJobInvitationAccepted;
use App\Mail\TalentJobInvitationRejected;
use App\Models\Notification;

class JobInvitationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $this->authorize('view-any', JobInvitation::class);

        $per_page = $request->input('per_page', 100);
        $sort_by = $request->input('sort_by', 'created_at');
        $sort_dir = $request->input('sort_dir', 'desc');
        $search = $request->input('search');
        $archived = $request->input('archived');
        $datatable_draw = $request->input('draw'); // if any

        $archived = $archived == 'yes' ? true : ($archived == 'no' ? false : null);

        $jobInvitations = JobInvitation::where( function ($query) use ($archived) {
            if ($archived !== null ) {
                if ($archived === true ) {
                    $query->whereNotNull('archived_at');
                } else {
                    $query->whereNull('archived_at');
                }
            }
        })->orderBy($sort_by, $sort_dir);

        if ($per_page === 'all' || $per_page <= 0 ) {
            $results = $jobInvitations->get();
            $jobInvitations = new \Illuminate\Pagination\LengthAwarePaginator($results, $results->count(), -1);
        } else {
            $jobInvitations = $jobInvitations->paginate($per_page);
        }

        $response = collect([
            'status' => true,
            'message' => "Successful."
        ])->merge($jobInvitations)->merge(['draw' => $datatable_draw]);
        return response()->json($response, 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Models\Http\Requests\JobInvitationRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(JobInvitationRequest $request)
    {
        // Authorization is declared in the Form Request

        // Retrieve the validated input data...
        $validatedData = $request->validated();
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

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Http\Requests\JobInvitationRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function check(JobInvitationRequest $request)
    {
        // Authorization is declared in the Form Request

        // Retrieve the validated input data...
        $validatedData = $request->validated();
        $jobInvitation = JobInvitation::where('employer_user_id', $validatedData['employer_user_id'])
                                        ->where('talent_user_id', $validatedData['talent_user_id'])
                                        ->where('talent_cv_id', $validatedData['talent_cv_id'])
                                        ->firstOrFail();

        return response()->json([
            'status' => true,
            'message' => "Successful.",
            'data' => $jobInvitation
        ], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $jobInvitation = JobInvitation::findOrFail($id);

        $this->authorize('view', [JobInvitation::class, $jobInvitation]);

        return response()->json([
            'status' => true,
            'message' => "Successful.",
            'data' => $jobInvitation
        ], 200);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Models\Http\Requests\JobInvitationRequest  $request
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function update(JobInvitationRequest $request, $id)
    {
        // Authorization is declared in the JobInvitationRequest

        // Retrieve the validated input data....
        $validatedData = $request->validated();
        $jobInvitation = JobInvitation::findOrFail($id);

        $skill_ids = $validatedData['skill_ids'];
        $course_of_study_ids = $validatedData['course_of_study_ids'];
        $language_ids = $validatedData['language_ids'];
        unset($validatedData['skill_ids'], $validatedData['course_of_study_ids'], $validatedData['language_ids']);

        $jobInvitation->update($validatedData);

        // Update records to pivot table
        $jobInvitation->skills()->sync($skill_ids);
        $jobInvitation->courseOfStudies()->sync($course_of_study_ids);
        $jobInvitation->languages()->sync($language_ids);

        return response()->json([
            'status' => true,
            'message' => "Job invitation updated successfully.",
            'data' => JobInvitation::find($jobInvitation->id)
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
        $jobInvitation = JobInvitation::findOrFail($id);

        $this->authorize('delete', [JobInvitation::class, $jobInvitation]);

        $jobInvitation->archived_at = now();
        $jobInvitation->save();

        return response()->json([
            'status' => true,
            'message' => "Job invitation archived successfully.",
            'data' => JobInvitation::find($jobInvitation->id)
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
        $jobInvitation = JobInvitation::findOrFail($id);

        $this->authorize('delete', [JobInvitation::class, $jobInvitation]);

        $jobInvitation->archived_at = null;
        $jobInvitation->save();

        return response()->json([
            'status' => true,
            'message' => "Job invitation unarchived successfully.",
            'data' => JobInvitation::find($jobInvitation->id)
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
        $jobInvitation = JobInvitation::findOrFail($id);

        $this->authorize('delete', [JobInvitation::class, $jobInvitation]);

        $name = $jobInvitation->name;
        // check if the record is linked to other records
        $relatedRecordsCount = related_records_count(JobInvitation::class, $jobInvitation);

        if ($relatedRecordsCount <= 0) {
            $jobInvitation->delete();
            return response()->json([
                'status' => true,
                'message' => "Job invitation deleted successfully.",
            ], 200);
        } else {
            return response()->json([
                'status' => false,
                'message' => "The \"{$name}\" record cannot be deleted because it is associated with {$relatedRecordsCount} other record(s). You can archive it instead.",
            ], 400);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function destroyMultiple(Request $request)
    {
        $ids = $request->input('ids');
        $valid_ids = [];
        $deleted_count = 0;
        if (is_array($ids)) {
            foreach ($ids as $id) {
                $jobInvitation = JobInvitation::find($id);
                if ($jobInvitation) {
                    $this->authorize('delete', [JobInvitation::class, $jobInvitation]);
                    $valid_ids[] = $jobInvitation->id;
                }
            }
        }
        $valid_ids = collect($valid_ids);
        if ($valid_ids->isNotEmpty()) {
            foreach ($valid_ids as $id) {
                $jobInvitation = JobInvitation::find($id);
                // check if the record is linked to other records
                $relatedRecordsCount = related_records_count(JobInvitation::class, $jobInvitation);
                if ($relatedRecordsCount <= 0) {
                    $jobInvitation->delete();
                    $deleted_count++;
                }
            }
        }

        return response()->json([
            'status' => true,
            'message' => "{$deleted_count} job invitations deleted successfully.",
        ], 200);
    }

    /**
     * Accept a job invitation
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function accept($id)
    {
        $jobInvitation = JobInvitation::findOrFail($id);

        $this->authorize('update', [JobInvitation::class, $jobInvitation]);

        $display_name = $jobInvitation->talentCv->name;
        if ($jobInvitation->status != 'accepted') {
            $jobInvitation->status = 'accepted';
            $jobInvitation->save();

            //Send push notifications
            $notification = new Notification();
            $notification->user_id = $jobInvitation->talent_user_id;
            $notification->action = '/my-job';
            $notification->category = 'success';
            $notification->title = 'Job Invitation Accepted';
            $notification->message = "Your job invitation/offer was accepted by $display_name ";
            $notification->save();
            // send email notification
            if ($jobInvitation->employerUser->email) {
                if (config('mail.queue_send')) {
                    Mail::to($jobInvitation->employerUser->email)->queue(new TalentJobInvitationAccepted($jobInvitation));
                } else {
                    Mail::to($jobInvitation->employerUser->email)->send(new TalentJobInvitationAccepted($jobInvitation));
                }
            }
        }

        return response()->json([
            'status' => true,
            'message' => "Job invitation accepted successfully.",
            'data' => JobInvitation::find($jobInvitation->id)
        ], 200);
    }

    /**
     * Reject a job invitation
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function reject($id)
    {
        $jobInvitation = JobInvitation::findOrFail($id);

        $this->authorize('update', [JobInvitation::class, $jobInvitation]);

        $display_name = $jobInvitation->talentCv->name;
        if ($jobInvitation->status != 'rejected') {
            $jobInvitation->status = 'rejected';
            $jobInvitation->save();

            // send push notification
            // $notification = new Notification();
            // $notification->user_id = $jobInvitation->talent_user_id;
            // $notification->action = '/my-job';
            // $notification->category = 'danger';
            // $notification->title = 'Job Invitation Rejected';
            // $notification->message = "Your job invitation/offer was rejected by $display_name ";
            // $notification->save();
            // send email notification
            if ($jobInvitation->employerUser->email) {
                if (config('mail.queue_send')) {
                    Mail::to($jobInvitation->employerUser->email)->queue(new TalentJobInvitationRejected($jobInvitation));
                } else {
                    Mail::to($jobInvitation->employerUser->email)->send(new TalentJobInvitationRejected($jobInvitation));
                }
            }
        }

        return response()->json([
            'status' => true,
            'message' => "Job invitation rejected successfully.",
            'data' => JobInvitation::find($jobInvitation->id)
        ], 200);
    }
}

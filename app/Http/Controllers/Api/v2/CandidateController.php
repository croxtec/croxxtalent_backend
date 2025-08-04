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
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Response;

class CandidateController extends Controller
{

    // use ApiResponseTrait;

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
        $jobApplied = AppliedJob::with([
                'talentUser', 'cvUpload',
                'talentInvitation'
            ])
            ->where($campaign_field, $id)
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
            'message' => ""
        ])->merge($jobApplied)->merge(['draw' => $datatable_draw]);

        return response()->json($response, 200);
    }

    public function rateCandidate(Request $request, $id)
    {
        $request->validate([
            'rating' => 'required|integer|between:1,2',
        ]);

        $applied = AppliedJob::findOrFail($id);
        $applied->rating = $request->rating;
        $applied->save();

        return $this->successResponse(
            AppliedJob::find($id),
            'services.candidates.rated',
            [],
            Response::HTTP_CREATED
        );
    }

    public function invite(JobInvitationRequest $request)
    {
        try {
            $employer = $request->user();
            $validatedData = $request->validated();

            $appliedJob = AppliedJob::where('campaign_id', $validatedData['campaign_id'])
                ->where('talent_user_id', $validatedData['talent_user_id'])
                ->first();

            $validatedData['employer_user_id'] = $employer->id;
            $validatedData['talent_cv_id'] = $appliedJob->talent_cv_id;

            $jobInvitation = JobInvitation::firstOrCreate($validatedData);

            if ($appliedJob) {
                $appliedJob->rating = 3;
                $appliedJob->save();
            }

            Notification::send($jobInvitation->talentUser, new JobInvitationNotification($jobInvitation));

            return $this->successResponse(
                JobInvitation::find($jobInvitation->id),
                'services.candidates.invited',
                ['name' => $jobInvitation->talentCv->name],
                Response::HTTP_CREATED
            );

        } catch (\Exception $e) {
            return $this->errorResponse(
                'services.candidates.invite_error',
                [],
                Response::HTTP_BAD_REQUEST
            );
        }
    }

    public function result(Request $request, $id)
    {
        $request->validate([
            'score' => 'required|integer|between:1,5',
        ]);

        $jobInvitation = JobInvitation::findOrFail($id);
        $jobInvitation->score = $request->score;
        $jobInvitation->save();

        return $this->successResponse(
            JobInvitation::find($id),
            'services.candidates.scored',
            [],
            Response::HTTP_CREATED
        );
    }

    public function withdraw($id)
    {
        $jobApplied = AppliedJob::findOrFail($id);
        
        if ($jobApplied->status != 2) {
            $jobApplied->status = 2;
            $jobApplied->save();
        }

        return $this->successResponse(
            AppliedJob::find($jobApplied->id),
            'services.candidates.withdrawn'
        );
    }

    public function withdrawMultiple(Request $request)
    {

    }



}

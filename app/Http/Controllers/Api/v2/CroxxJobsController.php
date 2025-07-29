<?php

namespace App\Http\Controllers\Api\v2;

// use App\Events\NewNotification;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Campaign;
use App\Models\Cv;
use App\Models\AppliedJob;
use App\Models\SavedJob;
use App\Models\JobInvitation;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class CroxxJobsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // $user = optional($request->user());
        $user = Auth::guard('api')->user();
        // $this->authorize('view-any', Campaign::class);
        $per_page = $request->input('per_page', 100);
        $sort_by = $request->input('sort_by', 'created_at');
        $sort_dir = $request->input('sort_dir', 'desc');
        $search = $request->input('search');
        $country = $request->input('country');
        $industry = $request->input('industry');
        $experience = $request->input('experience');
        $employers = $request->input('employers');
        $employment_types = $request->input('employment_types');
        $qualifications = $request->input('qualifications');
        $languages = $request->input('languages');
        $date_filter = $request->input('date_filter');
        $datatable_draw = $request->input('draw'); // if any

        $campaigns = Campaign::where(function ($query) use ($search) {
            $query->where('title', 'LIKE', "%{$search}%")
                ->orWhere('job_title', 'LIKE', "%{$search}%");
        })
        ->when($employment_types, function ($query) use ($employment_types) {
            $query->whereIn('work_type', $employment_types);
        })
        ->when($industry, function ($query) use ($industry) {
            $query->whereIn('industry_id', $industry);
        })
        ->when($employers, function ($query) use ($employers) {
            $query->whereIn('user_id', $employers);
        })
        ->when($qualifications, function ($query) use ($qualifications) {
            $query->whereIn('minimum_degree_id', $qualifications);
        })
        ->when($date_filter, function ($query) use ($date_filter) {
            switch ($date_filter) {
                case 'past_24_hours':
                    $query->where('created_at', '>=', Carbon::now()->subDay());
                    break;
                case 'past_week':
                    $query->where('created_at', '>=', Carbon::now()->subWeek());
                    break;
                case 'past_month':
                    $query->where('created_at', '>=', Carbon::now()->subMonth());
                    break;
            }
        })
        ->when($languages, function ($query) use ($languages) {
            $query->whereHas('languages', function ($q) use ($languages) {
                $q->whereIn('language_id', $languages);
            });
        })
        ->where('is_published', 1)
        ->orderBy($sort_by, $sort_dir);

        if ($per_page === 'all' || $per_page <= 0) {
            $results = $campaigns->get();
            $campaigns = new \Illuminate\Pagination\LengthAwarePaginator($results, $results->count(), -1);
        } else {
            $campaigns = $campaigns->paginate($per_page);
        }

        if ($user) {
            $appliedJobs = AppliedJob::where('talent_user_id', $user->id)->pluck('campaign_id')->toArray();
            $savedJobs = SavedJob::where('talent_user_id', $user->id)->pluck('campaign_id')->toArray();
        }

        foreach ($campaigns as $job) {
            if ($user) {
                $job->is_applied = in_array($job->id, $appliedJobs);
                $job->is_saved = in_array($job->id, $savedJobs);
            }
        }

        $response = collect([
            'status' => true,
            'message' => $user
        ])->merge($campaigns)->merge(['draw' => $datatable_draw]);

        return response()->json($response, 200);

    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        $user = Auth::guard('api')->user();

        if (is_numeric($id)) {
            $job = Campaign::whereId($id)->where('is_published', 1)->firstOrFail();
        } else {
            $job = Campaign::where('code', $id)->where('is_published', 1)->firstOrFail();
        }

        if ($user) {
            $appliedJobs = AppliedJob::where('talent_user_id', $user->id)->pluck('campaign_id')->toArray();
            $savedJobs = SavedJob::where('talent_user_id', $user->id)->pluck('campaign_id')->toArray();
            $job->is_applied = in_array($job->id, $appliedJobs);
            $job->is_saved = in_array($job->id, $savedJobs);
        }
        // if ($user) {
        //     $job->is_applied = $job->appliedJobs()->where('talent_user_id', $user->id)->exists();
        //     $job->is_saved = $job->savedJobs()->where('talent_user_id', $user->id)->exists();
        // }


        return response()->json([
            'status' => true,
            'message' => $user,
            'data' => $job
        ], 200);
    }

    public function recommendations(Request $request)
    {
        $user = $request->user();
        // $this->authorize('view-any', Campaign::class);
        $cv = CV::where('user_id', $user->id)->first();

        $per_page = $request->input('per_page', 9);
        $sort_by = $request->input('sort_by', 'created_at');
        $sort_dir = $request->input('sort_dir', 'desc');
        $datatable_draw = $request->input('draw'); // if any

        $search = $cv->job_title ?? null;
        $industry = $cv->industry_id ?? null;

        $campaigns = Campaign::where( function($query) use ($search) {
            $query->where('title', 'LIKE', "%{$search}%");
        })->when($industry, function($query) use($industry){
            $query->where('industry_id', $industry);
        })
        ->where('is_published', 1)
        ->orderBy($sort_by, $sort_dir);

        if ($per_page === 'all' || $per_page <= 0 ) {
            $results = $campaigns->get();
            $campaigns = new \Illuminate\Pagination\LengthAwarePaginator($results, $results->count(), -1);
        } else {
            $campaigns = $campaigns->paginate($per_page);
        }

        // info(count($campaigns));
        $response = collect([
            'status' => true,
            'message' => ""
        ])
        ->merge($campaigns)->merge(['draw' => $datatable_draw]);

        return response()->json($response, 200);
    }

    public function dashboard(Request $request){
        $user = $request->user();

        $total_applied = AppliedJob::where('talent_user_id', $user->id)->count();
        $total_saved = SavedJob::where('talent_user_id', $user->id)->count();
        $total_invited = JobInvitation::where('talent_user_id', $user->id)->count();

        $data  = compact('total_applied', 'total_saved', 'total_invited');

        return response()->json([
            'status' => true,
            'data' => $data,
            'message' => ''
        ], 200);
    }

    /**
     * Apply for a new Job Campaign.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
   public function apply(Request $request)
    {
        $user = $request->user();
        $cv = CV::where('user_id', $user->id)->firstorFail();

        $validator = Validator::make($request->all(),[
            'campaign_id' => 'required',
        ]);

        if($validator->fails()){
            return $this->validationErrorResponse($validator->errors());
        }

        $request['talent_user_id'] = $user->id;
        $request['talent_cv_id'] = $cv->id;
        $request['rating'] = 0;

        try {
            $campaign = Campaign::where('id',$request->campaign_id)
                        ->where('is_published', 1)->firstOrFail();
            
            Log::info("Applying for campaign: {$campaign->title} by user: {$user->email}");
            Log::info("Request data: ", $request->all());

            $appliedJob = AppliedJob::firstOrCreate($request->all());

            // $notification = new Notification();
            // $notification->id = Str::uuid();
            // $notification->type = 'CampaignApplication';
            // $notification->notifiable_id = $campaign->user_id;
            // $notification->notifiable_type = 'App\Models\User';
            // $notification->data = json_encode([
            //     'action' => "/campaign/applications/{$request->campaign_id}",
            //     'title' => __('talent.notifications.campaign_application_title'),
            //     'message' => __('talent.notifications.campaign_application_message', [
            //         'title' => $campaign->title
            //     ])
            // ]);
            // $notification->category = 'primary';
            // $notification->save();

            return $this->successResponse(
                AppliedJob::find($appliedJob->id),
                'talent.application.submitted'
            );

        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('talent.campaign.not_found');
        } catch (\Exception $e) {
            return $this->errorResponse(
                'talent.application.error',
                ['error' => $e->getMessage()]
            );
        }
    }

    public function saved(Request $request)
    {
        $user = $request->user();
        $cv = CV::where('user_id', $user->id)->firstorFail();

        $validator = Validator::make($request->all(),[
            'campaign_id' => 'required',
        ]);

        if($validator->fails()){
            return $this->validationErrorResponse($validator->errors());
        }

        try {
            $campaign = Campaign::where('id',$request->campaign_id)
                ->where('is_published', 1)->firstOrFail();

            $request['talent_user_id'] = $user->id;
            $request['talent_cv_id'] = $cv->id;

            $saved = SavedJob::firstOrCreate($request->all());

            return $this->successResponse(
                SavedJob::find($saved->id),
                'talent.saved.success'
            );

        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('talent.campaign.not_found');
        } catch (\Exception $e) {
            return $this->errorResponse(
                'talent.saved.error',
                ['error' => $e->getMessage()]
            );
        }
    }

    public function topEmployers(Request $request)
    {
        $sort_by = $request->input('sort_by', 'created_at');
        $type = $request->input('type', 'employer');
        $active = $request->input('active');
        $active = $active == 'yes' ? true : ($active == 'no' ? false : null);

        $employers = User::where( function ($query) use ($type, $active) {
            if ($type) {
                $query->where('type', $type);
            }
            if ($active !== null ) {
                $query->where('is_active', $active);
            }
        })
        ->orderBy($sort_by)->limit(10)->get();

        foreach($employers as $employer){
            $employer->vacancy = Campaign::whereId($employer->id)->count();
        }

        $response = collect([
            'status' => true,
            'data' => $employers,
            'message' => "Successful."
        ]);
        return response()->json($response, 200);
    }


}

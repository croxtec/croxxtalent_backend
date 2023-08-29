<?php

namespace App\Http\Controllers\Api\v2;

use App\Events\NewNotification;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Campaign;
use App\Models\Cv;
use App\Models\AppliedJob;
use App\Models\SavedJob;
use App\Models\Notification;
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
        $user = $request->user();
        // $this->authorize('view-any', Campaign::class);

        // info($request->all());

        $per_page = $request->input('per_page', 100);
        $sort_by = $request->input('sort_by', 'created_at');
        $sort_dir = $request->input('sort_dir', 'desc');
        $search = $request->input('search');
        $industry = $request->input('industry');
        $country = $request->input('country');
        $qualifications = $request->input('qualifications');
        $experience = $request->input('experience');
        $employer = $request->input('employer');
        $employment_type = $request->input('employment_type');
        $salary_currency = $request->input('salary_currency');
        $salary_salary = $request->input('salary_salary');
        $salary_end = $request->input('salary_end');
        $date_start = $request->input('date_start');
        $date_end = $request->input('date_end');
        $languages = $request->input('languages');

        $datatable_draw = $request->input('draw'); // if any


        $campaigns = Campaign::where('is_published', 1)
        ->where( function($query) use ($search) {
            $query->where('title', 'LIKE', "%{$search}%");
        })
        // Filters
        // ->when
        ->orderBy($sort_by, $sort_dir);

        if ($per_page === 'all' || $per_page <= 0 ) {
            $results = $campaigns->get();
            $campaigns = new \Illuminate\Pagination\LengthAwarePaginator($results, $results->count(), -1);
        } else {
            $campaigns = $campaigns->paginate($per_page);
        }

        // foreach($campaigns as $campaign){
        //     $campaign->applied = AppliedJob::where('campaign_id', $campaign->id)->where('talent_user_id', $user->id)->first();
        // }


        $response = collect([
            'status' => true,
            'data' => $campaigns,
            'message' => "Successful."
        ]);
        // ->merge($campaigns)->merge(['draw' => $datatable_draw]);
        return response()->json($response, 200);
    }


      /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $campaign = Campaign::findOrFail($id);
        $campaign->applications;
        foreach ($campaign->applications as $application) {
            $application->cv = Cv::find($application->talent_cv_id);
        }

        return response()->json([
            'status' => true,
            'message' => "Successful.",
            'data' => $campaign
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
            // 'talent_user_id' => 'required',
            // 'talent_cv_id' => 'required',
        ]);

        $request['talent_user_id'] = $user->id;
        $request['talent_cv_id'] = $cv->id;
        $request['rating'] = 0;

        info($request->all());
        $appliedJob = AppliedJob::firstOrCreate($request->all());

        if ($appliedJob) {
            // $campaign = Campaign::find($request->campaign_id);
            // $notification = new Notification();
            // $notification->user_id = $campaign->user_id;
            // $notification->action = "/campaign/applications/$request->campaign_id";
            // $notification->title = 'Campaign Application';
            // $notification->message = "A talent has just applied for $campaign->title campaingn.";
            // $notification->save();
            // event(new NewNotification($notification->user_id,$notification));
            return response()->json([
                'status' => true,
                'message' => "Your Job Application has been submitted.",
                'data' => AppliedJob::find($appliedJob->id)
            ], 201);
        } else {
            return response()->json([
                'status' => false,
                'message' => "Could not complete request.",
            ], 400);
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
            $status = false;
            $message = $validator->errors()->toJson();
            return response()->json(compact('status', 'message') , 400);
        }

        $request['talent_user_id'] = $user->id;
        $request['talent_cv_id'] = $cv->id;

        $saved = SavedJob::firstOrCreate($request->all());

        if ($saved) {
            return response()->json([
                'status' => true,
                'message' => "Campaign has been saved successfuly.",
                'data' => SavedJob::find($saved->id)
            ], 201);
        } else {
            return response()->json([
                'status' => false,
                'message' => "Could not complete request.",
            ], 400);
        }
    }

    public function recommendations(Request $request)
    {
        $user = $request->user();
        // $this->authorize('view-any', Campaign::class);


        $per_page = $request->input('per_page', 100);
        $sort_by = $request->input('sort_by', 'created_at');
        $sort_dir = $request->input('sort_dir', 'desc');
        $search = $request->input('search');
        $industry = $request->input('industry');
        $country = $request->input('country');
        $qualifications = $request->input('qualifications');
        $experience = $request->input('experience');
        $employer = $request->input('employer');
        $employment_type = $request->input('employment_type');
        $salary_currency = $request->input('salary_currency');
        $salary_salary = $request->input('salary_salary');
        $salary_end = $request->input('salary_end');
        $date_start = $request->input('date_start');
        $date_end = $request->input('date_end');
        $languages = $request->input('languages');

        $datatable_draw = $request->input('draw'); // if any


        $campaigns = Campaign::where('is_published', 1)
        ->where( function($query) use ($search) {
            $query->where('title', 'LIKE', "%{$search}%");
        }) // ->when
        ->orderBy($sort_by, $sort_dir);

        $results = $campaigns->limit(18)->get();
        $campaigns = new \Illuminate\Pagination\LengthAwarePaginator($results, $results->count(), -1);


        $response = collect([
            'status' => true,
            'data' => $campaigns,
            'message' => "Successful."
        ]);
        // ->merge($campaigns)->merge(['draw' => $datatable_draw]);
        return response()->json($response, 200);
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

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
        $experience = $request->input('experience');
        $employers = $request->input('employers');
        $employment_types = $request->input('employment_types');
        // $country = $request->input('country');
        // $salary_currency = $request->input('salary_currency');
        // $salary_salary = $request->input('salary_salary');
        // $salary_end = $request->input('salary_end');
        $qualifications = $request->input('qualifications');
        $languages = $request->input('languages');
        $date_filter = $request->input('date_filter');

        $datatable_draw = $request->input('draw'); // if any


        $campaigns = Campaign::where( function($query) use ($search) {
            $query->where('title', 'LIKE', "%{$search}%")
                   ->orWhere('job_title', 'LIKE', "%{$search}%");
        })
        ->when($employment_types, function($query) use($employment_types){
            $query->whereIn('work_type', $employment_types);
        })
        ->when($industry, function($query) use($industry){
            $query->whereIn('industry_id', $industry);
        })
        ->when($employers, function($query) use($employers){
            $query->whereIn('user_id', $employers);
        })
        ->when($qualifications, function($query) use($qualifications){
            $query->whereIn('minimum_degree_id', $qualifications);
        })
        ->when($date_filter, function($query) use ($date_filter) {
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
        ->when($languages, function($query) use ($languages) {
            $query->whereHas('languages', function($q) use ($languages) {
                $q->whereIn('language_id', $languages);
            });
        })
        ->where('is_published', 1)
        // ->whereNull('archived_at')
        ->orderBy($sort_by, $sort_dir);

        if ($per_page === 'all' || $per_page <= 0 ) {
            $results = $campaigns->get();
            $campaigns = new \Illuminate\Pagination\LengthAwarePaginator($results, $results->count(), -1);
        } else {
            $campaigns = $campaigns->paginate($per_page);
        }

        $response = collect([
            'status' => true,
            'message' => "Successful."
        ]) ->merge($campaigns)->merge(['draw' => $datatable_draw]);
        return response()->json($response, 200);
    }


      /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        if (is_numeric($id)) {
             $campaign = Campaign::whereId($id)->where('is_published', 1)->firstOrFail();
        }else{
            $campaign = Campaign::where('code', $id)->where('is_published', 1)->firstOrFail();
        }


        // foreach ($campaign->applications as $application) {
        //     $application->cv = Cv::find($application->talent_cv_id);
        // }

        return response()->json([
            'status' => true,
            'message' => "Successful.",
            'data' => $campaign
        ], 200);
    }


    public function recommendations(Request $request)
    {
        $user = $request->user();
        // $this->authorize('view-any', Campaign::class);
        $cv = CV::where('user_id', $user->id)->firstorFail();


        $per_page = $request->input('per_page', 9);
        $sort_by = $request->input('sort_by', 'created_at');
        $sort_dir = $request->input('sort_dir', 'desc');
        $datatable_draw = $request->input('draw'); // if any

        $search = $cv->job_title;
        $industry = $cv->industry_id;


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

        info(count($campaigns));
        $response = collect([
            'status' => true,
            'message' => "Successful."
        ])
        ->merge($campaigns)->merge(['draw' => $datatable_draw]);

        return response()->json($response, 200);
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
            $status = false;
            $message = $validator->errors()->toJson();
            return response()->json(compact('status', 'message') , 400);
        }

        $request['talent_user_id'] = $user->id;
        $request['talent_cv_id'] = $cv->id;
        $request['rating'] = 0;

        $campaign = Campaign::where('id',$request->campaign_id)
                     ->where('is_published', 1)->firstOrFail();

        $appliedJob = AppliedJob::firstOrCreate($request->all());

        if ($appliedJob) {
            $notification = new Notification();
            $notification->user_id = $campaign->user_id;
            $notification->action = "/campaign/applications/$request->campaign_id";
            $notification->title = 'Campaign Application';
            $notification->message = "A talent has just applied for $campaign->title campaingn.";
            $notification->save();
            event(new NewNotification($notification->user_id,$notification));
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

        $campaign = Campaign::where('id',$request->campaign_id)
        ->where('is_published', 1)->firstOrFail();

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

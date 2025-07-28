<?php

namespace App\Http\Controllers\Api\v2;

use App\Events\NewNotification;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;
use App\Http\Requests\CampaignRequest;
use App\Models\Campaign;
use App\Mail\CampaignPublished;
use App\Models\Cv;
use App\Models\Notification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class CampaignController extends Controller
{
    use ApiResponseTrait;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $this->authorize('view-any', Campaign::class);

        $per_page = $request->input('per_page', 100);
        $sort_by = $request->input('sort_by', 'created_at');
        $sort_dir = $request->input('sort_dir', 'desc');
        $search = $request->input('search');
        $archived = $request->input('archived');
        $datatable_draw = $request->input('draw'); // if any

        $archived = $archived == 'yes' ? true : ($archived == 'no' ? false : null);

        $campaigns = Campaign::where( function ($query) use ($archived) {
            if ($archived !== null ) {
                if ($archived === true ) {
                    $query->whereNotNull('archived_at');
                } else {
                    $query->whereNull('archived_at');
                }
            }
        })
        ->where('user_id', $user->id)
        ->when($request->start_date && $request->end_date, function ($query) use ($request) {
            $startDate = Carbon::parse($request->start_date);
            $endDate = Carbon::parse($request->end_date);
            $query->whereBetween('created_at', [$startDate, $endDate]);
        })
        ->where( function($query) use ($search) {
            $query->where('title', 'LIKE', "%{$search}%");
        })->orderBy($sort_by, $sort_dir);

        if ($per_page === 'all' || $per_page <= 0 ) {
            $results = $campaigns->get();
            $campaigns = new \Illuminate\Pagination\LengthAwarePaginator($results, $results->count(), -1);
        } else {
            $campaigns = $campaigns->paginate($per_page);
        }

        $response = collect([
            'status' => true,
            'message' => "Successful."
        ])->merge($campaigns)->merge(['draw' => $datatable_draw]);
        return response()->json($response, 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Models\Http\Requests\CampaignRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CampaignRequest $request)
    {
        $user = $request->user();
        $validatedData = $request->validated();
        
        try {
            DB::beginTransaction();
            
            $validatedData['user_id'] = $user->id;
            $validatedData['code'] = $user->id . md5(time());

            $skill_ids = $validatedData['skill_ids'];
            $course_of_study_ids = $validatedData['course_of_study_ids'] ?? [];
            $language_ids = $validatedData['language_ids'] ?? [];

            unset($validatedData['skill_ids'], $validatedData['course_of_study_ids'], $validatedData['language_ids']);

            $campaign = Campaign::create($validatedData);
            $campaign->skills()->attach($skill_ids);
            $campaign->courseOfStudies()->attach($course_of_study_ids);
            $campaign->languages()->attach($language_ids);

            DB::commit();

            return $this->successResponse(
                Campaign::find($campaign->id),
                'services.campaigns.created',
                [],
                Response::HTTP_CREATED
            );

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse(
                'services.campaigns.create_error',
                [],
                Response::HTTP_BAD_REQUEST
            );
        }
    }


    /**
     * Display the specified resource.
     *
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        if (is_numeric($id)) {
          $campaign = Campaign::findOrFail($id);
        }else{
            $campaign = Campaign::where('code', $id)->firstOrFail();
        }

        $campaign->applications;
        foreach ($campaign->applications as $application) {
            $application->cv = Cv::find($application->talent_cv_id);
        }
        // Log::info($campaign->applications[0]);
        $this->authorize('view', [Campaign::class, $campaign]);

        return response()->json([
            'status' => true,
            'message' => "",
            'data' => $campaign
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Models\Http\Requests\CampaignRequest  $request
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function update(CampaignRequest $request, $id)
    {
        // Authorization is declared in the CampaignRequest
        try{
            // Retrieve the validated input data....
            $validatedData = $request->validated();
            $campaign = Campaign::findOrFail($id);
    
            $skill_ids = $validatedData['skill_ids'];
            $course_of_study_ids = $validatedData['course_of_study_ids'];
            $language_ids = $validatedData['language_ids'];
            unset($validatedData['skill_ids'], $validatedData['course_of_study_ids'], $validatedData['language_ids']);
    
            $campaign->update($validatedData);
    
            // Update records to pivot table
            // $campaign->skills()->sync($skill_ids);
            $campaign->courseOfStudies()->sync($course_of_study_ids);
            $campaign->languages()->sync($language_ids);
    
            return $this->successResponse(
                Campaign::find($campaign->id),
                'services.campaigns.updated'
            );

        } catch (\Exception $e) {
            return $this->errorResponse(
                'services.campaigns.create_error',
                [],
                Response::HTTP_BAD_REQUEST
            );
        }
    }

    /**
     * Archive the specified resource from active list.
     *
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function archive($id)
    {
        $campaign = Campaign::findOrFail($id);

        $this->authorize('delete', [Campaign::class, $campaign]);

        $campaign->archived_at = now();
        $campaign->save();

        return $this->successResponse(
            Campaign::find($campaign->id),
            'services.campaigns.archived'
        );
    }

    /**
     * Publish campaign.
     *
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function publish($id)
    {
        $campaign = Campaign::findOrFail($id);

        $this->authorize('update', [Campaign::class, $campaign]);

        if ($campaign->is_published != true) {
            $campaign->is_published = true;
            $campaign->save();
            
            // Send Push notification
            // $notification = new Notification();
            // $notification->user_id = $campaign->user_id;
            // $notification->action = "/campaigns";
            // $notification->title = 'Campaign Published';
            // $notification->message = " Your campaign <b>$campaign->title</b> has been published.";
            // $notification->save();
            // event(new NewNotification($notification->user_id,$notification));

            // send email notification
            if ($campaign->user->email) {
                if (config('mail.queue_send')) {
                    Mail::to($campaign->user->email)->queue(new CampaignPublished($campaign));
                } else {
                    Mail::to($campaign->user->email)->send(new CampaignPublished($campaign));
                }
            }
        }

        return $this->successResponse(
            Campaign::find($campaign->id),
            'services.campaigns.published'
        );
    }

    /**
     * Unpublish campaign.
     *
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function unpublish($id)
    {
        $campaign = Campaign::findOrFail($id);

        $this->authorize('update', [Campaign::class, $campaign]);

        $campaign->is_published = false;
        $campaign->save();

        return $this->successResponse(
            Campaign::find($campaign->id),
            'services.campaigns.closed'
        );
    }


    /**
     * Unarchive the specified resource from archived storage.
     *
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function unarchive($id)
    {
        $campaign = Campaign::findOrFail($id);

        $this->authorize('delete', [Campaign::class, $campaign]);

        $campaign->archived_at = null;
        $campaign->save();

        return $this->successResponse(
            Campaign::find($campaign->id),
            'services.campaigns.restored'
        );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $campaign = Campaign::findOrFail($id);
        $this->authorize('delete', [Campaign::class, $campaign]);

        $relatedRecordsCount = related_records_count(Campaign::class, $campaign);

        if ($relatedRecordsCount <= 0) {
            $campaign->delete();
            return $this->successResponse(
                null,
                'services.campaigns.deleted'
            );
        }

        return $this->errorResponse(
            'services.campaigns.delete_error',
            ['name' => $campaign->name, 'count' => $relatedRecordsCount],
            Response::HTTP_BAD_REQUEST
        );
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
                $campaign = Campaign::find($id);
                if ($campaign && $this->authorize('delete', [Campaign::class, $campaign])) {
                    $valid_ids[] = $campaign->id;
                }
            }
        }

        foreach ($valid_ids as $id) {
            $campaign = Campaign::find($id);
            if (related_records_count(Campaign::class, $campaign) <= 0) {
                $campaign->delete();
                $deleted_count++;
            }
        }

        return $this->successResponse(
            null,
            'services.campaigns.multi_deleted',
            ['count' => $deleted_count]
        );
    }
}

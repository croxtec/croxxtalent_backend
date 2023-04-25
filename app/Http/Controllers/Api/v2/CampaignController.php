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
use Illuminate\Support\Facades\Log;

class CampaignController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
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
        })->where( function($query) use ($search) {
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
        // Authorization is declared in the Form Request

        // Retrieve the validated input data...
        $validatedData = $request->validated();

        $skill_ids = $validatedData['skill_ids'];
        $course_of_study_ids = $validatedData['course_of_study_ids'];
        $language_ids = $validatedData['language_ids'];
        unset($validatedData['skill_ids'], $validatedData['course_of_study_ids'], $validatedData['language_ids']);

        $campaign = Campaign::create($validatedData);
        if ($campaign) {
            // save records to pivot table
            // $campaign->skills()->attach($skill_ids);
            $campaign->courseOfStudies()->attach($course_of_study_ids);
            $campaign->languages()->attach($language_ids);

            return response()->json([
                'status' => true,
                'message' => "Campaign created successfully.",
                'data' => Campaign::find($campaign->id)
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
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $campaign = Campaign::findOrFail($id);
        $campaign->applications;
        foreach ($campaign->applications as $application) {
            $application->cv = Cv::find($application->talent_cv_id);
        }
        // Log::info($campaign->applications[0]);
        $this->authorize('view', [Campaign::class, $campaign]);

        return response()->json([
            'status' => true,
            'message' => "Successful.",
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

        return response()->json([
            'status' => true,
            'message' => "Campaign updated successfully.",
            'data' => Campaign::find($campaign->id)
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
        $campaign = Campaign::findOrFail($id);

        $this->authorize('delete', [Campaign::class, $campaign]);

        $campaign->archived_at = now();
        $campaign->save();

        return response()->json([
            'status' => true,
            'message' => "Campaign archived successfully.",
            'data' => Campaign::find($campaign->id)
        ], 200);
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
            $notification = new Notification();
            $notification->user_id = $campaign->user_id;
            $notification->action = "/campaigns";
            $notification->title = 'Campaign Published';
            $notification->message = " Your campaign <b>$campaign->title</b> has been published.";
            $notification->save();
            event(new NewNotification($notification->user_id,$notification));
            // send email notification
            if ($campaign->user->email) {
                if (config('mail.queue_send')) {
                    Mail::to($campaign->user->email)->queue(new CampaignPublished($campaign));
                } else {
                    Mail::to($campaign->user->email)->send(new CampaignPublished($campaign));
                }
            }
        }

        return response()->json([
            'status' => true,
            'message' => "Campaign published successfully.",
            'data' => Campaign::find($campaign->id)
        ], 200);
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

        return response()->json([
            'status' => true,
            'message' => "Campaign unpublished successfully.",
            'data' => Campaign::find($campaign->id)
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
        $campaign = Campaign::findOrFail($id);

        $this->authorize('delete', [Campaign::class, $campaign]);

        $campaign->archived_at = null;
        $campaign->save();

        return response()->json([
            'status' => true,
            'message' => "Campaign unarchived successfully.",
            'data' => Campaign::find($campaign->id)
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
        $campaign = Campaign::findOrFail($id);

        $this->authorize('delete', [Campaign::class, $campaign]);

        $name = $campaign->name;
        // check if the record is linked to other records
        $relatedRecordsCount = related_records_count(Campaign::class, $campaign);

        if ($relatedRecordsCount <= 0) {
            $campaign->delete();
            return response()->json([
                'status' => true,
                'message' => "Campaign deleted successfully.",
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
                $campaign = Campaign::find($id);
                if ($campaign) {
                    $this->authorize('delete', [Campaign::class, $campaign]);
                    $valid_ids[] = $campaign->id;
                }
            }
        }
        $valid_ids = collect($valid_ids);
        if ($valid_ids->isNotEmpty()) {
            foreach ($valid_ids as $id) {
                $campaign = Campaign::find($id);
                // check if the record is linked to other records
                $relatedRecordsCount = related_records_count(Campaign::class, $campaign);
                if ($relatedRecordsCount <= 0) {
                    $campaign->delete();
                    $deleted_count++;
                }
            }
        }

        return response()->json([
            'status' => true,
            'message' => "{$deleted_count} campaigns deleted successfully.",
        ], 200);
    }
}

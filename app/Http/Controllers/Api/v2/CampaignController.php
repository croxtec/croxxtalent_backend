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
    // use ApiResponseTrait;

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
        $published = $request->input('published');
        $datatable_draw = $request->input('draw'); // if any

        $archived = $archived == 'yes' ? true : ($archived == 'no' ? false : null);
        $published = $published == 'yes' ? true : ($published == 'no' ? false : null);
 
        $campaigns = Campaign::where( function ($query) use ($archived, $published) {
            if ($archived !== null ) {
                if ($archived === true ) {
                    $query->whereNotNull('archived_at');
                } else {
                    $query->whereNull('archived_at');
                }
            }
            if ($published !== null) {
                if ($published === true) {
                    $query->where('is_published', true);
                } else {    
                    $query->where('is_published', false);
                }
            }
        }) 
        ->when($user->type == 'employer', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })
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
            'message' => ""
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
     * Show campaign details (lightweight for job seekers/public view)
     * Only includes essential campaign information
     */
    public function show($id)
    {
        if (is_numeric($id)) {
            $campaign = Campaign::findOrFail($id);
        } else {
            $campaign = Campaign::where('code', $id)->firstOrFail();
        }
        
        $this->authorize('view', [Campaign::class, $campaign]);
        
        // Only load essential campaign data without applications
        $campaignData = $campaign->only([
            'id', 'code', 'title', 'job_title', 'summary', 'description',
            'experience_level', 'work_site', 'work_type', 'city', 'expire_at',
            'currency_code', 'min_salary', 'max_salary', 'number_of_positions',
            'years_of_experience', 'is_confidential_salary', 'is_published', 'published_at', 'status'
        ]) + [
            // Include computed attributes that are lightweight
            'industry_name' => $campaign->industry_name,
            'department_name' => $campaign->department_name,
            'minimum_degree_name' => $campaign->minimum_degree_name,
            'country_name' => $campaign->country_name,
            'state_name' => $campaign->state_name,
            'currency_symbol' => $campaign->currency_symbol,
            'photo_url' => $campaign->photo_url,
            'user_display_name' => $campaign->user_display_name,
            'total_applications' => $campaign->total_applications,
            // Include related data that's typically needed
            'skills' => $campaign->skills,
            'languages' => $campaign->languages,
            'course_of_studies' => $campaign->course_of_studies,
        ];

        return response()->json([
            'status' => true,
            'message' => "Campaign details retrieved successfully",
            'data' => $campaignData
        ], 200);
    }

    /**
     * Get campaign candidates/applications (for employers only)
     * This is where you load the heavy application data
     */
    public function candidates($id)
    {
        if (is_numeric($id)) {
            $campaign = Campaign::findOrFail($id);
        } else {
            $campaign = Campaign::where('code', $id)->firstOrFail();
        }
        
        // Ensure only campaign owner/employer can view candidates
        $this->authorize('viewCandidates', [Campaign::class, $campaign]);
        
        // Load applications with related data efficiently
        $applications = $campaign->applications()
            ->with([
                'talentUser:id,name,email,phone,display_name',
                'talentCv:id,talent_user_id,file_path,created_at',
                'talentInvitation:id,talent_user_id,campaign_id,status,created_at'
            ])
            ->select([
                'id', 'campaign_id', 'talent_user_id', 'talent_cv_id', 
                'rating', 'status', 'created_at', 'updated_at'
            ])
            ->orderBy('created_at', 'desc')
            ->get();

        // Transform the data to include computed attributes efficiently
        $candidatesData = $applications->map(function ($application) {
            return [
                'id' => $application->id,
                'status' => $application->status, // Uses the accessor you defined
                'rating' => $application->rating,
                'applied_at' => $application->created_at,
                'talent' => [
                    'id' => $application->talentUser->id,
                    'name' => $application->talentUser->name,
                    'display_name' => $application->talentUser->display_name,
                    'email' => $application->talentUser->email,
                    'phone' => $application->talentUser->phone,
                ],
                'cv' => $application->talentCv ? [
                    'id' => $application->talentCv->id,
                    'file_path' => $application->talentCv->file_path,
                    'uploaded_at' => $application->talentCv->created_at,
                ] : null,
                'invitation' => $application->talentInvitation ? [
                    'status' => $application->talentInvitation->status,
                    'invited_at' => $application->talentInvitation->created_at,
                ] : null,
            ];
        });

        return response()->json([
            'status' => true,
            'message' => "Campaign candidates retrieved successfully",
            'data' => [
                'campaign' => [
                    'id' => $campaign->id,
                    'code' => $campaign->code,
                    'title' => $campaign->title,
                    'total_applications' => $applications->count(),
                ],
                'candidates' => $candidatesData
            ]
        ], 200);
    }

    /**
     * Get campaign summary statistics (lightweight overview for dashboard)
     */
    public function summary($id)
    {
        if (is_numeric($id)) {
            $campaign = Campaign::findOrFail($id);
        } else {
            $campaign = Campaign::where('code', $id)->firstOrFail();
        }
        
        $this->authorize('view', [Campaign::class, $campaign]);
        
        // Get application statistics without loading full data
        $applicationStats = $campaign->applications()
            ->selectRaw('
                COUNT(*) as total_applications,
                SUM(CASE WHEN rating = 0 THEN 1 ELSE 0 END) as applied_count,
                SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) as qualified_count,
                SUM(CASE WHEN rating = 2 THEN 1 ELSE 0 END) as unqualified_count,
                SUM(CASE WHEN rating = 3 THEN 1 ELSE 0 END) as invited_count
            ')
            ->first();

        return response()->json([
            'status' => true,
            'message' => "Campaign summary retrieved successfully",
            'data' => [
                'campaign' => [
                    'id' => $campaign->id,
                    'code' => $campaign->code,
                    'title' => $campaign->title,
                    'status' => $campaign->expire_at > now() ? 'active' : 'expired',
                    'expire_at' => $campaign->expire_at,
                ],
                'statistics' => $applicationStats
            ]
        ], 200);
    }

    /**
     * Alternative: Paginated candidates method for large datasets
     */
    public function candidatesPaginated($id, Request $request)
    {
        if (is_numeric($id)) {
            $campaign = Campaign::findOrFail($id);
        } else {
            $campaign = Campaign::where('code', $id)->firstOrFail();
        }
        
        $this->authorize('viewCandidates', [Campaign::class, $campaign]);
        
        $perPage = $request->get('per_page', 15);
        $status = $request->get('status'); // Filter by status if needed
        
        $query = $campaign->applications()
            ->with([
                'talentUser:id,name,email,display_name',
                'talentCv:id,talent_user_id,file_path,created_at'
            ])
            ->select([
                'id', 'campaign_id', 'talent_user_id', 'talent_cv_id', 
                'rating', 'created_at'
            ]);
        
        if ($status !== null) {
            $statusMap = [
                'applied' => 0,
                'qualified' => 1,
                'unqualified' => 2,
                'invited' => 3
            ];
            if (isset($statusMap[$status])) {
                $query->where('rating', $statusMap[$status]);
            }
        }
        
        $applications = $query->orderBy('created_at', 'desc')->paginate($perPage);
        
        return response()->json([
            'status' => true,
            'message' => "Campaign candidates retrieved successfully",
            'data' => $applications
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
        try {
            $validatedData = $request->validated();
            $campaign = Campaign::findOrFail($id);
            
            // Check if campaign is published
            $isPublished = $campaign->status === 'published' || 
                        $campaign->is_published === true ||
                        $campaign->published_at !== null;
            
            // Extract relationship data if present (only for draft campaigns)
            $skill_ids = $validatedData['skill_ids'] ?? null;
            $course_of_study_ids = $validatedData['course_of_study_ids'] ?? null;
            $language_ids = $validatedData['language_ids'] ?? null;
            
            // Remove relationship fields from main update data
            unset(
                $validatedData['skill_ids'], 
                $validatedData['course_of_study_ids'], 
                $validatedData['language_ids']
            );

            // Update main campaign data
            $campaign->update($validatedData);

            // Update relationships only if not published (relationships are restricted)
            if (!$isPublished) {
                if ($skill_ids !== null) {
                    $campaign->skills()->sync($skill_ids);
                }
                if ($course_of_study_ids !== null) {
                    $campaign->courseOfStudies()->sync($course_of_study_ids);
                }
                if ($language_ids !== null) {
                    $campaign->languages()->sync($language_ids);
                }
            }

            // Log the update for audit trail
            // $this->logCampaignUpdate($campaign, $request->all(), $isPublished);

            return $this->successResponse(
                Campaign::with(['skills', 'courseOfStudies', 'languages'])->find($campaign->id),
                'services.campaigns.updated'
            );

        } catch (\Exception $e) {
            \Log::error('Campaign update failed: ' . $e->getMessage(), [
                'campaign_id' => $id,
                'data' => $request->all(),
            ]);
            
            return $this->errorResponse(
                'services.campaigns.update_error',
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
            $campaign->published_at = now();
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
        $campaign->archived_at = null;
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

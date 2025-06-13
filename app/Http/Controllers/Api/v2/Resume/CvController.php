<?php

namespace App\Http\Controllers\Api\v2;

use App\Events\NewNotification;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Requests\CvRequest;
use App\Http\Requests\CvPhotoRequest;
use Carbon\Carbon;
use DomPDF;
use SnappyPDF as PDF;
use App\Models\User;
use App\Models\Cv;
use App\Models\Audit;
use Illuminate\Support\Facades\Log;

class CvController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // $this->authorize('view-any', Cv::class);
        // Log::info('Reach');
        $per_page = $request->input('per_page', 100);
        $sort_by = $request->input('sort_by', 'name');
        $sort_dir = $request->input('sort_dir', 'asc');
        $search = $request->input('search');
        $skill_ids = $request->input('skill_ids');
        $job_title_ids = $request->input('job_title_ids');
        $language_ids = $request->input('language_ids');
        $course_of_study_ids = $request->input('course_of_study_ids');
        $experience_years = $request->input('experience_years');
        $published = $request->input('published');
        $datatable_draw = $request->input('draw'); // if any

        $published = $published == 'yes' ? true : ($published == 'no' ? false : null);

        $cvs = Cv::where( function ($query) use ($skill_ids, $job_title_ids, $language_ids, $course_of_study_ids, $experience_years, $published) {
            // filter by skills
            if ($skill_ids) {
                $query->whereHas('skills', function($sub_query) use ($skill_ids) {

                    if (is_array($skill_ids)) {
                        $counter = 1;
                        foreach($skill_ids as $skill_id) {
                            if ($counter === 1) {
                                $sub_query->where('skill_id', $skill_id);
                            } else {
                                $sub_query->orWhere('skill_id', $skill_id);
                            }
                            // $sub_query->secondary = Secondary::where('id','skill_secondary_id')->first();
                            // $sub_query->tertiary =  Tertiary::where('id','skill_tertiary_id') ->first();
                            $counter++;
                        }
                    } else {
                        $sub_query->where('skill_id', $skill_ids);
                    }
                    return $sub_query;
                });
            }
            // filter by job titles
            if ($job_title_ids) {
                if (is_array($job_title_ids)) {
                    $counter = 1;
                    foreach($job_title_ids as $job_title_id) {
                        if ($counter === 1) {
                            $query->where('job_title_id', $job_title_id);
                        } else {
                            $query->orWhere('job_title_id', $job_title_id);
                        }
                        $counter++;
                    }
                } else {
                    $query->where('job_title_id', $job_title_ids);
                }
            }
            // filter by languages
            if ($language_ids) {
                $query->whereHas('languages', function($sub_query) use ($language_ids) {
                    if (is_array($language_ids)) {
                        $counter = 1;
                        foreach($language_ids as $language_id) {
                            if ($counter === 1) {
                                $sub_query->where('language_id', $language_id);
                            } else {
                                $sub_query->orWhere('language_id', $language_id);
                            }
                            $counter++;
                        }
                    } else {
                        $sub_query->where('language_id', $language_ids);
                    }
                    return $sub_query;
                });
            }
            // filter by course of studies
            if ($course_of_study_ids) {
                $query->whereHas('educations', function($sub_query) use ($course_of_study_ids) {
                    if (is_array($course_of_study_ids)) {
                        $counter = 1;
                        foreach($course_of_study_ids as $course_of_study_id) {
                            if ($counter === 1) {
                                $sub_query->where('course_of_study_id', $course_of_study_id);
                            } else {
                                $sub_query->orWhere('course_of_study_id', $course_of_study_id);
                            }
                            $counter++;
                        }
                    } else {
                        $sub_query->where('course_of_study_id', $course_of_study_ids);
                    }
                    return $sub_query;
                });
            }
            // filter by experience_years
            if ($experience_years) {
                $query->whereHas('workExperiences', function($sub_query) use ($experience_years) {
                    if (is_array($experience_years)) {
                        $counter = 1;
                        foreach($experience_years as $exp_year) {
                            $max_start_date = date('Y-m-d', strtotime("-{$exp_year} years"));
                            $min_start_date = date('Y-m-d', strtotime("-1 year", strtotime($max_start_date)));
                            if ($counter === 1) {
                                if ($exp_year < 15) {
                                    $sub_query->whereBetween('start_date', [$min_start_date, $max_start_date]);
                                } else {
                                    $sub_query->where('start_date', "<=", $max_start_date);
                                }
                            } else {
                                if ($exp_year < 15) {
                                    $sub_query->orWhereBetween('start_date', [$min_start_date, $max_start_date]);
                                } else {
                                    $sub_query->orWhere('start_date', "<=", $max_start_date);
                                }
                            }
                            $counter++;
                        }
                    } else {
                        $max_start_date = date('Y-m-d', strtotime("-{$experience_years} years"));
                        $min_start_date = date('Y-m-d', strtotime("-1 year", strtotime($max_start_date)));
                        if ($experience_years < 15) {
                            $sub_query->whereBetween('start_date', [$min_start_date, $max_start_date]);
                        } else {
                            $sub_query->where('start_date', "<=", $max_start_date);
                        }
                    }
                    return $sub_query;
                });
            }
            // filter by published
            if ($published !== null ) {
                $query->where('is_published', $published);
            }
        })->when( $search ,function($query) use ($search) {
            // filter by search
            $query->where('first_name', 'LIKE', "%{$search}%")
                    ->orWhere('last_name', 'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%")
                    ->orWhere('phone', 'LIKE', "%{$search}%")
                    ->orWhere('career_summary', 'LIKE', "%{$search}%");
        })->orderBy($sort_by, $sort_dir);

        // foreach ($cvs->skills as $skill) {
        //     $skill->secondary = Secondary::where('id','skill_secondary_id')->first();
        //     $skill->tertiary =  Tertiary::where('id','skill_tertiary_id') ->first();
        // }

        if ($per_page === 'all' || $per_page <= 0 ) {
            $results = $cvs->get();
            $cvs = new \Illuminate\Pagination\LengthAwarePaginator($results, $results->count(), -1);
        } else {
            $cvs = $cvs->paginate($per_page);
        }

        $response = collect([
            'status' => true,
            'message' => "Successful."
        ])->merge($cvs)->merge(['draw' => $datatable_draw]);
        return response()->json($response, 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Models\Http\Requests\CvRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CvRequest $request)
    {
        // Authorization was declared in the Form Request

        // Retrieve the validated input data...
        $validatedData = $request->validated();

        $user = User::findOrFail($validatedData['user_id']);

        if ($user->type != 'talent') {
            return response()->json([
                'status' => false,
                'message' => "CV Builder can only be used by a talent.",
            ], 400);
        }

        $aff_eligibility = false;
        $_cv = Cv::where('user_id', $user->id)->first();

        if (!$_cv) {
            $aff_eligibility = true;
        }

        $cv = Cv::firstOrCreate(
            ['user_id' => $user->id],
            [
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
            ]
        );

        if ($cv) {
            // give reward to affiliate referral
            if ($aff_eligibility && $user->referral_user_id) {
                $referralUser = User::find($user->referral_user_id);
                if ($referralUser) {
                    $referralUser->affiliate_reward_points = (int) $referralUser->affiliate_reward_points + 10;
                    $referralUser->save();
                }
            }

            return response()->json([
                'status' => true,
                'message' => "CV profile loaded successfully.",
                'data' => $cv
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
        $cv = Cv::findOrFail($id);
        $cv_skills = [];
        foreach($cv->skills as $sk){
            $sk->secondary;
            $sk->tertiary;
            // array_push($cv_skills, $sk);
        }
        // $cv->cv_skills = $cv_skills;
        $this->authorize('view', [Cv::class, $cv]);

        return response()->json([
            'status' => true,
            'message' => "Successful.",
            'data' => $cv
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Models\Http\Requests\CvRequest  $request
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function update(CvRequest $request, $id)
    {
        // Authorization was declared in the CvRequest
        // $request->photo_url = 'https://images.unsplash.com/photo-1544502062-f82887f03d1c?ixid=MXwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHw%3D&ixlib=rb-1.2.1&auto=format&fit=crop&w=827&q=80';
        // Retrieve the validated input data....
        // $validatedData = $request->validated();
        $cv = Cv::findOrFail($id);
        // Log::info($request);
        $cv->update($request->all());
        return response()->json([
            'status' => true,
            'message' => "CV profile saved successfully.",
            'data' => Cv::find($cv->id)
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
        $cv = Cv::findOrFail($id);

        $this->authorize('delete', [Cv::class, $cv]);

        $name = $cv->first_name;
        // check if the record is linked to other records
        // $relatedRecordsCount = related_records_count(Cv::class, $cv);
        $relatedRecordsCount = 1;

        if ($relatedRecordsCount <= 0) {
            // $user->delete();
            return response()->json([
                'status' => true,
                'message' => "CV deleted successfully.",
            ], 200);
        } else {
            return response()->json([
                'status' => false,
                'message' => "The record cannot be deleted because it is associated with {$relatedRecordsCount} other record(s). You can archive it instead.",
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
                $cv = Cv::find($id);
                if ($cv) {
                    $this->authorize('delete', [Cv::class, $cv]);
                    $valid_ids[] = $cv->id;
                }
            }
        }
        $valid_ids = collect($valid_ids);
        if ($valid_ids->isNotEmpty()) {
            foreach ($valid_ids as $id) {
                $cv = Cv::find($id);
                // check if the record is linked to other records
                $relatedRecordsCount = related_records_count(Cv::class, $cv);
                if ($relatedRecordsCount <= 0) {
                    $cv->delete();
                    $deleted_count++;
                }
            }
        }

        return response()->json([
            'status' => true,
            'message' => "{$deleted_count} CVs deleted successfully.",
        ], 200);
    }

    /**
    * Upload and update photo.
    *
    * @param  \App\Models\Http\Requests\CvPhotoRequest  $request
    * @param  string  $id
    * @return \Illuminate\Http\Response
    */
    public function photo(Request $request, $id)
    {
        // Authorization was declared in the CvPhotoRequest

        // Retrieve the validated input data....
        // $validatedData = $request->validated();
        $cv = Cv::findOrFail($id);

        if ($request->hasFile('photo') && $request->file('photo')->isValid()) {
            $extension = $request->file('photo')->extension();
            $filename = $cv->id . '-' . time() . '-' . Str::random(32);
            $filename = "{$filename}.$extension";
            $year = date('Y');
            $month = date('m');
            // $rel_upload_path    = "profile/{$year}";
            $rel_upload_path    = "profile/";
            if ( config('app.env') == 'local') {
                $rel_upload_path = "local/{$rel_upload_path}"; // dir for dev environment test uploads
            }
            // do upload
            $uploaded_file_path = $request->file('photo')->storeAs($rel_upload_path, $filename);
            Storage::setVisibility($uploaded_file_path, 'public'); //set file visibility to  "public"
            // delete previously uploaded file if any
            if ($cv->photo) {
                Storage::delete($cv->photo);
            }
            // Update with the newly update file
            $cv->photo = $uploaded_file_path;
            $cv->save();

            return response()->json([
                'status' => true,
                'message' => 'Photo uploaded successfully.',
                'data' => [
                    'photo_url' => cloud_asset($uploaded_file_path),
                    'cv' => $cv
                ]
            ], 200);
        }
        return response()->json([
            'status' => true,
            'message' => "Could not upload photo, please try again.",
        ], 400);
    }

    /**
     * Publish a CV
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function publish(Request $request, $id)
    {
        $cv = Cv::findOrFail($id);

        $this->authorize('update', [Cv::class, $cv]);
        $old_is_published = $cv->is_published;

        $cv->is_published = true;
        $cv->save();

        // save audit trail log
        $event = $old_is_published === false ? 'cvs.created' : 'cvs.updated';
        $old_values = ['is_published' => $old_is_published];
        $new_values = ['is_published' => $cv->is_published];
        Audit::log($request->user()->id, $event, $old_values, $new_values, Cv::class, $cv->id);

        // Send notifications to the references
        if ($cv->references) {
            foreach($cv->references as $cvReference) {
                $cvReference->sendReferenceRequestEmail();
            }
        }

        return response()->json([
            'status' => true,
            'message' => "CV published and sent successfully.",
            'data' => Cv::find($cv->id)
        ], 200);
    }

    /**
     * Unublish a CV
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function unpublish($id)
    {
        $cv = Cv::findOrFail($id);

        $this->authorize('update', [Cv::class, $cv]);

        $cv->is_published = false;
        $cv->save();

        return response()->json([
            'status' => true,
            'message' => "CV unpublished successfully.",
            'data' => Cv::find($cv->id)
        ], 200);
    }

    /**
    * Generate CV
    *
    * @param  string  $id
    * @return \Illuminate\Http\Response
    */
    public function generate(Request $request, $id)
    {
        $user = null;
        if(str_contains($id,'-')){
            $id = explode('-', $id)[0];
            $user = explode('-', $id)[1];
            if($user) $user = User::find($user);
        }
        $cv = Cv::findOrFail($id);

        $download = $request->query('download');
        // Log::info($user);
        // $this->authorize('view', [Cv::class, $cv]);

        $filename = Str::slug("{$cv->first_name} {$cv->last_name} CV {$cv->id}") . ".pdf";
        $pdf = PDF::loadView('api.cv-templates.default', compact('cv','user'));
        // print_r($pdf);exit;

        $pdf->setPaper('a4');
        // options applicable to SnappyPDF
        $pdf->setOrientation('portrait'); // portrait | landscape
        $pdf->setOption('margin-top', 0);
        $pdf->setOption('margin-bottom', 0);
        $pdf->setOption('margin-left', 0);
        $pdf->setOption('margin-right', 0);

        if ($download && $download == 1) {
            return $pdf->download($filename);
        } else {
            return $pdf->stream($filename); // Or $pdf->inline($filename);
        }

        return response()->json([
            'status' => true,
            'message' => "Successful.",
            'data' => $cv
        ], 200);
    }

    public function generate_employer(Request $request, $id,$employer)
    {
        $user = User::find($employer);

        $cv = Cv::findOrFail($id);

        $download = $request->query('download');
        // Log::info($user);
        // $this->authorize('view', [Cv::class, $cv]);

        $filename = Str::slug("{$cv->first_name} {$cv->last_name} CV {$cv->id}") . ".pdf";
        $pdf = PDF::loadView('api.cv-templates.default', compact('cv','user'));
        // print_r($pdf);exit;

        $pdf->setPaper('a4');
        // options applicable to SnappyPDF
        $pdf->setOrientation('portrait'); // portrait | landscape
        $pdf->setOption('margin-top', 0);
        $pdf->setOption('margin-bottom', 0);
        $pdf->setOption('margin-left', 0);
        $pdf->setOption('margin-right', 0);

        if ($download && $download == 1) {
            return $pdf->download($filename);
        } else {
            return $pdf->stream($filename); // Or $pdf->inline($filename);
        }

        return response()->json([
            'status' => true,
            'message' => "Successful.",
            'data' => $cv
        ], 200);
    }


    public function save_cv(Request $request, $id)
    {
        $cv = Cv::findOrFail($id);

        if ($request->hasFile('cv') && $request->file('cv')->isValid()) {
            $extension = $request->file('cv')->extension();
            $filename = $cv->id . '-' . time() . '-' . Str::random(32);
            $filename = "{$filename}.$extension";
            $year = date('Y');
            $month = date('m');
            $rel_upload_path    = "profile/{$year}/{$month}";
            if ( config('app.env') == 'local') {
                $rel_upload_path = "local/{$rel_upload_path}"; // dir for dev environment test uploads
            }
            // do upload
            $uploaded_file_path = $request->file('cv')->storeAs($rel_upload_path, $filename);
            Storage::setVisibility($uploaded_file_path, 'public'); //set file visibility to  "public"
            // delete previously uploaded file if any
            if ($cv->photo) {
                Storage::delete($cv->photo);
            }
            // Update with the newly update file
            $cv->photo = $uploaded_file_path;
            $cv->save();

            return response()->json([
                'status' => true,
                'message' => 'CV uploaded successfully.',
                'data' => [
                    'photo_url' => cloud_asset($uploaded_file_path),
                    'cv' => $cv
                ]
            ], 200);
        }
        return response()->json([
            'status' => true,
            'message' => "Could not upload photo, please try again.",
        ], 400);

    }
}

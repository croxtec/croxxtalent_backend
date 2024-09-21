<?php

namespace App\Http\Controllers\Api\v2\Resume;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Requests\CvSkillRequest;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Cv;
use App\Models\CvSkill;
use App\Models\VettingSummary;

class CvSkillController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  string  $cv_id
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $cv = CV::where('user_id', $user->id)->firstorFail();

        $this->authorize('view-any', Cv::class);

        $per_page = $request->input('per_page', 100);
        $sort_by = $request->input('sort_by', 'created_at');
        $sort_dir = $request->input('sort_dir', 'asc');
        $search = $request->input('search');
        $datatable_draw = $request->input('draw'); // if any

        $cvSkills = CvSkill::where('cv_id', $cv->id)
        ->where( function($query) use ($search) {
            $query->where('id', 'LIKE', "%{$search}%");
        })->orderBy($sort_by, $sort_dir);

        if ($per_page === 'all' || $per_page <= 0 ) {
            $results = $cvSkills->get();
            $cvSkills = new \Illuminate\Pagination\LengthAwarePaginator($results, $results->count(), -1);
        } else {
            $cvSkills = $cvSkills->paginate($per_page);
        }

        foreach ($cvSkills as $skill) {
            $skill->secondary;
            $skill->tertiary;
            $skill->extra = 1;
        }

        $response = collect([
            'status' => true,
            'message' => "Successful."
        ])->merge($cvSkills)->merge(['draw' => $datatable_draw]);
        return response()->json($response, 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Models\Http\Requests\CvSkillRequest  $request
     * @param  string  $cv_id
     * @return \Illuminate\Http\Response
     */
    public function store(CvSkillRequest $request)
    {
        $user = $request->user();

        $cv = CV::where('user_id', $user->id)->firstorFail();

        // Authorization was declared in the Form Request

        // Retrieve the validated input data...
        $validatedData = $request->validated();

        $validatedData['cv_id'] = $cv->id;
        // info($validatedData);

        $cvSkill = CvSkill::updateOrCreate(
            [
                'cv_id' => $validatedData['cv_id'],
                'domain_id' => $validatedData['domain_id'],
                'core_id' => $validatedData['core_id'],
                'skill_id' => $validatedData['skill_id'],
                'level' => $request->level
            ],
            $validatedData
        );

        $vetting = VettingSummary::create([
            'cv_skill' => $cvSkill->id,
            'assesment_id' => 1,
            'talent_id' => $user->id
        ]);

        if ($cvSkill) {
            return response()->json([
                'status' => true,
                'message' => "Competence added successfully.",
                'data' => $cvSkill
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
     *3
     * @param  string  $cv_id
     * @param  string  $cv_skill_id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request,  $cv_skill_id)
    {
        $user = $request->user();

        $cv = CV::where('user_id', $user->id)->firstorFail();
        $cvSkill = CvSkill::findOrFail($cv_skill_id);
        if ($cv->id != $cvSkill->cv_id) {
            return response()->json([
                'status' => false,
                'message' => "Unrelated request.",
            ], 400);
        }
        $this->authorize('view', [Cv::class, $cv]);

        return response()->json([
            'status' => true,
            'message' => "Successful.",
            'data' => $cvSkill
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Models\Http\Requests\CvSkillRequest  $request
     * @param  string  $cv_id
     * @param  string  $cv_skill_id
     * @return \Illuminate\Http\Response
     */
    public function update(CvSkillRequest $request,  $cv_skill_id)
    {
        $user = $request->user();

        $cv = CV::where('user_id', $user->id)->firstorFail();
        $cvSkill = CvSkill::findOrFail($cv_skill_id);
        if ($cv->id != $cvSkill->cv_id) {
            return response()->json([
                'status' => false,
                'message' => "Unrelated request.",
            ], 400);
        }

        // Authorization was declared in the Form Request

        // Retrieve the validated input data....
        $validatedData = $request->validated();
        $cvSkill->update($validatedData);
        return response()->json([
            'status' => true,
            'message' => "Competence updated successfully.",
            'data' => CvSkill::findOrFail($cvSkill->id)
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  string  $id
     * @param  string  $cv_skill_id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $cv_skill_id)
    {
        $user = $request->user();

        $cv = CV::where('user_id', $user->id)->firstorFail();
        $cvSkill = CvSkill::findOrFail($cv_skill_id);
        if ($cv->id != $cvSkill->cv_id) {
            return response()->json([
                'status' => false,
                'message' => "Unrelated request.",
            ], 400);
        }

        $this->authorize('delete', [Cv::class, $cv]);

        $cvSkill->delete();
        return response()->json([
            'status' => true,
            'message' => "Competence deleted successfully.",
        ], 200);
    }
}

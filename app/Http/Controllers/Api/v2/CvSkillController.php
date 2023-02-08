<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Requests\CvSkillRequest;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Cv;
use App\Models\CvSkill;

class CvSkillController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  string  $cv_id
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, $cv_id)
    {
        $cv = Cv::findOrFail($cv_id);

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
    public function store(CvSkillRequest $request, $cv_id)
    {
        $cv = Cv::findOrFail($cv_id);

        // Authorization was declared in the Form Request

        // Retrieve the validated input data...
        $validatedData = $request->validated(); 
        
        $validatedData['cv_id'] = $cv->id;
        $cvSkill = CvSkill::updateOrCreate(
            ['cv_id' => $validatedData['cv_id'], 'skill_id' => $validatedData['skill_id'],
                'skill_secondary_id' => $request->secondary_id, 
                'skill_tertiary_id' => $request->tertiary_id,
                'level' => $request->level
            ], 
            $validatedData
        );  
        if ($cvSkill) {
            return response()->json([
                'status' => true, 
                'message' => "Skill created successfully.",
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
     *
     * @param  string  $cv_id
     * @param  string  $cv_skill_id
     * @return \Illuminate\Http\Response
     */
    public function show($cv_id, $cv_skill_id)
    {
        $cv = Cv::findOrFail($cv_id);
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
            'data' => $cv
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
    public function update(CvSkillRequest $request, $cv_id, $cv_skill_id)
    {
        $cv = Cv::findOrFail($cv_id);
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
            'message' => "Skill updated successfully.",
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
    public function destroy($cv_id, $cv_skill_id)
    {
        $cv = Cv::findOrFail($cv_id);
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
            'message' => "Skill deleted successfully.",
        ], 200);              
    }
}

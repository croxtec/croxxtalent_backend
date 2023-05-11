<?php

namespace App\Http\Controllers\Api\v2;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cv;
use App\Models\CvSkill;
use App\Models\Employee;
use App\Models\AssesmentSummary;
use App\Models\Assesment;

class TalentCompetencyController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function skill(Request $request)
    {
        $user = $request->user();
        $cv = CV::where('user_id', $user->id)->firstorFail();
        $groups = array();

        $per_page = $request->input('per_page', -1);
        $sort_by = $request->input('sort_by', 'created_at');
        $sort_dir = $request->input('sort_dir', 'asc');
        $search = $request->input('search');


        $cvSkills = CvSkill::where('cv_id', $cv->id)
        ->where( function($query) use ($search) {
            $query->where('id', 'LIKE', "%{$search}%");
        })->orderBy($sort_by, $sort_dir);

        $cvSkills = $cvSkills->get()->toArray();

        $secondary = array_column($cvSkills, 'skill_secondary_id');

        foreach($cvSkills as $skill){
            $groups[$skill['skill_secondary_id']][] = $skill;
        }

        // info($groups);

        return response()->json([
            'status' => true,
            'data' => $groups,
            'message' => 'Data imported successfully.'
        ], 200);
    }



    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function experience(Request $request)
    {
        $user = $request->user();

        $experience =  Employee::where('user_id', $user->id)->get();

        foreach ($experience as $learn) {
           $summary = AssesmentSummary::where([
                'talent_id' => $user->id,
                'employer_id' => $learn->user_id
            ])->with('assesment')->get();

            // $assemsnts = array_column($summary->toArray(), 'assesment_id');
            // info($assemsnts);
            $learn->competence =  $summary;// Assesment::where('id', $assemsnts)->with('summary')->get();
        }

        return response()->json([
            'status' => true,
            'data' => $experience,
            'message' => 'Data imported successfully.'
        ], 200);

    }


     /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function manager(Request $request)
    {
        $user = $request->user();
    }


    public function professional(Request $request)
    {
        $user = $request->user();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }
}

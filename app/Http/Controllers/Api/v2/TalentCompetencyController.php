<?php

namespace App\Http\Controllers\Api\v2;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cv;
use App\Models\CvSkill;
use App\Models\Employee;
use App\Models\AssesmentSummary;
use App\Models\Assesment;
use App\Models\VettingSummary;
use App\Models\EmployerJobcode as JobCode;

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
        })
        ->orderBy($sort_by, $sort_dir);

        $cvSkills = $cvSkills->get()->toArray();

        $secondary = array_column($cvSkills, 'skill_secondary_id');

        foreach($cvSkills as $skill){
            $skill['vetting'] = VettingSummary::where('cv_skill', $skill['id'])->first();
            $groups[$skill['skill_id']][$skill['skill_secondary_id']][] = $skill;
        }

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
            ])->with('assesment_code')->get();

            // $assemsnts = array_column($summary->toArray(), 'assesment_id');
            // info($assemsnts);
            $learn->competence =  $summary;// Assesment::where('id', $assemsnts)->with('summary')->get();
        }

        return response()->json([
            'status' => true,
            'data' => $experience,
            'message' => 'Experience  .'
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
        $per_page = $request->input('per_page', 100);
        $sort_by = $request->input('sort_by', 'created_at');
        $sort_dir = $request->input('sort_dir', 'desc');
        $search = $request->input('search');
        $archived = $request->input('archived');

        $groups = array();
        $employee =  Employee::where('user_id', $user->id)->firstOrFail();

        $jobcodes =  JobCode::whereJsonContains('managers', $employee->id)->get();
        $managers =  array_column($jobcodes->toArray(),  'id');


        $assessments =  Assesment::where(['job_code_id' =>  $managers ])
                                ->orWhereJsonContains('managers', $employee->id)
            ->when($archived ,function ($query) use ($archived) {
            if ($archived !== null ) {
                if ($archived === true ) {
                    $query->whereNotNull('archived_at');
                } else {
                    $query->whereNull('archived_at');
                }
            }
        })->where( function($query) use ($search) {
            $query->where('code', 'LIKE', "%{$search}%");
        })->orderBy($sort_by, $sort_dir)->get();


        foreach($assessments->toArray() as $assesment){
            $groups[$assesment['domain_id']][$assesment['core_id']][] = $assesment;
        }

        return response()->json([
            'status' => true,
            'data' => $groups,
            'message' => 'Manager  .'
        ], 200);

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
    public function finder(Request $request)
    {
        //
    }
}

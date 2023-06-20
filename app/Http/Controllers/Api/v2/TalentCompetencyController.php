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
use App\Models\JobInvitation;
use App\Models\AssesmentQuestion;
use App\Models\AssesmentTalentAnswer;

class TalentCompetencyController extends Controller
{
    public function index(Request $request){
        $user = $request->user();

        $vetting_interview = VettingSummary::where('talent_id', $user->id)
                                ->whereNotNull('interview_at')->latest()->get()->toArray();
        $invitation = JobInvitation::where('talent_user_id', $user->id)
                                ->whereNotNull('interview_at')->latest()->get()->toArray();

        $interview = array_merge($vetting_interview, $invitation);

        $assesments = Assesment::join('assesment_talent_answers',
                        'assesment_talent_answers.assesment_id', '=', 'assesments.id')
                    ->where('assesment_talent_answers.talent_id', $user->id)
                    ->orderBy('assesment_talent_answers.created_at','DESC')->get()
                    ->groupBy('assesment_talent_answers.assesment_id');

        // info([$assesments]);

        return response()->json([
            'status' => true,
            'data' => compact('interview', 'assesments'),
            'message' => '.'
        ], 200);
    }

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


        $cvSkills = CvSkill::join('vetting_summaries',
                    'cv_skills.id', '=', 'vetting_summaries.cv_skill')
                    // ->where('vetting_summaries.talent_id', $user->id)
                    ->where('cv_skills.cv_id', $cv->id)
                    ->where( function($query) use ($search) {
                        $query->where('cv_skills.id', 'LIKE', "%{$search}%");
                    })
                    ->get();

        foreach ($cvSkills as $assessment) {
            info([$assessment]);
            $assessment->total_questions = AssesmentQuestion::where('assesment_id', $assessment->assesment_id)->count();
            $assessment->answered = AssesmentTalentAnswer::where([ 'assesment_id' => $assessment->assesment_id, 'talent_id' => $user->id ])->count();
            $assessment->percentage = $assessment->answered ??( $assessment->answered / $assessment->total_questions  ) * 100;
        }

        $competency = croxxtalent_competency_tree($cvSkills->toArray());

        return response()->json([
            'status' => true,
            'data' => $competency,
            'message' => 'Talent Skill competency.'
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

        $experience =  Employee::where('user_id', $user->id)
                            ->with(['job_code','employer'])->get();

        foreach ($experience as $learn) {

            $summary =  Assesment::join('assesment_summaries',
                        'assesment_summaries.assesment_id', '=', 'assesments.id')
                        ->where('assesment_summaries.talent_id', $user->id)
                        ->where('assesments.admin_id' , $learn->employer_id)
                        ->get();


            foreach($summary as $assessment){
                $assessment->total_questions = AssesmentQuestion::where('assesment_id', $assessment->id)->count();
                $assessment->answered = AssesmentTalentAnswer::where([ 'assesment_id' => $assessment->id, 'talent_id' => $user->id ])->count();
                $assessment->percentage = $assessment->answered ?? ( $assessment->answered / $assessment->total_questions  ) * 100;
            }

            $learn->competence =  $summary;
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

        $competency = croxxtalent_competency_tree($assesments);

        return response()->json([
            'status' => true,
            'data' => $competency,
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

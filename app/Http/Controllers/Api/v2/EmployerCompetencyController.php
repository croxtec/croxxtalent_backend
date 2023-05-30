<?php

namespace App\Http\Controllers\Api\v2;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\User;
use App\Models\Employee;
use App\Models\AssesmentSummary;
use App\Models\Assesment;
use App\Models\VettingSummary;
use App\Models\EmployerJobcode as JobCode;

class EmployerCompetencyController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $groups = array();

        $per_page = $request->input('per_page', -1);
        $sort_by = $request->input('sort_by', 'created_at');
        $sort_dir = $request->input('sort_dir', 'asc');
        $search = $request->input('search');


        $companySkills = Assesment::
        where( function($query) use ($search) {
            $query->where('id', 'LIKE', "%{$search}%");
        })
        ->distinct('skill_id')
        ->select(['id', 'domain_id','core_id', 'code','skill_id'])
        ->get()->toArray();

        $competency = croxxtalent_competency_tree($companySkills);

        return response()->json([
            'status' => true,
            'data' => $competency,
            'message' => 'Data imported successfully.'
        ], 200);
    }

    public function competency(Request $request)
    {
        $user = $request->user();
        $groups = array();

        $per_page = $request->input('per_page', -1);
        $skill_gap = $request->input('skill_gap');
        $jobcode_gap = $request->input('jobcode_gap');
        $search = $request->input('search');

        $assessments = Assesment::join('assesment_summaries',
                    'assesment_summaries.assesment_id', '=', 'assesments.id')
                    // ->where('assesments.admin_id', $user->id)
                    ->when($skill_gap, function($query) use ($skill_gap){
                        info($skill_gap);
                        $query ->where('assesments.skill_id', $skill_gap);
                     })
                     ->when($jobcode_gap, function($query) use ($jobcode_gap){
                        // info($jobcode_gap);
                        $query ->where('assesments.job_code_id', $jobcode_gap);
                     })
                    ->get()->toArray();



        foreach($assessments as $skill){
            $groups[$skill['talent_id']]['assesments'][] = $skill;
        }

        foreach($groups as $key => $competency ){
            // info($competency);
            $groups[$key]['talent'] = Employee::where('user_id',$key)->first();
            $groups[$key]['info'] = [
                'total_assesments' =>  count($groups[$key]['assesments'])
            ];
        }

        $groups = array_values($groups);
        return response()->json([
            'status' => true,
            'data' => $groups,
            'message' => 'Data imported successfully.'
        ], 200);
    }
}

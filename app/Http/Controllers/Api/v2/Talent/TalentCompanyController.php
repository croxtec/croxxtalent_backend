<?php

namespace App\Http\Controllers\Api\v2\Talent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\Supervisor;

class TalentCompanyController extends Controller
{

    public function index(Request $request){

        $user = $request->user();
        $companies = Employee::where('user_id', $user->id)->get();
        $default_company =  null;

        if (count($companies)) {
            // Get the first company's employer_id as the default
            $firstCompanyEmployerId = $companies->first()->id;

            // Retrieve the id from the request or use the first company's id as default
            $defaultCompanyId = $request->input('company', $firstCompanyEmployerId);

            $default_company = $companies->firstWhere('id', $defaultCompanyId);
            if($default_company){
                $default_company->department;
                $default_company->department_role;
                $default_company->employer;
            }
        }

        return response()->json([
            'status' => true,
            'data' => compact('default_company','companies'),
            'message' => ''
        ], 200);
    }

    public function supervisor(Request $request){
        $user = $request->user();
        $companies = Employee::where('user_id', $user->id)->get();

        $default_company =  null;

        if (count($companies)) {
            // Get the first company's employer_id as the default
            $firstCompanyEmployerId = $companies->first()->id;

            // Retrieve the id from the request or use the first company's id as default
            $defaultCompanyId = $request->input('company', $firstCompanyEmployerId);

            $default_company = $companies->firstWhere('id', $defaultCompanyId);

            if($default_company->supervisor_id){
                $supervisor = Supervisor::where('supervisor_id', $default_company->supervisor_id)->first();

                return response()->json([
                    'status' => true,
                    'data' => compact('supervisor'),
                    'message' => ''
                ], 200);
            }
        }

        return response()->json([
            'status' => false,
            'message' => 'Supervisor not found'
        ], 404);

    }

}

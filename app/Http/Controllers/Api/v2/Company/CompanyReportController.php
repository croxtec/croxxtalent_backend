<?php

namespace App\Http\Controllers\Api\v2\Company;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Training\CroxxTraining;
use App\Models\Training\Employee;
use App\Models\Competency\DepartmentMapping as CompentencyMapping;

class CompanyReportController extends Controller
{

    public function insights(){
        $employer = $request->user();
        $default_company =  null;

        if($request->input('department')) {
            $user->default_company_id = $request->input('department');
            $user->save();
        }

        $competency_match  = CompentencyMapping::where('employer_id', $employer->id)
                                    ->whereNull('archived_at')->count();
        $employees = Employee::where('employer_id', $employer->id)
                        ->select(['id', 'gender'])->get();

        $total_employees = $employees->count();
        $genderCount = $employees->groupBy('gender')->map(function ($row) {
            return count($row);
        });

        $genderPercentage = $genderCount->map(function ($count) use ($totalEmployees) {
            return round(($count / $totalEmployees) * 100, 2) . '%';
        });

        $genderDistribution = $genderPercentage->toArray();



    }

}

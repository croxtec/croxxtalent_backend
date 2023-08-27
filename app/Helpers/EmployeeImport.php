<?php

namespace App\Helpers;

use App\Models\Skill;
use App\Models\SkillSecondary as Secondary;
use App\Models\SkillTertiary as Tertiary;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use App\Models\EmployerJobcode as JobCode;
use App\Models\Employee;

class EmployeeImport implements ToModel, WithHeadingRow
{
    protected $employer;

    public function __construct($employer){
        $this->employer = $employer;
    }

    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        $name = $row['name'];
        $email = $row['email'];
        $phone = $row['phone'];
        $code = $row['job_code'];
        $level = $row['level'];

        $isEmployer = Employee::where([
            'employer_id' => $this->employer->id,
            'email' =>  $email,
        ])->first();

        if(!$isEmployer){
            $jobCode = JobCode::firstOrCreate([
                'employer_id' => $this->employer->id,
                'job_code' => $code
            ]);

            $data = [
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                // 'level' => $level,
                'employer_id' => $this->employer->id,
                'job_code_id' => $jobCode->id
            ];
            return Employee::create($data);
        }

        return $isEmployer;
    }
}

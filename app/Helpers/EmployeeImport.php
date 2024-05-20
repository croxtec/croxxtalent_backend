<?php

namespace App\Helpers;

use App\Models\Skill;
use App\Models\SkillSecondary as Secondary;
use App\Models\SkillTertiary as Tertiary;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use App\Models\Employee;
use App\Mail\WelcomeEmployee;
use Illuminate\Support\Facades\Mail;
use App\Models\Verification;
use App\Models\EmployerJobcode as Department;
use App\Models\DepartmentRole;
use App\Models\Supervisor;

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
        $name = isset($row['employee_name']) ? $row['employee_name'] : null;
        $email = isset($row['official_email']) ? $row['official_email'] : null;
        $phone = isset($row['phone']) ? $row['phone'] : null;
        $department = isset($row['department']) ? $row['department'] : null;
        $role = isset($row['job_rolecode']) ? $row['job_rolecode'] : null;
        $level = isset($row['level']) ? $row['level'] : null;
        $location = isset($row['Location']) ? $row['Location'] : null;
        $supervisor = isset($row['supervisor']) ? $row['supervisor'] : null;
        // info($row);
        // info([$name , $email , $phone , $department , $role , $level]);

        if($name && $email && $phone && $department && $role && $level){

            $isEmployer = Employee::where([
                'employer_id' => $this->employer->id,
                'email' =>  $email,
            ])->first();


            if(!$isEmployer){
                $data = [
                    'employer_id' => $this->employer->id,
                    'name' => $name,
                    'email' => $email,
                    'phone' => $phone,
                    'level' => $level,
                    'location' => $location,
                ];

                if(isset($department)){
                    $department = Department::firstOrCreate([
                        'employer_id' => $this->employer->id,
                        'job_code' => $department
                    ]);
                    $data['job_code_id'] = $department->id;
                }

                if (isset($role)) {
                    $department_role = DepartmentRole::firstOrCreate([
                        'employer_id' => $this->employer->id,
                        'department_id' => $data['job_code_id'],
                        'name' => $role
                    ]);

                    $data['department_role_id'] = $department_role->id;
                }


                $employee = Employee::create($data);
                // info(['EMployee Created ', $employee]);

                if($employee){
                    $verification = new Verification();
                    $verification->action = "employee";
                    $verification->sent_to = $email;
                    $verification->metadata = null;
                    $verification->is_otp = false;
                    // $verification = $employee->verifications()->save($verification);
                    // if ($verification) {
                    //     // Mail::to($email)->send(new WelcomeEmployee($employee, $this->employer, $verification));
                    // }
                }

                if(isset($supervisor)){
                    if(strtolower($supervisor) == 'yes'){
                        $employee = Employee::where('id', $employee->id)->first();
                        $isSupervisor = Supervisor::where('supervisor_id', $employee->id)
                                                ->where('employer_id', $this->employer->id)->first();

                        if(!$isSupervisor){
                            $supervisor =  Supervisor::create([
                                'type' => 'department',
                                'employer_id' => $this->employer->id,
                                'supervisor_id'=> $employee->id,
                                'department_id' => $data['job_code_id']
                            ]);

                            $employee->supervisor_id = $supervisor->id;
                            $employee->save();
                        }
                    }
                }

                return $employee;
            }

            if(isset($supervisor)){
                if(strtolower(trim($supervisor)) == 'yes '){
                    $isSupervisor = Supervisor::where('supervisor_id',  $isEmployer->id)
                                            ->where('employer_id', $this->employer->id)->first();

                    if(!$isSupervisor){
                        $supervisor =  Supervisor::create([
                            'employer_id' => $this->employer->id,
                            'supervisor_id'=> $isEmployer->id,
                            'department_id' => $isEmployer->job_code_id,
                            'type' => 'department'
                        ]);

                        // info(['Supervisor Created ', $supervisor]);
                    }
                }
            }
            return $isEmployer;
        } else {
            return null;
        }


    }
}

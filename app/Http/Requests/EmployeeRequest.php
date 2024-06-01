<?php

namespace App\Http\Requests;

use App\Models\Employee;
use Illuminate\Foundation\Http\FormRequest;
use App\Models\SUpervisor;
use Illuminate\Validation\Rule;

class EmployeeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        switch($this->method()) {
            case 'GET':
                return false;
            case 'POST':
                return true;
                // return $this->user()->can('create', Employee::class);
            case 'PUT':
            case 'PATCH':
                return true;
                // $employee = Employee::findOrFail($this->id);
                // return $this->user()->can('update', [Employee::class, $employee]);
            case 'DELETE':
                return false;
            default:break;
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        switch($this->method()) {
            case 'GET':
                return [];
            case 'POST':
                return [
                    'name' => 'required|max:100',
                    'email' => 'required|max:100',
                    'phone' => 'required|max:100',
                    'level' => 'required|in:beginner,intermediate,advance,expert',
                    'job_code_id' => [
                        Rule::requiredIf(function () {
                            return !request()->has('job_code');
                        }),
                        'exists:employer_jobcodes,id'
                    ],
                    'department_role_id' => [
                        Rule::requiredIf(function () {
                            return !request()->has('department_role');
                        }),
                        'exists:department_roles,id'
                    ],
                    'location' => 'nullable|min:5|max:256',
                    'job_code' => 'nullable|string|min:3|max:56',
                    'department_role' => 'nullable|string|min:3|max:56'
                ];
            case 'PUT':
            case 'PATCH':
                return [
                    'name' => 'sometimes|required|max:100',
                    'phone' => 'sometimes|required|max:100',
                    'level' => 'sometimes|required|in:beginner,intermediate,advance,expert',
                    'location' => 'nullable',
                    'work_type' =>  'nullable|in:contract,fulltime,parttime,internship',
                    'gender' =>  'nullable',
                    'language' =>  'nullable',
                    'language' =>  'nullable',
                    'hired_date' =>  'nullable|date',
                    'birth_date' =>  'nullable|date',
                ];
            default:break;
        }
    }

      /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            // 'name.unique' => 'The name already exist.',
        ];
    }
}

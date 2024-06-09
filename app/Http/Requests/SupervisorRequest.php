<?php

namespace App\Http\Requests;

use App\Models\Supervisor;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class SupervisorRequest extends FormRequest
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
                    'supervisor_id' => 'required|exists:employees,id',
                    'type' => 'required|in:department,role,employees',
                    'department_id' => 'required_if:type,department,role|exists:employer_jobcodes,id',
                    'department_role_id' =>  'required_if:type,role|exists:department_roles,id'
                ];
            case 'PUT':
            case 'PATCH':
                return [
                    'name' => 'required|max:100',
                    'phone' => 'required|max:100',
                    'job_code_id' => 'required|exists:employer_jobcodes,id',
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

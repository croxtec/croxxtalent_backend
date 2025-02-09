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
                    'project_id' => 'required|integer|exists:projects,id',
                    'milestone_id' => [
                        Rule::requiredIf(function () {
                            return !request()->has('milestone');
                        }),
                        'nullable',
                        'exists:milestones,id'
                    ],
                    'milestone' => 'nullable|string|min:3|max:56',
                    'title' => 'required|max:100',
                    'metric' => 'nullable|max:2048',
                    'status' => 'required|in:to-do,in-progress,in-review,rework,completed',
                    'priority_level' => 'nullable|in:low,medium,high,urgent',
                    // 'attachment' => 'nullable|mimetypes:video/mp4|max:61440',
                ];
            case 'PATCH':
                return [
                    'project_id' => 'sometimes|integer|exists:projects,id',
                    'milestone_id' => 'nullable|exists:milestones,id',
                    'milestone' => 'nullable|string|min:3|max:56',
                    'title' => 'sometimes|required|max:100',
                    'metric' => 'nullable|max:2048',
                    'status' => 'sometimes|required|in:to-do,in-progress,in-review,rework,completed',
                    'priority_level' => 'nullable|in:low,medium,high,urgent',
                ];
            case 'DELETE':
                return [];
            default:
                return [];
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

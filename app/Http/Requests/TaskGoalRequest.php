<?php

namespace App\Http\Requests;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class TaskGoalRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
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
                    'status' => 'required|in:to-do,in-progress',//in-review,rework,completed
                    'priority_level' => 'nullable|in:low,medium,high,urgent',
                    'assigned.*' => 'integer|exists:employees,id',
                    // 'attachment' => 'nullable|mimetypes:video/mp4|max:61440', // 60MB in kilobytes
                ];
            case 'PATCH':
                return [
                    'milestone_id' => ['nullable','exists:milestones,id'],
                    'milestone' => 'nullable|string|min:3|max:56',
                    'title' => 'nullable|max:100',
                    'metric' => 'nullable|max:2048',
                    'status' => 'nullable|in:to-do,in-progress,in-review,rework,completed',
                    'priority_level' => 'nullable|in:low,medium,high,urgent',
                ];
            case 'DELETE':
                return [];
            default:
                break;
        }
    }
}

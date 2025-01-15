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
        return [
            'project_id' => 'required|integer|exists:projects,id',
            'milestone_id' => [
                Rule::requiredIf(function () {
                    return !request()->has('milestone');
                }),
                'nullable',
                'exists:employer_jobcodes,id'
            ],
            'milestone' => 'nullable|string|min:3|max:56',
            'title' => 'required|max:100',
            'metric' => 'required|min:20|max:2048',
            'status' => 'required|in:to-do,in-progress',//in-review,rework,completed
            'priority_level' => 'nullable|low,medium,high,urgent',
            // 'attachment' => 'nullable|mimetypes:video/mp4|max:61440', // 60MB in kilobytes
        ];
        // switch($this->method()) {
        //     case 'GET':
        //         return [];
        //     case 'POST':
        //     case 'DELETE':
        //         return [];
        //     default:
        //         break;
        // }
    }
}

<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProjectRequest extends FormRequest
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
                //$this->user()->can('create',Assesment::class);
            case 'PUT':
            case 'PATCH':
                // $assessment = Assessment::findOrFail(1);
                // return $this->user()->can('update',[Assessment::class,$assessment]);
            case 'DELETE':
                return false;
            default:break;
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string,mixed>
     */
    public function rules()
    {
        switch($this->method()) {
            case 'GET':
                return [];
            case 'POST':
            case 'PUT':
                return[
                    'title' => 'required|max:100',
                    'description' => 'required|max:400',
                    'start_date' => 'required|date',
                    'end_date' => 'required|date',
                    'department_id' => 'required|integer|exists:employer_jobcodes,id',
                    'budget' => 'nullable|numeric',
                    'resource_allocation' => 'nullable|integer',
                    'category' => 'nullable|string',
                    'priority_level' => 'required|in:low,medium,high,urgent',
                    'team_members.*' => 'integer|exists:employees,id',
                    'team_leads.*' => 'nullable|integer|exists:employees,id',
                ];
            case 'PATCH':
                return [
                    'title' => 'sometimes|required|max:100',
                    'description' => 'sometimes|required|max:550',
                    'start_date' => 'sometimes|required|date',
                    'end_date' => 'sometimes|required|date',
                    'department_id' => 'sometimes|required|integer|exists:employer_jobcodes,id',
                    'resource_allocation' => 'sometimes|nullable|integer',
                    'category' => 'sometimes|required|string',
                    'priority_level' => 'sometimes|nullable|in:low,medium,high,urgent',
                ];
            case 'DELETE':
                return [];
            default:break;
        }
     }
}




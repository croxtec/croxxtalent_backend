<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MilestoneRequest extends FormRequest
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
                //$this->user()->can('create', Assesment::class);
            case 'PUT':
            case 'PATCH':
                // return $this->user()->can('update', [Assessment::class, $assessment]);
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
                    'milestone_name' => 'required|max:100',
                    'description' => 'required|max:550',
                    'start_date' => 'nullable|date',
                    'end_date' => 'nullable|date',
                    'priority_level' => 'nullable|low,medium,high,urgent',
                ];
            case 'PUT':
            case 'PATCH':
                return [
                    'milestone_name' => 'sometimes|required|max:100',
                    'description' => 'sometimes|required|max:550',
                    'start_date' => 'sometimes|required|date',
                    'end_date' => 'sometimes|required|date',
                    'project_id' => 'sometimes|required|integer|exists:projects,id',
                    'priority_level' => 'nullable|low,medium,high,urgent',
                ];
            case 'DELETE':
                return [];
            default:break;
        }
     }
}

<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TrainingRequest extends FormRequest
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
                return  true;
                // $assessment = Assessment::findOrFail(1);
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
                    'type' => 'required|in:company,training,competency',
                    'experience_level' => 'required|in:beginner,intermediate,advance,expert',
                    'title' => 'required|max:100',
                    'objective' => 'required|max:250',
                    'assessment_level' => 'nullable',
                    'department_id' => 'required_if:type,company|integer|exists:employer_jobcodes,id',
                    'career_id' => 'required_if:type,training,competency|integer',
                ];

            case 'PUT':
            case 'PATCH':
                return [
                    'experience_level' => 'sometimes|required|in:beginner,intermediate,advance,expert',
                    'title' => 'sometimes|required|max:100',
                    'objective' => 'sometimes|required|max:250',
                    'assessment_level' => 'nullable',
                ];
            case 'DELETE':
                return [];
            default:break;
        }
    }
}

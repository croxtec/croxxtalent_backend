<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EvaluationAssessmentRequest extends FormRequest
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
                return true;//$this->user()->can('create', Assesment::class);
            case 'PUT':
            case 'PATCH':
                return  true;
                $assessment = Assessment::findOrFail(1);
                return $this->user()->can('update', [Assessment::class, $assessment]);
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
                    'type' => 'required|in:company,vetting',
                    'category' => 'required|in:peer_review,experience',
                    'level' => 'required|in:beginner,intermediate,advance,expert',
                    'name' => 'required|max:100',
                    'description' => 'nullable|max:250',
                    'validity_period' => 'nullable|date',
                    'expected_score' => 'required|integer',

                    'questions' => 'required|array',
                    'questions.*.question' => 'required|min:10',
                    'questions.*.desctiption' => 'nullable',
                    // 'career_id'
                    'employees.*' => 'required_if:type,company|integer|exists:employees,id',
                    'supervisors.*' => 'required_if:type,company|integer|exists:employees,id',
                    'delivery_type' => 'nullable|in:quiz,classroom,on_the_job,assessment,experience,exam,external',
                ];
            case 'PUT':
            case 'PATCH':
                return [];
            case 'DELETE':
                return [];
            default:break;
        }
    }
}

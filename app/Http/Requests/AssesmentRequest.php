<?php

namespace App\Http\Requests;

use App\Models\Assesment;
use Illuminate\Foundation\Http\FormRequest;

class AssesmentRequest extends FormRequest
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
                $assesment = Assesment::findOrFail(1);
                return $this->user()->can('update', [Assesment::class, $assesment]);
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
                    // 'employer_id' => 'required|exists:users,id',
                    'domain_id' => 'required|exists:skills,id',
                    'core_id' => 'required',
                    'skill_id' => 'required',
                    'level' => 'required|max:100',
                    'name' => 'required|max:100',
                    'description' => 'nullable|max:250',
                    'category' => 'required|in:hse,vetting,job specific,generic',
                    'delivery_type' => 'required|in:quiz,classroom,on the job,assessment,experience,exam,external',
                    'validity_period' => 'nullable',
                    'expected_score' => 'nullable',
                    'questions' => 'required|array',

                    'questions.*.type' => 'required|in:text,reference,radio,checkbox,file',
                    'questions.*.question' => 'required',
                    'questions.*.desctiption' => 'nullable',
                    'questions.*.option1' => 'nullable|max:50',
                    'questions.*.option2' => 'nullable|max:50',
                    'questions.*.option3' => 'nullable|max:50',
                    'questions.*.option4' => 'nullable|max:50',

                    'job_code_id' => 'required_if:category,job specific',
                    'candidates' => 'required_if:category,generic',
                    'managers' => 'required_if:category,generic',
                    'job_code_id' => 'integer|exists:employer_jobcodes,id',
                    'candidates.*' => 'integer|exists:employees,id',
                    'managers.*' => 'integer|exists:employees,id',
                ];
            case 'PUT':
            case 'PATCH':
                return [
                    // 'employer_id' => 'required|exists:users,id',
                    'domain_id' => 'required|exists:skills,id',
                    'core_id' => 'required',
                    'skill_id' => 'required',
                    'level' => 'required|max:100',
                    'name' => 'required|max:100',
                    'description' => 'nullable|max:250',
                    'category' => 'required|in:hse,vetting,job specific,generic',
                    'delivery_type' => 'required|in:quiz,classroom,on the job,assessment,experience,exam,external',
                    'validity_period' => 'nullable',
                    'expected_score' => 'nullable',

                    'job_code_id' => 'required_if:category,job specific',
                    'candidates' => 'required_if:category,generic',
                    'managers' => 'required_if:category,generic',
                    'job_code_id.*' => 'integer|exists:employer_jobcodes,id',
                    'candidates.*' => 'integer|exists:employees,id',
                    'managers.*' => 'integer|exists:employees,id',
                ];
            case 'DELETE':
                return [];
            default:break;
        }
    }

    public function messages()
    {
        return [
            // 'questions.*.type'
        ];
    }
}

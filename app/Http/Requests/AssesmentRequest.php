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
                    'job_code_id' => 'required',
                    // 'skill_ids.*' => 'required',

                    'name' => 'required|max:100',
                    'description' => 'nullable',
                    'category' => 'required|in:hse,assesment,job specific, generic',
                    'delivery_type' => 'required|Classroom, On-the-job, Assessment, Experience, Exam, External',
                    'validity_period' => 'nullable',
                    'expected_score' => 'nullable',
                    'questions' => 'required|array',

                    'questions.*.type' => 'required',
                    'questions.*.question' => 'required',
                    'questions.*.desctiption' => 'nullable',
                    'questions.*.option1' => 'nullable|max:50',
                    'questions.*.option2' => 'nullable|max:50',
                    'questions.*.option3' => 'nullable|max:50',
                    'questions.*.option4' => 'nullable|max:50',
                ];
            case 'PUT':
            case 'PATCH':
                return [
                    // 'employer_id' => 'required|exists:users,id',
                    'domain_id' => 'required|exists:skills,id',
                    'core_id' => 'required',
                    'skill_id' => 'required',
                    'level' => 'required|max:100',
                    'job_code_id' => 'required',
                    // 'skill_ids.*' => 'required',

                    'name' => 'required|max:100',
                    'description' => 'nullable',
                    'category' => 'required',
                    'validity_period' => 'nullable',
                    'delivery_type' => 'required',
                    'expected_score' => 'nullable',
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

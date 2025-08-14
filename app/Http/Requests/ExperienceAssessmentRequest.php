<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExperienceAssessmentRequest extends FormRequest
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
        switch ($this->method()) {
            case 'GET':
                return [];
            case 'POST':
                return [
                    'type'            => 'required|in:company,supervisor,vetting',
                    'category'        => 'required|in:peer_review,experience',
                    'level'           => 'required|in:beginner,intermediate,advance,expert',
                    'name'            => 'required|max:100',
                    'description'     => 'nullable|max:512',
                    'validity_period' => 'nullable|date',
                    'expected_score'  => 'required|integer',
                    'delivery_type'   => 'nullable|in:quiz,classroom,on_the_job,assessment,experience,exam,external',
                    'is_published'    => 'required|boolean',

                    'competency_ids.*'         => 'required|integer|exists:department_mappings,id',
                    'questions'                => 'required|array',
                    'questions.*.question'     => 'required|min:10',
                    'questions.*.document' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,txt,jpg,jpeg,png|max:10240', // 10MB max
                    'questions.*.desctiption'  => 'nullable|max:712',

                    // For company/supervisor (non-peer_review), we still expect employees:required_if:type,company,supervisor|
                    'employees'     => 'required_if:category,experience|array',
                    'employees.*'   => 'integer|exists:employees,id',

                    // For peer_review, we now require a structured array of employees_reviewers
                    'employees_reviewers'                    => 'required_if:category,peer_review|array',
                    'employees_reviewers.*.employee_id'       => 'required|integer|exists:employees,id',
                    'employees_reviewers.*.reviewers'         => 'required|array',
                    'employees_reviewers.*.reviewers.*'       => 'required|integer|exists:employees,id',

                    'department_id'       => 'required_if:type,company|integer',
                    'career_id'           => 'required_if:type,vetting,competency_match|integer',
                    'supervisor_id'       => 'required_if:type,supervisor|integer|exists:employees,id',
                    'department_role_id'  => 'nullable|integer',
                ];
            case 'PUT':
                return [
                    'level'                 => 'sometimes|required|in:beginner,intermediate,advance,expert',
                    'assessment_name'       => 'sometimes|required|max:100',
                    'assessment_description'=> 'nullable|max:400',
                    'validity_period'       => 'nullable|date',
                    'expected_score'        => 'sometimes|required|integer',
                ];
            case 'PATCH':
            case 'DELETE':
                return [];
            default:
                break;
        }
    }

}

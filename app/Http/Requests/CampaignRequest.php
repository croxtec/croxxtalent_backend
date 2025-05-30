<?php

namespace App\Http\Requests;

use App\Models\Campaign;
use Illuminate\Foundation\Http\FormRequest;

class CampaignRequest extends FormRequest
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
                return $this->user()->can('create', Campaign::class);
            case 'PUT':
            case 'PATCH':
                $campaign = Campaign::findOrFail($this->id);
                return $this->user()->can('update', [Campaign::class, $campaign]);
            case 'DELETE':
                return false;
            default:break;
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        switch($this->method()) {
            case 'GET':
                return [];
            case 'POST':
                return [
                    // 'user_id' => 'required|exists:users,id',
                    'title' => 'required|max:100',
                    'industry_id' => 'nullable|exists:industries,id',
                    'job_title' => 'required',
                    'work_type' => 'required',
                    // 'job_title_id' => 'required|exists:job_titles,id',
                    // 'core_id' => 'required',
                    'department_id' => 'required|exists:employer_jobcodes,id',
                    'skill_ids.*' => 'required|exists:department_mappings,id',
                    'course_of_study_ids.*' => 'nullable|exists:course_of_studies,id',
                    'language_ids.*' => 'nullable|exists:languages,id',
                    'minimum_degree_id' => 'nullable|exists:degrees,id',
                    'currency_code' => 'nullable',
                    'min_salary' => 'nullable',
                    'max_salary' => 'nullable',
                    'is_confidential_salary' => 'boolean',
                    'years_of_experience' => 'nullable|integer',
                    'number_of_positions' => 'nullable|integer',
                    'expire_at' => 'required|date',
                    'city' => 'required|max:255',
                    'state_id' => 'required|exists:states,id',
                    'country_code' => 'required|exists:countries,code',
                    'summary' => 'required|max:1024',
                    'description' => 'required',
                    'interview' => 'nullable',
                    'is_managed' => 'boolean',
                ];
            case 'PUT':
            case 'PATCH':
                return [
                    'user_id' => 'required|exists:users,id',
                    'title' => 'required|max:100',
                    'industry_id' => 'required|exists:industries,id',
                    'job_title' => 'required',
                    'work_type' => 'required',
                    'experience_level' => 'required',
                    'work_site' => 'nullable',
                    // 'job_title_id' => 'required|exists:job_titles,id',
                    // 'domain_id' => 'required|exists:skills,id',
                    // 'core_id' => 'required',
                    // 'years_of_experience' => 'required',
                    'skill_ids.*' => 'required',
                    'course_of_study_ids.*' => 'required|exists:course_of_studies,id',
                    'language_ids.*' => 'required|exists:languages,id',
                    'minimum_degree_id' => 'required|exists:degrees,id',
                    'currency_code' => 'nullable',
                    'min_salary' => 'nullable',
                    'max_salary' => 'nullable',
                    'is_confidential_salary' => 'nullable|boolean',
                    'number_of_positions' => 'required',
                    'expire_at' => 'required|date',
                    'city' => 'required|max:255',
                    'state_id' => 'required|exists:states,id',
                    'country_code' => 'required|exists:countries,code',
                    'summary' => 'required|max:500',
                    'description' => 'nullable',
                    'interview' => 'nullable',
                    'is_managed' => 'boolean',
                ];
            case 'DELETE':
                return [];
            default:break;
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
            'user_id.exists' => 'User not found.',
        ];
    }
}

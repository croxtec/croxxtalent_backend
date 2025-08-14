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
                return true; // Authorization is handled in the controller
                // $campaign = Campaign::findOrFail($this->id);
                // return $this->user()->can('update', [Campaign::class, $campaign]);
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
                return $this->getCreateRules();
                
            case 'PUT':
            case 'PATCH':
                return $this->getUpdateRules();
                
            case 'DELETE':
                return [];
                
            default:
                return [];
        }
    }

    private function getCreateRules()
    {
        return [
            'title' => 'required|max:100',
            'industry_id' => 'nullable|exists:industries,id',
            'job_title' => 'required|string|max:255',
            'work_type' => 'required|string',
            'department_id' => 'required|exists:employer_jobcodes,id',
            'skill_ids' => 'required|array|min:1',
            'skill_ids.*' => 'required|exists:department_mappings,id',
            'course_of_study_ids' => 'nullable|array',
            'course_of_study_ids.*' => 'exists:course_of_studies,id',
            'language_ids' => 'nullable|array',
            'language_ids.*' => 'exists:languages,id',
            'minimum_degree_id' => 'nullable|exists:degrees,id',
            'currency_code' => 'nullable|string|max:3',
            'min_salary' => 'nullable|numeric|min:0',
            'max_salary' => 'nullable|numeric|min:0|gte:min_salary',
            'is_confidential_salary' => 'boolean',
            'years_of_experience' => 'required|integer|min:0',
            'number_of_positions' => 'required|integer|min:1',
            'expire_at' => 'required|date|after:today',
            'city' => 'required|string|max:255',
            'state_id' => 'required|exists:states,id',
            'country_code' => 'required|exists:countries,code',
            'summary' => 'required|string|max:1024',
            'description' => 'required|string',
            'interview' => 'nullable|string',
            'is_managed' => 'boolean',
        ];
    }

    private function getUpdateRules()
    {
        $campaign = $this->route('campaign');
        
        // If campaign is published, use restricted rules
        if ($campaign && $this->isCampaignPublished($campaign)) {
            return $this->getPublishedCampaignRules();
        }
        
        // For draft campaigns, use full validation (same as create but optional department/skills)
        $rules = $this->getCreateRules();
        
        // Make some fields optional for draft updates
        $rules['skill_ids'] = 'nullable|array';
        $rules['department_id'] = 'nullable|exists:employer_jobcodes,id';
        $rules['years_of_experience'] = 'nullable|integer|min:0';
        $rules['expire_at'] = 'required|date|after_or_equal:today'; // Allow today for updates
        
        return $rules;
    }

    private function getPublishedCampaignRules()
    {
        return [
            // Core campaign details - NOT ALLOWED for published campaigns
            'title' => 'prohibited',
            'industry_id' => 'prohibited', 
            'job_title' => 'prohibited',
            'work_type' => 'prohibited',
            'department_id' => 'prohibited',
            'skill_ids' => 'prohibited',
            'course_of_study_ids' => 'prohibited',
            'language_ids' => 'prohibited',
            'minimum_degree_id' => 'prohibited',
            'currency_code' => 'prohibited',
            
            // Salary and logistics - ALLOWED for published campaigns
            'min_salary' => 'nullable|numeric|min:0',
            'max_salary' => 'nullable|numeric|min:0|gte:min_salary',
            'is_confidential_salary' => 'boolean',
            'years_of_experience' => 'required|integer|min:0',
            'number_of_positions' => 'required|integer|min:1',
            'expire_at' => 'required|date|after_or_equal:today',
            'city' => 'required|string|max:255',
            'state_id' => 'required|exists:states,id',
            'country_code' => 'required|exists:countries,code',
            'summary' => 'required|string|max:1024',
            'description' => 'required|string',
            'interview' => 'nullable|string',
            'is_managed' => 'boolean',
        ];
    }

    private function isCampaignPublished($campaignId)
    {
        $campaign = Campaign::find($campaignId);
        return $campaign && (
            $campaign->status === 'published' || 
            $campaign->is_published === true ||
            $campaign->published_at !== null
        );
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

    // public function messages()
    // {
    //     return [
    //         'title.prohibited' => 'Campaign title cannot be changed after publishing.',
    //         'job_title.prohibited' => 'Job title cannot be changed after publishing.',
    //         'industry_id.prohibited' => 'Industry cannot be changed after publishing.',
    //         'work_type.prohibited' => 'Work type cannot be changed after publishing.',
    //         'department_id.prohibited' => 'Department cannot be changed after publishing.',
    //         'skill_ids.prohibited' => 'Skills cannot be changed after publishing.',
    //         'course_of_study_ids.prohibited' => 'Course of studies cannot be changed after publishing.',
    //         'language_ids.prohibited' => 'Languages cannot be changed after publishing.',
    //         'minimum_degree_id.prohibited' => 'Minimum degree cannot be changed after publishing.',
    //         'currency_code.prohibited' => 'Currency cannot be changed after publishing.',
    //         'max_salary.gte' => 'Maximum salary must be greater than or equal to minimum salary.',
    //         'expire_at.after' => 'Expiry date must be in the future.',
    //         'expire_at.after_or_equal' => 'Expiry date cannot be in the past.',
    //     ];
    // }
}

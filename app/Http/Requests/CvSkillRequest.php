<?php

namespace App\Http\Requests;

use App\Models\Cv;
use App\Models\CvSkill;
use Illuminate\Foundation\Http\FormRequest;

class CvSkillRequest extends FormRequest
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
                $cvSkill = CvSkill::findOrFail($this->id);
                $cv = Cv::findOrFail($cvSkill->cv_id);
                return $this->user()->can('view', [Cv::class, $cv]);
            case 'POST':
                return $this->user()->can('create', Cv::class);
            case 'PUT':
            case 'PATCH':
                $cvSkill = CvSkill::findOrFail($this->cv_skill_id);
                $cv = Cv::findOrFail($cvSkill->cv_id);
                return $this->user()->can('update', [Cv::class, $cv]);
            case 'DELETE':
                $cvSkill = CvSkill::findOrFail($this->id);
                $cv = Cv::findOrFail($cvSkill->cv_id);
                return $this->user()->can('delete', [Cv::class, $cv]);
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
                    'domain_id' => 'required|exists:skills,id',
                    'core_id' => 'required|exists:skill_secondaries,id',
                    'skill_id' => 'required|exists:skill_tertiaries,id',
                    'level' => 'required|max:50|in:basic,intermediate,advance,expert',
                ];
            case 'PUT':
            case 'PATCH':
                return [
                    'domain_id' => 'required|exists:skills,id',
                    'core_id' => 'required|exists:skill_secondaries,id',
                    'skill_id' => 'required|exists:skill_tertiaries,id',
                    'level' => 'required|max:50|in:basic,intermediate,advance,expert',
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
            'domain_id.required' => 'Domain field is required',
            'domain_id.exists' => 'Domain not found.',

            'core_id.required' => 'Core field is required',
            'core_id.exists' => 'Core not found.',

            'skill_id.required' => 'Skill field is required',
            'skill_id.exists' => 'Skill not found.',

        ];
    }
}

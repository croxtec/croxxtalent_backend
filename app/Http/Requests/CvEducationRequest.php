<?php

namespace App\Http\Requests;

use App\Models\Cv;
use App\Models\CvEducation;
use Illuminate\Foundation\Http\FormRequest;

class CvEducationRequest extends FormRequest
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
                $cvEducation = CvEducation::findOrFail($this->id);
                $cv = Cv::findOrFail($cvEducation->cv_id);
                return $this->user()->can('view', [Cv::class, $cv]);
            case 'POST':
                return $this->user()->can('create', Cv::class);
            case 'PUT':
            case 'PATCH':
                $cvEducation = CvEducation::findOrFail($this->id);
                $cv = Cv::findOrFail($cvEducation->cv_id);
                return $this->user()->can('update', [Cv::class, $cv]);
            case 'DELETE':
                $cvEducation = CvEducation::findOrFail($this->id);
                $cv = Cv::findOrFail($cvEducation->cv_id);
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
                    'school' => 'required|max:100',
                    'course_of_study_id' => 'required|exists:course_of_studies,id',
                    'degree_id' => 'required|exists:degrees,id',
                    'city' => 'required|max:255',
                    'country_code' => 'required|exists:countries,code',
                    'start_date' => 'required|date',
                    'end_date' => 'nullable|date',
                    'is_current' => 'boolean',
                    'description' => 'nullable|max:500',
                ];
            case 'PUT':
            case 'PATCH':
                return [
                    'school' => 'required|max:100',
                    'course_of_study_id' => 'required|exists:course_of_studies,id',
                    'degree_id' => 'required|exists:degrees,id',
                    'city' => 'required|max:255',
                    'country_code' => 'required|exists:countries,code',
                    'start_date' => 'required|date',
                    'end_date' => 'nullable|date',
                    'is_current' => 'boolean',
                    'description' => 'nullable|max:500',
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

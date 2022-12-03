<?php

namespace App\Http\Requests;

use App\Models\Cv;
use App\Models\CvCertification;
use Illuminate\Foundation\Http\FormRequest;

class CvCertificationRequest extends FormRequest
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
                $cvCertification = CvCertification::findOrFail($this->id);
                $cv = Cv::findOrFail($cvCertification->cv_id);
                return $this->user()->can('view', [Cv::class, $cv]);
            case 'POST':
                return $this->user()->can('create', Cv::class);
            case 'PUT':
            case 'PATCH':
                $cvCertification = CvCertification::findOrFail($this->id);
                $cv = Cv::findOrFail($cvCertification->cv_id);
                return $this->user()->can('update', [Cv::class, $cv]);
            case 'DELETE':
                $cvCertification = CvCertification::findOrFail($this->id);
                $cv = Cv::findOrFail($cvCertification->cv_id);
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
                    'institution' => 'required|max:100',
                    'certification_course_id' => 'required|exists:certification_courses,id',
                    'start_date' => 'required|date',
                    'end_date' => 'nullable|date',
                    'is_current' => 'boolean',
                ];
            case 'PUT':
            case 'PATCH':
                return [
                    'institution' => 'required|max:100',
                    'certification_course_id' => 'required|exists:certification_courses,id',
                    'start_date' => 'required|date',
                    'end_date' => 'nullable|date',
                    'is_current' => 'boolean',
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

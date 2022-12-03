<?php

namespace App\Http\Requests;

use App\Models\Cv;
use App\Models\CvAward;
use Illuminate\Foundation\Http\FormRequest;

class CvAwardRequest extends FormRequest
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
                $cvAward = CvAward::findOrFail($this->id);
                $cv = Cv::findOrFail($cvAward->cv_id);
                return $this->user()->can('view', [Cv::class, $cv]);
            case 'POST':
                return $this->user()->can('create', Cv::class);
            case 'PUT':
            case 'PATCH':
                $cvAward = CvAward::findOrFail($this->id);
                $cv = Cv::findOrFail($cvAward->cv_id);
                return $this->user()->can('update', [Cv::class, $cv]);
            case 'DELETE':
                $cvAward = CvAward::findOrFail($this->id);
                $cv = Cv::findOrFail($cvAward->cv_id);
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
                    'title' => 'required|max:50',
                    'organization' => 'required|max:100',
                    'date' => 'required|date',
                    'description' => 'nullable|max:500',
                ];
            case 'PUT':
            case 'PATCH':
                return [
                    'title' => 'required|max:50',
                    'organization' => 'required|max:100',
                    'date' => 'required|date',
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

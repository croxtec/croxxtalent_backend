<?php

namespace App\Http\Requests;

use App\Models\Cv;
use App\Models\CvReference;
use Illuminate\Foundation\Http\FormRequest;

class CvReferenceRequest extends FormRequest
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
                $cvReference = CvReference::findOrFail($this->id);
                $cv = Cv::findOrFail($cvReference->cv_id);
                return $this->user()->can('view', [Cv::class, $cv]);
            case 'POST':
                return $this->user()->can('create', Cv::class);
            case 'PUT':
            case 'PATCH':
                $cvReference = CvReference::findOrFail($this->id);
                $cv = Cv::findOrFail($cvReference->cv_id);
                return $this->user()->can('update', [Cv::class, $cv]);
            case 'DELETE':
                $cvReference = CvReference::findOrFail($this->id);
                $cv = Cv::findOrFail($cvReference->cv_id);
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
                    'name' => 'required|max:50',
                    'company' => 'required|max:100',
                    'position' => 'required|max:50',
                    'email' => 'required|email|max:150',
                    'phone' => 'required|max:25',
                    'description' => 'nullable|max:500',
                ];
            case 'PUT':
            case 'PATCH':
                return [
                    'name' => 'required|max:50',
                    'company' => 'required|max:100',
                    'position' => 'required|max:50',
                    'email' => 'required|email|max:150',
                    'phone' => 'required|max:25',
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

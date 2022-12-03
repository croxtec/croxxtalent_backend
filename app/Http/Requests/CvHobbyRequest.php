<?php

namespace App\Http\Requests;

use App\Models\Cv;
use App\Models\CvHobby;
use Illuminate\Foundation\Http\FormRequest;

class CvHobbyRequest extends FormRequest
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
                $cvHobby = CvHobby::findOrFail($this->id);
                $cv = Cv::findOrFail($cvHobby->cv_id);
                return $this->user()->can('view', [Cv::class, $cv]);
            case 'POST':
                return $this->user()->can('create', Cv::class);
            case 'PUT':
            case 'PATCH':
                $cvHobby = CvHobby::findOrFail($this->id);
                $cv = Cv::findOrFail($cvHobby->cv_id);
                return $this->user()->can('update', [Cv::class, $cv]);
            case 'DELETE':
                $cvHobby = CvHobby::findOrFail($this->id);
                $cv = Cv::findOrFail($cvHobby->cv_id);
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
                    'name' => 'required|max:100',
                ];
            case 'PUT':
            case 'PATCH':
                return [
                    'name' => 'required|max:100',
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

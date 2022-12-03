<?php

namespace App\Http\Requests;

use App\Models\Cv;
use App\Models\CvLanguage;
use Illuminate\Foundation\Http\FormRequest;

class CvLanguageRequest extends FormRequest
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
                $cvLanguage = CvLanguage::findOrFail($this->id);
                $cv = Cv::findOrFail($cvLanguage->cv_id);
                return $this->user()->can('view', [Cv::class, $cv]);
            case 'POST':
                return $this->user()->can('create', Cv::class);
            case 'PUT':
            case 'PATCH':
                $cvLanguage = CvLanguage::findOrFail($this->id);
                $cv = Cv::findOrFail($cvLanguage->cv_id);
                return $this->user()->can('update', [Cv::class, $cv]);
            case 'DELETE':
                $cvLanguage = CvLanguage::findOrFail($this->id);
                $cv = Cv::findOrFail($cvLanguage->cv_id);
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
                    'language_id' => 'required|exists:languages,id',
                    'level' => 'required|max:50',
                ];
            case 'PUT':
            case 'PATCH':
                return [
                    'language_id' => 'required|exists:languages,id',
                    'level' => 'required|max:50',
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

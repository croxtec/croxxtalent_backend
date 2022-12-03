<?php

namespace App\Http\Requests;

use App\Models\Cv;
use Illuminate\Foundation\Http\FormRequest;

class CvPhotoRequest extends FormRequest
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
                $cv = Cv::findOrFail($this->id);
                return $this->user()->can('update', [Cv::class, $cv]);
            case 'PUT':
            case 'PATCH':                
            case 'DELETE':
                return false;
            default:
                break;
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
                    'photo' => 'required|mimes:jpeg,jpg,png,gif,bmp|max:5120',
                ];
            case 'PUT':
            case 'PATCH':                
            case 'DELETE':
                return [];
            default:
                break;
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
            'photo.mimes' => 'The photo file must be an Image (JPEG, PNG, GIF, BMP) format only.',
            'photo.max' => 'The photo file is too large, file should not be greater than 5 megabytes.',
        ];
    }
}

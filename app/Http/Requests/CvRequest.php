<?php

namespace App\Http\Requests;

use App\Models\Cv;
use Illuminate\Foundation\Http\FormRequest;

class CvRequest extends FormRequest
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
                return $this->user()->can('create', Cv::class);
            case 'PUT':
            case 'PATCH':
                $cv = Cv::findOrFail($this->id);
                return $this->user()->can('update', [Cv::class, $cv]);
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
            case 'PUT':
                // return [
                //     'phone' => 'required|max:25',
                //     'email' => 'required|email|max:150',
                //     'country_code' => 'required|exists:countries,code',
                //     'city' => 'required|max:255',
                //     'state_id' => 'required|exists:states,id',
                //     'postal_code' => 'nullable|max:10',
                //     'address' => 'required|max:255',
                // ];
            case 'POST':
                return [
                    'first_name' => 'required|max:30',
                    'last_name' => 'required|max:30',
                    'gender' => 'required',
                    'date_of_birth' => 'nullable|date',

                    'industry_id' => 'required|exists:industries,id',
                    // 'job_title_id' => 'required|exists:job_titles,id',
                    'job_title' => 'required',
                    'career_summary' => 'required|max:500',
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
            // 'user_id.exists' => 'User not found.',
        ];
    }
}

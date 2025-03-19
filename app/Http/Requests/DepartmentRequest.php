<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DepartmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'job_code' => 'required|max:30',
            'description' => 'nullable|max:130',
            'roles' => 'required|array|min:1',
            'roles.*.name' => 'required|max:30',
            'roles.*.description' => 'nullable|max:130',
        ];
    }
}

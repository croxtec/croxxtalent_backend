<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
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
                return true;
            case 'PUT':
            case 'PATCH':
                return false;
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
            case 'POST':
                return [
                    'type' => 'required|in:talent,employer,affiliate', // optional for admin
                    'first_name' => 'required|max:30',
                    'last_name' => 'required|max:30',
                    'email' => 'required|max:150|unique:users,email',
                    'password' => 'required|min:6',
                    'referral_code' => 'nullable',
                    'long_lived_access_token' => 'nullable|boolean',
                ];
            case 'PUT':
            case 'PATCH':
                return [];
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
            'type.required' => 'Account type is required.',
            'type.in' => 'Unknown account request.',
            'email.required' => 'Email address is required.',
            'email.unique' => 'The email address has already been taken.',
            'password.required' => 'Password is required.',
        ];
    }
}

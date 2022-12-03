<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class ResetNewPasswordRequest extends FormRequest
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
                    'email' => 'required|email',
                    'password_reset_code' => 'required',
                    'new_password' => 'required|min:6|confirmed',
                    'new_password_confirmation' => 'required',
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
            'new_password.required' => 'Password is required.',
        ];
    }
}

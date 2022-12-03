<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class ChangePasswordRequest extends FormRequest
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
            case 'PUT':
                return false;
            case 'PATCH':
                $user = User::findOrFail($this->id);
                return $this->user()->can('update', [User::class, $user]);
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
            case 'PUT':
                return [];
            case 'PATCH':
                return [
                    'password' => 'required|password:sanctum',
                    'new_password' => 'required|min:6|confirmed',
                    'new_password_confirmation' => 'required',
                    'force_logout' => 'nullable|boolean',
                ];
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
            'password.required' => 'Password is required.',
        ];
    }
}

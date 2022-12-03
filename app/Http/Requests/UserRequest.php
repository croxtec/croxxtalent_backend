<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
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
                return $this->user()->id == 1 || $this->user()->can('create', User::class);
            case 'PUT':
            case 'PATCH':
                $user = User::findOrFail($this->id);
                return $this->user()->can('update', [User::class, $user]);
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
                    'type' => 'required|in:admin,talent,employer,affiliate', // optional for admin
                    'first_name' => 'required|max:30',
                    'last_name' => 'required|max:30',
                    'email' => 'required|email|max:150|unique:users,email',
                ];
            case 'PUT':
            case 'PATCH':
                return [
                    'first_name' => 'required|max:30',
                    'last_name' => 'required|max:30',
                    'email' => 'required|email|max:150|unique:users,email,' . $this->id,
                    'phone' => 'required|max:25',
                    'company_name' => 'nullable|max:100',
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
            'email.unique' => 'The email already exist.',
        ];
    }
}

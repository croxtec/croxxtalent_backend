<?php

namespace App\Http\Requests;

use App\Models\JobInvitation;
use Illuminate\Foundation\Http\FormRequest;

class JobInvitationRequest extends FormRequest
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
                return $this->user()->can('create', JobInvitation::class);
            case 'PUT':
            case 'PATCH':
                $jobInvitation = JobInvitation::findOrFail($this->id);
                return $this->user()->can('update', [JobInvitation::class, $jobInvitation]);
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
                    'employer_user_id' => 'required|exists:users,id',
                    'talent_user_id' => 'required|exists:users,id',
                    'talent_cv_id' => 'required|exists:cvs,id',
                ];
            case 'PUT':
                return [
                    'employer_user_id' => 'required|exists:users,id',
                    'talent_user_id' => 'required|exists:users,id',
                    'talent_cv_id' => 'required|exists:cvs,id',
                ];
            case 'PATCH':
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
            'employer_user_id.exists' => 'User not found.',
        ];
    }
}

<?php

namespace Modules\HR\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\HR\Entities\Policy;

class PolicyRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        switch($this->method()) {
            case 'GET':
                return [];
            case 'POST':
                return [
                    'policy_name' => 'required|min:3',
                    'policy_description' => 'required|string|max:512',
                    'status' => 'required|in:active,inactive',
                    'department_id' => 'nullable|integer|exists:employer_jobcodes,id',
                    'policy_document' => 'nullable|file|mimes:pdf,docx|max:4088',
                ];
            case 'PUT':
                return [
                    'policy_name' => 'sometimes|min:3',
                    'policy_description' => 'sometimes|string|max:512',
                    'status' => 'sometimes|in:active,inactive',
                ];
            case 'PATCH':
            case 'DELETE':
                return [];
            default:break;
        }
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        switch($this->method()) {
            case 'GET':
                return false;
            case 'POST':
                return true;
                // return $this->user()->can('create', Holiday::class);
            case 'PUT':
            case 'PATCH':
                return true;
                $holiday = Policy::findOrFail($this->id);
                // return $this->user()->can('update', [Holiday::class, $holiday]);
            case 'DELETE':
                return false;
            default:break;
        }
    }
}

<?php

namespace App\Http\Requests;

use App\Models\Industry;
use Illuminate\Foundation\Http\FormRequest;

class IndustryRequest extends FormRequest
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
                return $this->user()->can('create', Industry::class);
            case 'PUT':
            case 'PATCH':
                $skill = Industry::findOrFail($this->id);
                return $this->user()->can('update', [Industry::class, $skill]);
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
                    'name' => 'required|max:100|unique:industries,name',
                    'description' => 'nullable|max:500',
                ];
            case 'PUT':
            case 'PATCH':
                return [
                    'name' => 'required|max:100|unique:industries,name,' . $this->id,
                    'description' => 'nullable|max:500',
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
            'name.unique' => 'The name already exist.',
        ];
    }
} 

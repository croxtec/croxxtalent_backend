<?php

namespace App\Http\Requests;

use App\Models\Language;
use Illuminate\Foundation\Http\FormRequest;

class LanguageRequest extends FormRequest
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
                return $this->user()->can('create', Language::class);
            case 'PUT':
            case 'PATCH':
                $skill = Language::findOrFail($this->id);
                return $this->user()->can('update', [Language::class, $skill]);
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
                    'name' => 'required|max:100|unique:skills,name',
                    'description' => 'nullable|max:500',
                ];
            case 'PUT':
            case 'PATCH':
                return [
                    'name' => 'required|max:100|unique:skills,name,' . $this->id,
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

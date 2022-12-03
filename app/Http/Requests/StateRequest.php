<?php

namespace App\Http\Requests;

use App\Models\State;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class StateRequest extends FormRequest
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
                return $this->user()->can('create', State::class);
            case 'PUT':
            case 'PATCH':
                $title = State::findOrFail($this->id);
                return $this->user()->can('update', [State::class, $title]);
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
                    'name' => [
                        'required', 'string', 'max:100',
                        Rule::unique('states', 'name')->where(function ($query) {
                            return $query->where('country_code', $this->country_code);
                        }) 
                    ],
                    'country_code' => 'nullable|string|max:2|exists:countries,code',
                    'latitude' => 'nullable|numeric',
                    'longitude' => 'nullable|numeric',
                ];
            case 'PUT':
            case 'PATCH':
                return [
                    'name' => [
                        'required', 'string', 'max:100',
                        Rule::unique('states', 'name')->where(function ($query) {
                            return $query->where('country_code', $this->country_code);
                        })->ignore($this->id)
                    ],
                    'country_code' => 'nullable|string|max:2|exists:countries,code',
                    'latitude' => 'nullable|numeric',
                    'longitude' => 'nullable|numeric',
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

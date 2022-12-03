<?php

namespace App\Http\Requests;

use App\Models\Timezone;
use Illuminate\Foundation\Http\FormRequest;

class TimezoneRequest extends FormRequest
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
                return $this->user()->can('create', Timezone::class);
            case 'PUT':
            case 'PATCH':
                $title = Timezone::findOrFail($this->id);
                return $this->user()->can('update', [Timezone::class, $title]);
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
                    'name' => 'required|max:100|unique:timezones,name',
                    'country_code' => 'nullable|string|max:2|exists:countries,code',
                    'city' => 'nullable|max:255',
                    'offset' => 'nullable|max:20',
                    'gmt' => 'nullable|max:20',
                    'abbreviation' => 'nullable|max:20',
                ];
            case 'PUT':
            case 'PATCH':
                return [
                    'name' => 'required|max:100|unique:timezones,name,' . $this->id,
                    'country_code' => 'nullable|string|max:2|exists:countries,code',
                    'city' => 'nullable|max:255',
                    'offset' => 'nullable|max:20',
                    'gmt' => 'nullable|max:20',
                    'abbreviation' => 'nullable|max:20',
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

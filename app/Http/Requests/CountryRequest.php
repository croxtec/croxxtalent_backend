<?php

namespace App\Http\Requests;

use App\Models\Country;
use Illuminate\Foundation\Http\FormRequest;

class CountryRequest extends FormRequest
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
                return $this->user()->can('create', Country::class);
            case 'PUT':
            case 'PATCH':
                $title = Country::findOrFail($this->id);
                return $this->user()->can('update', [Country::class, $title]);
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
                    'name' => 'required|max:100|unique:countries,name',
                    'code' => 'required|max:2|unique:countries,code',
                    'code3' => 'nullable|max:3|unique:countries,code3',
                    'phone_code' => 'required|integer',                    
                    'num_code' => 'nullable|integer',
                ];
            case 'PUT':
            case 'PATCH':
                return [
                    'name' => 'required|max:100|unique:countries,name,' . $this->id,
                    'code' => 'required|max:2|unique:countries,code,' . $this->id,
                    'code3' => 'nullable|max:3|unique:countries,code3,' . $this->id,
                    'phone_code' => 'required|integer',                    
                    'num_code' => 'nullable|integer',
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
            'code.unique' => 'The country code already exist.',
            'code3.unique' => 'The country ISO code3 already exist.',
        ];
    }
} 

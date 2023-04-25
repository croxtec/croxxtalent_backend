<?php

namespace App\Http\Requests;

use App\Models\Assesment;
use Illuminate\Foundation\Http\FormRequest;

class AssesmentRequest extends FormRequest
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
                return true;//$this->user()->can('create', Assesment::class);
            case 'PUT':
            case 'PATCH':
                $assesment = Assesment::findOrFail($this->id);
                return $this->user()->can('update', [Assesment::class, $assesment]);
            case 'DELETE':
                return false;
            default:break;
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {

        switch($this->method()) {
            case 'GET':
                return [];
            case 'POST':
                return [
                    // 'employer_id' => 'required|exists:users,id',
                    'domain_id' => 'required|exists:skills,id',
                    'core_id' => 'required',
                    'skill_id' => 'required',
                    'level' => 'required|max:100',
                    // 'skill_ids.*' => 'required',

                    'name' => 'required|max:100',
                    'description' => 'nullable',
                    'category' => 'required',
                    'validity_period' => 'nullable',
                    'delivery_type' => 'required',
                    'expected_score' => 'nullable',
                    'questions' => 'required|array',

                    'questions.*.type' => 'required',
                    'questions.*.question' => 'required',
                    'questions.*.desctiption' => 'nullable',
                    'questions.*.option1' => 'nullable|max:50',
                    'questions.*.option2' => 'nullable|max:50',
                    'questions.*.option3' => 'nullable|max:50',
                    'questions.*.option4' => 'nullable|max:50',
                ];
            case 'PUT':


        }
    }
}

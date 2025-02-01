<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LessonResourceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        switch($this->method()) {

            case 'POST':
                return [
                    'lesson_id' => 'required|integer|exists:croxx_lessons,id',
                    'title' => ['required', 'string', 'max:100'],
                    'files' => ['required', 'array'],
                    'files.*' => [
                        'required','file',
                        'mimes:pdf,doc,docx,xls,xlsx,txt,csv,jpg,jpeg,png,gif',
                        'max:16000'  // 15MB limit
                    ],
                ];
            case 'DELETE':
                return [];
            default:
                break;
        }
    }
}

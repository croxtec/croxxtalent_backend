<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CurrateLessonRequest extends FormRequest
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
            case 'GET':
                return [];
            case 'POST':
                return [
                    'training_id' => 'required|integer|exists:croxx_trainings,id',
                    'title' => 'required|max:100',
                    'description' => 'required|min:20|max:1536',
                    'video_url' => [
                        'nullable',
                        'url',
                        'regex:/^(https?:\/\/)?(www\.)?(youtube\.com|youtu\.be|vimeo\.com)\/.+$/'
                    ],
                    'keyword' => 'nullable|string|max:255',
                    'cover_photo' => 'nullable|image|max:512', // 512KB
                    // 'video' => 'nullable|mimetypes:video/mp4|max:61440', // 60MB in kilobytes
                ];

            case 'PUT':
            case 'PATCH':
                return [
                    'training_id' => 'sometimes|integer|exists:croxx_trainings,id',
                    'title' => 'sometimes|max:100',
                    'description' => 'sometimes|min:20|max:1536',
                    'video_url' => [
                        'sometimes',
                        'url',
                        'regex:/^(https?:\/\/)?(www\.)?(youtube\.com|youtu\.be|vimeo\.com)\/.+$/'
                    ],
                    'keyword' => 'nullable|string|max:255',
                    'cover_photo' => 'nullable|image|max:512',
                    'video' => 'nullable|mimetypes:video/mp4,video/quicktime|max:61440', // 60MB in kilobytes
                ];

            case 'DELETE':
                return [];
            default:
                break;
        }
    }

}

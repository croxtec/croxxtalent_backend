<?php

namespace Modules\HR\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\HR\Entities\Holiday;

class HolidayRequest extends FormRequest
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
                    'holiday_name' => 'required|min:3',
                    'holiday_date' => 'required|date',
                    'type' => 'required|in:public,optional,restricted',
                    // 'applicable_to' => 'required|min:3',
                    // 'is_recurring' => 'required|min:3',
                ];
            case 'PUT':
                return [
                    'holiday_name' => 'sometimes|min:3',
                    'holiday_date' => 'sometimes|date',
                    'type' => 'sometimes|in:public,optional,restricted',
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
                $holiday = Holiday::findOrFail($this->id);
                // return $this->user()->can('update', [Holiday::class, $holiday]);
            case 'DELETE':
                return false;
            default:break;
        }
    }
}

<?php

namespace App\Http\Requests;
use App\Models\Goal;
use App\Models\SUpervisor;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class GoalRequest extends FormRequest
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
                return true;
            //     // return $this->user()->can('create', Goal::class);
            case 'PUT':
            case 'PATCH':
                $goal = Goal::findOrFail($this->id);
                return $this->user()->can('update', [Goal::class, $goal]);
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
                    'type' => 'required|in:career,supervisor',
                    'title' => 'required|max:50',
                    'supervisor_id' => [
                        Rule::requiredIf(function () {
                            return in_array(request()->input('type'), ['supervisor']);
                        }),
                        'exists:employees,id'
                    ],
                    'employee_id' => [
                        Rule::requiredIf(function () {
                            return in_array(request()->input('type'), ['supervisor']);
                        }),
                        'exists:employees,id'
                    ],
                    'period' => ['required',
                        'date_format:Y-m-d H:i',
                        function ($attribute, $value, $fail) {
                            $currentTime = now()->addHour();
                            if (strtotime($value) <= strtotime($currentTime)) {
                                $fail('The ' . $attribute . ' must be at least one hour greater than the current time.');
                            }
                        }
                    ],
                    'reminder' => 'required|string',
                    'metric' => 'nullable|max:250',
                ];
            case 'PUT':
            case 'PATCH':
                return [
                    // 'type' => 'sometimes|required|in:career,supervisor,company',
                    // 'title' => 'sometimes|required|max:50',
                    // 'period' => 'sometimes|required|date',
                    // 'reminder' => 'sometimes|required|string',
                    'status' => 'required|in:done,missed',
                ];
            case 'DELETE':
                return [];
            default:
                return [];
        }
    }
}

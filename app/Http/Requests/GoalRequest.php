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
                return true;
                // $goal = Goal::findOrFail($this->id);
                // return $this->user()->can('update', [Goal::class, $goal]);
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
         // Define the available reminder options
         $reminderOptions = [
            '5 Minutes before',
            '10 Minutes before',
            '15 Minutes before',
            '30 Minutes before',
            '1 Hour before',
            '2 Hours before',
            '3 Hours before' ,
            '6 Hours before',
            '1 Day before',
            '2 Days before',
            '3 Days before',
        ];

        // Create the validation rule string for reminder options
        $reminderValidation = implode(',', $reminderOptions);

        switch($this->method()) {
            case 'GET':
                return [];
            case 'POST':
                return [
                    'type' => 'required|in:career,supervisor',
                    'title' => 'required|max:50',
                    'supervisor_code' => [
                        Rule::requiredIf(function () {
                            return in_array(request()->input('type'), ['supervisor']);
                        }),
                        'exists:employees,code'
                    ],
                    'employee_code' => [
                        Rule::requiredIf(function () {
                            return in_array(request()->input('type'), ['supervisor']);
                        }),
                        'exists:employees,code'
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
                    'reminder' => 'required|string|in:' . $reminderValidation,
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

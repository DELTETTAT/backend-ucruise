<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ScheduleRequest extends FormRequest
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
        return [
            'start_time' => 'required_if:shift_types,1,2',
            'end_time' => 'required_if:shift_types,2,3',
            'reacurrance_end_time' => 'required_if:is_repeat,on',

        ];
    }
    public function messages()
    {
        return [
            'start_time.required_if' => 'The pickup time is required.',
            'end_time.required_if' => 'The dropoff time is required.',
            'reacurrance_end_time.required_if' => 'The recurrence end time is required .',
        ];
    }
}

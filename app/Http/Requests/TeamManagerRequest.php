<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Rules\UniqueEmployeeAssignment;

class TeamManagerRequest extends FormRequest
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
        $teamId = $this->route('id');
        return [
            'name' => 'required|string',
            'description' => 'nullable|string',
            'employee_id' => ['nullable', new UniqueEmployeeAssignment($teamId)],
            'team_attendance_access' => 'nullable|in:0,1',
            'location' => 'nullable|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ];
    }

}


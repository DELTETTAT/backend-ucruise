<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\UniqueEmployeeAssignment;

class HrmsTeamRequest extends FormRequest
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
            'team_name'   => 'required|string|max:255',
            'description' => 'nullable|string',
            'members'     => ['nullable', new UniqueEmployeeAssignment($teamId)],    //'nullable',
            'team_leader' => ['nullable', new UniqueEmployeeAssignment($teamId)],            //'nullable|integer',
            'team_manager_id'   => 'nullable|integer'
        ];
    }
}

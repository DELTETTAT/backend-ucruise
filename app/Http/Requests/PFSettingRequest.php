<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PFSettingRequest extends FormRequest
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
            'pf_enabled' => 'nullable',
            'pf_type' => 'required|in:Percentage,Fixed',
            'pf_value' => 'required|string',
            'leave_deduction_enabled' => 'nullable',
            'medical_half' => 'nullable|integer',
            'medical_full' => 'nullable|integer',
            'casual_half' => 'nullable|integer',
            'casual_full' => 'nullable|integer',
            'maternity_half' => 'nullable|integer',
            'maternity_full' => 'nullable|integer',
            'bereavement_half' => 'nullable|integer',
            'bereavement_full' => 'nullable|integer',
            'wedding_half' => 'nullable|integer',
            'wedding_full' => 'nullable|integer',
            'paternity_half' => 'nullable|integer',
            'paternity_full' => 'nullable|integer',
            'late_day_count_enabled' => 'nullable',
            'late_day_max' => 'nullable|integer',
        ];
    }
}

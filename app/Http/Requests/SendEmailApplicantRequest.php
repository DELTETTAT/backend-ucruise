<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SendEmailApplicantRequest extends FormRequest
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
            'id' => 'required|integer|exists:new_applicant,id',
            'stage' => 'nullable|integer',
            'subject' => 'nullable|string|required_without:hiring_id',
            'body' => 'nullable|string|required_without:hiring_id',
            'hiring_id' => 'nullable|required_without_all:subject,body',
        ];
    }

}

<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserAnswerRequest extends FormRequest
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
            //
            'new_applicant_id' => 'required|integer',
            'hiring_quiz_id' => 'required|integer',
            'questionDetails' => 'required|array',
            'questionDetails.*.id' => 'required|integer',
            'questionDetails.*.question_type_id' => 'nullable|integer',
            'questionDetails.*.answer' => 'required|array',
            'questionDetails.*.answer.*.id' => 'required|integer',
            'questionDetails.*.answer.*.is_Select' => 'required',
            'questionDetails.*.answer.*.is_answer_correct' => 'required',

            'questionDetails.*.answer.*.description' => 'nullable',
        ];
    }
}

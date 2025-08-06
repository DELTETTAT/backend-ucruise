<?php

namespace App\Http\Requests\Hrms\Quiz\HiringQuiz;

use Illuminate\Foundation\Http\FormRequest;

class HiringQuizRequest extends FormRequest
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
            'name' => 'required',
            'description' => 'nullable',
            'desgination_id' => 'required',
            'quiz_level_id' => 'required',
            'questions_details' => 'required',
        ];
    }
}

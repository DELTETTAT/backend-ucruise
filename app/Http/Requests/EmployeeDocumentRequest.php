<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EmployeeDocumentRequest extends FormRequest
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
            'employee_id' => 'required|string',
            'document_title_id' => 'required|string',
            'sub_document_id' => 'required|string',
            'file' => 'required|mimes:jpeg,png,jpg,gif,svg,pdf,doc,docx|max:10240',
        ];
    }
}

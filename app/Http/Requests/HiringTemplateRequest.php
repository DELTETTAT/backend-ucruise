<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class HiringTemplateRequest extends FormRequest
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
            'template_name'=>'required|string|max:255',
            'title' => 'required|string|max:255',
            'status' => 'integer',
            'header_image' => 'nullable',
            'background_image' => 'nullable',
            'watermark' => 'nullable',
            'footer_image' => 'nullable',
            'content' => 'string',
            'watermarkOpacity' => 'nullable|numeric|between:0,1',
            'watermarkPosition' => 'nullable|integer',
            'headerImagePosition' => 'nullable|integer',
            'footerImagePosition' => 'nullable|integer',
            'template_type' => 'nullable|integer',
            'header_image_scale' => 'nullable|integer',
            'footer_image_scale' => 'nullable|integer',
        ];
    }
}

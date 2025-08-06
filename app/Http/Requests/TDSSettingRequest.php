<?php
 
namespace App\Http\Requests;
 
use Illuminate\Foundation\Http\FormRequest;
use \App\Models\TdsSetting;
 
class TDSSettingRequest extends FormRequest
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
 
 
    public function prepareForValidation()
    {
        // If data is already an array of arrays, skip conversion
        if (is_array($this->all()) && isset($this->all()[0]) && is_array($this->all()[0])) {
            return;
        }

        // Convert 0[tds_from] style input to proper nested array
        $structured = [];

        foreach ($this->all() as $key => $value) {
            if (preg_match('/^(\d+)\[(.+)\]$/', $key, $matches)) {
                $index = $matches[1];
                $field = $matches[2];
                $structured[$index][$field] = $value;
            }
        }

        if (!empty($structured)) {
            $this->replace($structured);
        }

        if ($this->tds) {
            $tds = strtolower($this->tds); // lowercase
            $tds = preg_replace('/\s+/', '', $tds); // remove whitespace
            $this->merge(['tds' => $tds]);
        }
    }
 
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            '*.tds_from' => [
                'required',
                'numeric',
            ],
            '*.tds_to' => [
                'required',
                'numeric',
                function ($attribute, $value, $fail) {
                    $index = explode('.', $attribute)[0]; // get array index like "0" from "0.tds_to"
                    $from = $this->input("$index.tds_from");
 
                    if ($from >= $value) {
                        return $fail("TDS From value must be less than TDS To value.");
                    }
 
                    $existing = TdsSetting::all();
                    foreach ($existing as $record) {
                        $existingFrom = (float) $record->tds_from;
                        $existingTo = (float) $record->tds_to;
 
                        if ($from < $existingTo && $value > $existingFrom) {
                            return $fail("This TDS range overlaps with existing range: {$existingFrom} - {$existingTo}");
                        }
                    }
                }
            ],
            '*.tds_type' => 'required|string|in:Percentage,Fixed',
            '*.tds_value' => 'required|string',
            '*.tds_enable' => 'nullable|integer',
        ];
    }
}
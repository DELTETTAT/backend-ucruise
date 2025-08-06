<?php

namespace App\Http\Resources\EmployeeDocuments;

use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeSubDocumentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'employee_id' => $this->employee_id,
            'document_title_id' => $this->document_title_id,
            'sub_document_id' => $this->sub_document_id,
            'file' => isset($this->file) ? asset('EmployeeDocuments/' . $this->file) : "No Image",
        ];
        //return parent::toArray($request);
    }
}

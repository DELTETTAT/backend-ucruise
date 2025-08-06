<?php

namespace App\Http\Resources\EmployeeDocuments;

use Illuminate\Http\Resources\Json\JsonResource;

class DocumentCategoryResource extends JsonResource
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
            'document_id' => $this->document_id,
            'category' => $this->category,
            'employee_documents' => EmployeeSubDocumentResource::collection($this->employeeDocuments),
        ];

        // return parent::toArray($request);
    }
}

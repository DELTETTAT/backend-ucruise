<?php

namespace App\Http\Resources\EmployeeDocuments;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\HrmsEmployeeSubDocument;

class EmployeeDocumentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return  [
            'id' => $this->id,
            'title' => $this->title,
            'document_categories' => DocumentCategoryResource::collection($this->documentCategories),
        ];
      
    }
}

<?php

namespace App\Http\Resources\Documents;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\HrmsDocumentCategories;

class DocumentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {

        $documentCategories = HrmsDocumentCategories::select('id', 'document_id', 'category')->where('document_id', $this->id)->get();
        return [
            'id' => $this->id,
            'title' => $this->title,
            'document_Categories' => $documentCategories
        ];
        //return parent::toArray($request);
    }
}

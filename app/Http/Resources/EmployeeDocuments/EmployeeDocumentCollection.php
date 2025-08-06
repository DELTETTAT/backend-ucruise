<?php

namespace App\Http\Resources\EmployeeDocuments;

use Illuminate\Http\Resources\Json\ResourceCollection;

class EmployeeDocumentCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $collection = $this->collection->map(function($value, $key){
              return  new EmployeeDocumentResource($value);
        });
        return $collection;
    }
}

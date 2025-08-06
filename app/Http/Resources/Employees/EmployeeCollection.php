<?php

namespace App\Http\Resources\Employees;

use Illuminate\Http\Resources\Json\ResourceCollection;

class EmployeeCollection extends ResourceCollection
{

    private $pagination;

    public function __construct($resource, $pagination_link = true){
        if ($pagination_link) {
            $this->pagination = [
                     'total' => $resource->total(),
                     'count' => $resource->count(),
                     'per_page' => $resource->perPage(),
                     'current_page' => $resource->currentPage(),
                     'last_page' => $resource->lastPage(),
                     'first_page_url' => $resource->url(1),
                     'last_page_url' => $resource->url($resource->lastPage()),
                     'next_page_url' => $resource->nextPageUrl(),
                     'prev_page_url' => $resource->previousPageUrl()
            ];

        }
        parent:: __construct($resource);
    }
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $collection = [
            'users_list' => $this->collection->map(function ($value, $key) {
                return new EmployeeResource($value);
            }),
        ];

        if (is_array($this->pagination)) {
            $collection['pagination'] = $this->pagination;
        }

        return $collection;
    }
}

<?php

namespace App\Http\Resources\NewApplicant;

use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Http\Resources\NewApplicant\CandidatesResource;

class CandidatesCollection extends ResourceCollection
{

    private $pagination;

    public function __construct($resource, $pagination_links = true) {
         if ($pagination_links) {
            $this->pagination = [
                'total' => $resource->total(),
                'count' => $resource->count(),
                'per_page' => $resource->perPage(),
                'current_page' => $resource->currentPage(),
                'last_page' => $resource->lastPage(),
                'hasMorePages' => $resource->hasMorePages(),
                'first_page_url' => $resource->url(1),
                'last_page_url' => $resource->url($resource->lastPage()),
                'next_page_url' => $resource->nextPageUrl(),
                'prev_page_url' => $resource->previousPageUrl()
            ];
            $resource = $resource->getCollection();
         }

         parent::__construct($resource);
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
            'candidates_list' => $this->collection->map(function ($value) {
                  return new CandidatesResource($value);
            })
        ];

        if ($this->pagination) {
            $collection['pagination'] = $this->pagination;
        }
        return $collection;
    }
}

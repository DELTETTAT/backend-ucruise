<?php

namespace App\Http\Resources\Announcement;

use Illuminate\Http\Resources\Json\ResourceCollection;

class AnnouncementCollection extends ResourceCollection
{

    private $pagination;

    public function __construct($resource, $pagination_links = true)
    {
        if ($pagination_links) {
            $this->pagination = [
                'total' => $resource->total(),
                'count' => $resource->count(),
                'per_page' => $resource->perPage(),
                'current_page' => $resource->currentPage(),
                'last_page' => $resource->lastPage(),
                'hasMorePages' => $resource->hasMorePages(),
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
            'announcement_list' => $this->collection->map(function ($value, $key) {
                return new AnnouncementResource($value);
            }),
        ];

        if (is_array($this->pagination)) {
            $collection['pagination'] = $this->pagination;
        }

        return $collection;

    }
}

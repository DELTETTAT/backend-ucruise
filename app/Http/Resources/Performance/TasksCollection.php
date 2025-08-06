<?php

namespace App\Http\Resources\Performance;

use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Http\Resources\Performance\TasksResource;

class TasksCollection extends ResourceCollection
{

    private $pagination;

    public function __construct($resource, $pagination_links = true){
        if ($pagination_links) {
            $this->pagination = [
                'total' => $resource->total(),
                'count' => $resource->count(),
                'per_page' => $resource->perPage(),
                'current_page' => $resource->currentPage(),
                'last_page' => $resource->lastPage(),
                'hasMorePages' => $resource->hasMorePages(),
                'links' => [
                    'first' => $resource->url(1),
                    'prev' => $resource->previousPageUrl(),
                    'next' => $resource->nextPageUrl(),
                    'last' => $resource->url($resource->lastPage()),
                ]
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
            'task_list' => $this->collection->map(function ($value) {
               return new TasksResource($value);
            })
            
            ->groupBy(function ($task) {
                return \Carbon\Carbon::parse($task->created_at)->format('Y-m-d');
            }),
        ];

        if ($this->pagination) {
            $collection['pagination'] = $this->pagination;
        }

        return $collection;
    }
}

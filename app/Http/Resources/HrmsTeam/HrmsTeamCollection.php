<?php

namespace App\Http\Resources\HrmsTeam;

use Illuminate\Http\Resources\Json\ResourceCollection;

class HrmsTeamCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */


    public function toArray($request)
    {
       // return parent::toArray($request);

        return [
            'url' => url('/images/'),
            'team_list' => $this->collection,
            'pagination' => [
                'total' => $this->resource->total(),
                'count' => $this->resource->count(),
                'per_page' => $this->resource->perPage(),
                'current_page' => $this->resource->currentPage(),
                'last_page' => $this->resource->lastPage(),
                'next_page_url' => $this->resource->nextPageUrl(),
                'prev_page_url' => $this->resource->previousPageUrl(),
                'links' => [
                     'first' => $this->resource->url(1),
                     'prev' => $this->resource->previousPageUrl(),
                     'next' => $this->resource->nextPageUrl(),
                     'last' => $this->resource->url($this->resource->lastPage()),
                 ]
            ],
        ];

    }
}

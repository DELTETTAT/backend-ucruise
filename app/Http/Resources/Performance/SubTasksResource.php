<?php

namespace App\Http\Resources\Performance;

use Illuminate\Http\Resources\Json\JsonResource;

class SubTasksResource extends JsonResource
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
            'task_id' => $this->task_id,
            'name' => $this->name,
            'status' => $this->status,
        ];
    }
}

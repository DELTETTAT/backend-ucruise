<?php

namespace App\Http\Resources\Performance;

use Illuminate\Http\Resources\Json\JsonResource;

class TasksResource extends JsonResource
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
            'assignee_id' => $this->assignee_id,
            'assigned_id' => $this->assigned_id,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'project_title' => $this->project_title,
            'priority' => $this->priority,
            'description' => $this->description,
            'status' => $this->status,
            'project' => $this->project ?? null,
            'assignee' => new TaskAssigneeResource($this->whenLoaded('assignee')),  // Here single records 
            'team_leader' => new TaskAssigneeResource($this->whenLoaded('teamLeader')),  // Here single records 
            'team_manager' => new TaskAssigneeResource($this->whenLoaded('teamManager')),  // Here single records 
            'assigned_users' => TaskAssignedResource::collection($this->assigned_users ?? []),
            'sub_tasks' => SubTasksResource::collection($this->whenLoaded('subTasks')) // Here multiple users records
        ];
    }
}

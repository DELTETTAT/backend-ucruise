<?php

namespace App\Http\Resources\HrmsTeam;

use Illuminate\Http\Resources\Json\JsonResource;

class HrmsTeamResource extends JsonResource
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
            "id" => $this->id,
            "team_name" => $this->team_name,
            "status" => $this->status == 1 ? "Active" : "Inactive",
            "description" => $this->description,
            "members" => TeamMemberResource::collection(collect($this->teamMembers)),
        ];
    }
}

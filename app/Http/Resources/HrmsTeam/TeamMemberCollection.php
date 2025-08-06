<?php

namespace App\Http\Resources\HrmsTeam;

use Illuminate\Http\Resources\Json\ResourceCollection;

class TeamMemberCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            "id" => $this->id,
            "user_id" => $this->user_id,
            "user" => $this->user ? [
                "id" => $this->user->id,
                "name" => $this->user->name,
                "email" => $this->user->email,
            ] : null,
        ];
    }
}

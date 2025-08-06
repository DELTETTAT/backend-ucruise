<?php

namespace App\Http\Resources\EmployeeAttendance;

use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeAttendanceResource extends JsonResource
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
            'user_id' => $this->user_id,
            'employee_id' => $this->employee_id,
            'date' => $this->date,
            'login_time' => $this->login_time,
            'logout_time' => $this->logout_time,
            'ideal_time' => $this->ideal_time,
            'production' => $this->production,
            'break' => $this->break,
            'overtime' => $this->break,
        ];
    }
}

<?php

namespace App\Http\Resources\Reason;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Reason\subCategorieCollection;
use App\Models\HrmsSubReason;
use App\Models\Reason;

class ReasonResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $reasons = HrmsSubReason::where('reason_id', $this->id)->select('id','reason_id','sub_categories')->get();
        return [
            'id' => $this->id,
            'title' => $this->title_of_reason,
            'status' => $this->status,
            'reasons' => $this->when($reasons->isNotEmpty(), $reasons),
        ];
    }
}

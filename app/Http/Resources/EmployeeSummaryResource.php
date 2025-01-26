<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeSummaryResource extends JsonResource
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
            'name' => $this->name,
            'job_code' => $this->job_code_id,
            'department' => $this->department?->job_code,
            'department_role' => $this->department_role?->name,
            'photo_url' => $this->photo_url,
            'code' => $this->code,
        ];
    }
}

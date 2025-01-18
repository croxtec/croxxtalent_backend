<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeTeamResource extends JsonResource
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
            'id' => $this->employee->id,
            'name' => $this->employee->name,
            'job_code' => $this->employee->job_code_id,
            'department_role' => $this->employee->department_role_id,
            'photo_url' => $this->employee->photo_url,
            'code' => $this->employee->code,
        ];
    }
}

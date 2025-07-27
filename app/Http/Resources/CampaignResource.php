<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CampaignResource extends JsonResource
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
            'title' => $this->title,
            'job_title' => $this->job_title,
            'industry_id' => $this->industry_id,
            'work_type' => $this->work_type,
            'department_id' => $this->department_id,
            'minimum_degree_id' => $this->minimum_degree_id,
            'currency_code' => $this->currency_code,
            'min_salary' => $this->min_salary,
            'max_salary' => $this->max_salary,
            'is_confidential_salary' => $this->is_confidential_salary,
            'years_of_experience' => $this->years_of_experience,
            'number_of_positions' => $this->number_of_positions,
            'expire_at' => $this->expire_at,
            'city' => $this->city,
            'state_id' => $this->state_id,
            'country_code' => $this->country_code,
            'summary' => $this->summary,
            'description' => $this->description,
            'is_managed' => $this->is_managed,
            'status' => $this->status,
            'is_published' => $this->is_published,
            'published_at' => $this->published_at,
            'skill_ids' => $this->skills->pluck('id')->toArray(),
            'course_of_study_ids' => $this->courseOfStudies->pluck('id')->toArray(),
            'language_ids' => $this->languages->pluck('id')->toArray(),
            'technical_skills' => $this->skills->where('type', 'technical'),
            'soft_skills' => $this->skills->where('type', 'soft'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

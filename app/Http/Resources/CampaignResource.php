<?php

namespace app\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CampaignResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name_campaign' => $this->name_campaign,
            'number_volunteers' => $this->number_volunteers,
            'cost' => $this->cost,
            'address' => $this->address,
            'from_time' => $this->from_time,
            'to_time' => $this->to_time,
            'points' => $this->points,
            'status' => $this->status,
            'specialization' => new SpecializationResource($this->whenLoaded('specialization')),
            'campaign_type' => new CampaignTypeResource($this->whenLoaded('campaignType')),
            'team' => new TeamResource($this->whenLoaded('team')),
            'employee' => new EmployeeResource($this->whenLoaded('employee')),
            'volunteers' => VolunteerResource::collection($this->whenLoaded('volunteers')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
} 
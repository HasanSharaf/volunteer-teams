<?php

namespace app\Http\Resources;

use Illuminate\Http\Request;
use app\Http\Resources\TeamResource;
use app\Http\Resources\EmployeeResource;
use App\Http\Resources\VolunteerResource;
use App\Http\Resources\CampaignTypeResource;
use App\Http\Resources\SpecializationResource;
use Illuminate\Http\Resources\Json\JsonResource;

class CampaignResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'campaign_name' => $this->campaign_name,
            'number_of_volunteer' => $this->number_of_volunteer,
            'cost' => $this->cost,
            'address' => $this->address,
            'from' => $this->from,
            'to' => $this->to,
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
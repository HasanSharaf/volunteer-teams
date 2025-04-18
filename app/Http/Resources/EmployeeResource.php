<?php

namespace app\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'full_name' => $this->full_name,
            'phone' => $this->phone,
            'national_number' => $this->national_number,
            'position' => $this->position,
            'date_accession' => $this->date_accession,
            'image' => $this->image,
            'team' => new TeamResource($this->whenLoaded('team')),
            'specialization' => new SpecializationResource($this->whenLoaded('specialization')),
            'campaigns' => CampaignResource::collection($this->whenLoaded('campaigns')),
            'points' => PointResource::collection($this->whenLoaded('points')),
            'attendances' => AttendanceResource::collection($this->whenLoaded('attendances')),
            'donor_payments' => DonorPaymentResource::collection($this->whenLoaded('donorPayments')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
} 
<?php

namespace app\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TeamResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'full_name' => $this->full_name,
            'team_name' => $this->team_name,
            'license_number' => $this->license_number,
            'reg_image' => $this->reg_image,
            'phone' => $this->phone,
            'bank_account_number' => $this->bank_account_number,
            'email' => $this->email,
            'campaigns' => CampaignResource::collection($this->whenLoaded('campaigns')),
            'employees' => EmployeeResource::collection($this->whenLoaded('employees')),
            'requests' => RequestResource::collection($this->whenLoaded('requests')),
            'donor_payments' => DonorPaymentResource::collection($this->whenLoaded('donorPayments')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
} 
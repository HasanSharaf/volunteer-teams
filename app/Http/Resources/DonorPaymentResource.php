<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DonorPaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'amount' => $this->amount,
            'payment_date' => $this->payment_date,
            'type' => $this->type,
            'process_number' => $this->process_number,
            'status' => $this->status,
            'image' => $this->image,
            'benefactor' => new BenefactorResource($this->whenLoaded('benefactor')),
            'team' => new TeamResource($this->whenLoaded('team')),
            'employee' => new EmployeeResource($this->whenLoaded('employee')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
} 
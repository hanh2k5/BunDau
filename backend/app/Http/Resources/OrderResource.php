<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'daily_number'   => $this->daily_number,
            'table_number'   => $this->table_number,
            'total'          => (int) $this->total,
            'status'         => $this->status->value,
            'payment_method' => $this->payment_method->value,
            'note'           => $this->note,
            'paid_at'    => $this->paid_at?->toIso8601String(),
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
            'created_by' => new UserResource($this->whenLoaded('user')),
            'items'      => OrderItemResource::collection($this->whenLoaded('items')),
        ];
    }
}

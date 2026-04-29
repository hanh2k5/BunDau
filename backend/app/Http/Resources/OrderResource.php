<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'total'      => (int) $this->total,
            'status'     => $this->status->value,
            'note'       => $this->note,
            'paid_at'    => $this->paid_at?->toIso8601String(),
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
            'created_by' => new UserResource($this->whenLoaded('user')),
            'items'      => OrderItemResource::collection($this->whenLoaded('items')),
        ];
    }
}

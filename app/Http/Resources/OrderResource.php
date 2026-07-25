<?php

namespace App\Http\Resources;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Order
 */
class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'placed_at' => $this->placed_at->toIso8601String(),
            'cancelled_at' => $this->cancelled_at?->toIso8601String(),
            'status' => $this->status->value,
            'subtotal_cents' => $this->subtotal_cents,
            'discount_cents' => $this->discount_cents,
            'shipping_cents' => $this->shipping_cents,
            'total_cents' => $this->total_cents,
            'coupon_code' => $this->coupon_code,
            'card_brand' => $this->card_brand,
            'card_last4' => $this->card_last4,
            'tracking_number' => $this->tracking_number,
            'ship' => [
                'name' => $this->ship_name,
                'line1' => $this->ship_line1,
                'line2' => $this->ship_line2,
                'city' => $this->ship_city,
                'postal_code' => $this->ship_postal_code,
                'country' => $this->ship_country,
            ],
            'timeline' => array_map(fn (array $entry): array => [
                'status' => $entry['status']->value,
                'at' => $entry['at']->toIso8601String(),
                'reached' => $entry['reached'],
            ], $this->timeline()),
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
        ];
    }
}

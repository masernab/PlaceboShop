<?php

namespace App\Http\Resources\Admin;

use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Coupon
 */
class CouponResource extends JsonResource
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
            'code' => $this->code,
            'type' => $this->type->value,
            'value' => $this->value,
            'min_subtotal_cents' => $this->min_subtotal_cents,
            'max_uses' => $this->max_uses,
            'used_count' => $this->used_count,
            'starts_at' => $this->starts_at?->toDateString(),
            'expires_at' => $this->expires_at?->toDateString(),
            'is_active' => $this->is_active,
        ];
    }
}

<?php

namespace App\Http\Resources;

use App\Models\Cart;
use App\Models\CartItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Cart
 */
class CartResource extends JsonResource
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
            'items' => $this->items->map(fn (CartItem $item): array => [
                'id' => $item->id,
                'quantity' => $item->quantity,
                'line_total_cents' => $item->quantity * $item->product->price_cents,
                'product' => new ProductCardResource($item->product),
            ])->all(),
        ];
    }
}

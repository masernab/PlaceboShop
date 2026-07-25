<?php

namespace App\Http\Resources;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Product
 */
class ProductCardResource extends JsonResource
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
            'slug' => $this->slug,
            'name' => $this->localized($this->name),
            'price_cents' => $this->price_cents,
            'compare_at_price_cents' => $this->compare_at_price_cents,
            'image' => $this->whenLoaded(
                'primaryImage',
                fn (): ?array => $this->primaryImage === null ? null : [
                    'url' => $this->primaryImage->url,
                    'alt' => $this->primaryImage->alt,
                ],
            ),
            'category' => new CategoryResource($this->whenLoaded('category')),
        ];
    }
}

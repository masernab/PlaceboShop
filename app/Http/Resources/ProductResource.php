<?php

namespace App\Http\Resources;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Product
 */
class ProductResource extends JsonResource
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
            'sku' => $this->sku,
            'name' => $this->localized($this->name),
            'description' => $this->localized($this->description),
            'price_cents' => $this->price_cents,
            'compare_at_price_cents' => $this->compare_at_price_cents,
            'stock' => $this->stock,
            'rating_avg' => $this->reviews_avg_rating === null
                ? null
                : round((float) $this->reviews_avg_rating, 1),
            'reviews_count' => $this->reviews_count ?? 0,
            'images' => $this->whenLoaded(
                'images',
                fn (): array => $this->images->map(fn (ProductImage $image): array => [
                    'id' => $image->id,
                    'url' => $image->url,
                    'alt' => $image->alt,
                ])->all(),
            ),
            'category' => new CategoryResource($this->whenLoaded('category')),
        ];
    }
}

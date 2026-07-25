<?php

namespace App\Http\Resources\Admin;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Full bilingual payload for the admin edit forms.
 *
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
            'category_id' => $this->category_id,
            'name' => $this->name,
            'description' => $this->description,
            'price_cents' => $this->price_cents,
            'compare_at_price_cents' => $this->compare_at_price_cents,
            'stock' => $this->stock,
            'is_active' => $this->is_active,
            'is_featured' => $this->is_featured,
            'category' => $this->whenLoaded('category', fn (): array => [
                'id' => $this->category->id,
                'name' => $this->category->name,
            ]),
            'thumbnail_url' => $this->whenLoaded(
                'primaryImage',
                fn (): ?string => $this->primaryImage?->url,
            ),
            'images' => $this->whenLoaded(
                'images',
                fn (): array => $this->images->map(fn (ProductImage $image): array => [
                    'id' => $image->id,
                    'url' => $image->url,
                    'alt' => $image->alt,
                    'position' => $image->position,
                ])->all(),
            ),
        ];
    }
}

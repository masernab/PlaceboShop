<?php

namespace App\Http\Resources;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Category
 */
class CategoryResource extends JsonResource
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
            'description' => $this->localized($this->description),
            // A top-level category rolls up its subcategories so the count
            // matches what filtering by that category actually returns.
            'products_count' => $this->whenCounted(
                'products',
                fn (int $count): int => $count + (int) ($this->child_products_count ?? 0),
            ),
            'parent' => $this->whenLoaded(
                'parent',
                fn (): self => new self($this->parent),
            ),
            'children' => $this->whenLoaded(
                'children',
                fn (): array => self::collection($this->children)->resolve($request),
            ),
        ];
    }
}

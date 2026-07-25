<?php

namespace App\Models;

use App\Models\Concerns\HasLocalizedFields;
use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $category_id
 * @property string $slug
 * @property string $sku
 * @property array<string, string> $name
 * @property array<string, string> $description
 * @property int $price_cents
 * @property int|null $compare_at_price_cents
 * @property int $stock
 * @property bool $is_active
 * @property bool $is_featured
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read float|string|null $reviews_avg_rating
 * @property-read int|null $reviews_count
 */
#[Fillable([
    'category_id',
    'slug',
    'sku',
    'name',
    'description',
    'price_cents',
    'compare_at_price_cents',
    'stock',
    'is_active',
    'is_featured',
])]
class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory, HasLocalizedFields;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'name' => 'json:unicode',
            'description' => 'json:unicode',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * @return HasMany<ProductImage, $this>
     */
    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('position');
    }

    /**
     * @return HasOne<ProductImage, $this>
     */
    public function primaryImage(): HasOne
    {
        return $this->hasOne(ProductImage::class)->ofMany('position', 'min');
    }

    /**
     * @return HasMany<Review, $this>
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    /**
     * @return HasMany<WishlistItem, $this>
     */
    public function wishlistItems(): HasMany
    {
        return $this->hasMany(WishlistItem::class);
    }

    public function wishlistedBy(?User $user): bool
    {
        if ($user === null) {
            return false;
        }

        return WishlistItem::query()
            ->where('user_id', $user->id)
            ->where('product_id', $this->id)
            ->exists();
    }

    /**
     * @param  Builder<$this>  $query
     */
    #[Scope]
    protected function active(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /**
     * @param  Builder<$this>  $query
     */
    #[Scope]
    protected function featured(Builder $query): void
    {
        $query->where('is_featured', true);
    }

    /**
     * Match the search term against the bilingual name and description JSON.
     *
     * @param  Builder<$this>  $query
     */
    #[Scope]
    protected function search(Builder $query, string $term): void
    {
        $query->where(function (Builder $query) use ($term): void {
            $query->where('name', 'like', "%{$term}%")
                ->orWhere('description', 'like', "%{$term}%");
        });
    }

    /**
     * @param  Builder<$this>  $query
     */
    #[Scope]
    protected function inCategory(Builder $query, string $slug): void
    {
        $query->whereHas('category', function (Builder $query) use ($slug): void {
            $query->where('slug', $slug);
        });
    }

    /**
     * @param  Builder<$this>  $query
     */
    #[Scope]
    protected function priceBetween(Builder $query, ?int $minCents, ?int $maxCents): void
    {
        if ($minCents !== null) {
            $query->where('price_cents', '>=', $minCents);
        }

        if ($maxCents !== null) {
            $query->where('price_cents', '<=', $maxCents);
        }
    }

    /**
     * @param  Builder<$this>  $query
     */
    #[Scope]
    protected function sorted(Builder $query, string $sort): void
    {
        match ($sort) {
            'price_asc' => $query->orderBy('price_cents'),
            'price_desc' => $query->orderByDesc('price_cents'),
            'name' => $query->orderBy('name->'.app()->getLocale()),
            default => $query->latest()->orderByDesc('id'),
        };
    }
}

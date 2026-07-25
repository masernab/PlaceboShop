<?php

namespace App\Models;

use App\Enums\CouponType;
use Database\Factories\CouponFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $code
 * @property CouponType $type
 * @property int $value
 * @property int $min_subtotal_cents
 * @property int|null $max_uses
 * @property int $used_count
 * @property Carbon|null $starts_at
 * @property Carbon|null $expires_at
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'code',
    'type',
    'value',
    'min_subtotal_cents',
    'max_uses',
    'used_count',
    'starts_at',
    'expires_at',
    'is_active',
])]
class Coupon extends Model
{
    /** @use HasFactory<CouponFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => CouponType::class,
            'starts_at' => 'datetime',
            'expires_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @param  Builder<$this>  $query
     */
    #[Scope]
    protected function active(Builder $query): void
    {
        $query->where('is_active', true);
    }

    public function hasStarted(): bool
    {
        return $this->starts_at === null || $this->starts_at->isPast();
    }

    public function hasExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function isExhausted(): bool
    {
        return $this->max_uses !== null && $this->used_count >= $this->max_uses;
    }

    public function meetsMinimum(int $subtotalCents): bool
    {
        return $subtotalCents >= $this->min_subtotal_cents;
    }

    public function isRedeemable(int $subtotalCents): bool
    {
        return $this->is_active
            && $this->hasStarted()
            && ! $this->hasExpired()
            && ! $this->isExhausted()
            && $this->meetsMinimum($subtotalCents);
    }

    public function discountFor(int $subtotalCents): int
    {
        return match ($this->type) {
            CouponType::Percent => (int) round($subtotalCents * $this->value / 100),
            CouponType::Fixed => min($this->value, $subtotalCents),
        };
    }
}

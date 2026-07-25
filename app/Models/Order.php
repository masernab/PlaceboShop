<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Database\Factories\OrderFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property string $order_number
 * @property Carbon $placed_at
 * @property Carbon|null $cancelled_at
 * @property int $subtotal_cents
 * @property int $discount_cents
 * @property int $shipping_cents
 * @property int $total_cents
 * @property int|null $coupon_id
 * @property string|null $coupon_code
 * @property string $card_brand
 * @property string $card_last4
 * @property string $tracking_number
 * @property string $ship_name
 * @property string $ship_line1
 * @property string|null $ship_line2
 * @property string $ship_city
 * @property string $ship_postal_code
 * @property string $ship_country
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read OrderStatus $status
 */
#[Fillable([
    'user_id',
    'order_number',
    'placed_at',
    'cancelled_at',
    'subtotal_cents',
    'discount_cents',
    'shipping_cents',
    'total_cents',
    'coupon_id',
    'coupon_code',
    'card_brand',
    'card_last4',
    'tracking_number',
    'ship_name',
    'ship_line1',
    'ship_line2',
    'ship_city',
    'ship_postal_code',
    'ship_country',
])]
class Order extends Model
{
    /** @use HasFactory<OrderFactory> */
    use HasFactory;

    /**
     * Fake tracking offsets in minutes from placed_at, per status.
     *
     * @var array<string, int>
     */
    private const array TIMELINE_OFFSETS_MINUTES = [
        OrderStatus::Paid->value => 0,
        OrderStatus::Processing->value => 10,
        OrderStatus::Shipped->value => 6 * 60,
        OrderStatus::OutForDelivery->value => 30 * 60,
        OrderStatus::Delivered->value => 54 * 60,
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'placed_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<OrderItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * The fake tracking timeline, computed on read from placed_at. Progress
     * freezes at cancelled_at for cancelled orders. Nothing is ever stored.
     *
     * @return list<array{status: OrderStatus, at: Carbon, reached: bool}>
     */
    public function timeline(): array
    {
        $frontier = $this->cancelled_at ?? Carbon::now();

        return array_map(
            function (string $status, int $offsetMinutes) use ($frontier): array {
                $at = $this->placed_at->copy()->addMinutes($offsetMinutes);

                return [
                    'status' => OrderStatus::from($status),
                    'at' => $at,
                    'reached' => $at->lessThanOrEqualTo($frontier),
                ];
            },
            array_keys(self::TIMELINE_OFFSETS_MINUTES),
            array_values(self::TIMELINE_OFFSETS_MINUTES),
        );
    }

    /**
     * @return Attribute<OrderStatus, never>
     */
    protected function status(): Attribute
    {
        return Attribute::get(function (): OrderStatus {
            if ($this->cancelled_at !== null) {
                return OrderStatus::Cancelled;
            }

            $current = OrderStatus::Paid;

            foreach ($this->timeline() as $entry) {
                if ($entry['reached']) {
                    $current = $entry['status'];
                }
            }

            return $current;
        });
    }
}

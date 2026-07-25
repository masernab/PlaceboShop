<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('order_number')->unique();
            $table->timestamp('placed_at');
            $table->timestamp('cancelled_at')->nullable();
            $table->unsignedInteger('subtotal_cents');
            $table->unsignedInteger('discount_cents')->default(0);
            $table->unsignedInteger('shipping_cents')->default(0);
            $table->unsignedInteger('total_cents');
            $table->unsignedBigInteger('coupon_id')->nullable();
            $table->string('coupon_code')->nullable();
            $table->string('card_brand');
            $table->string('card_last4', 4);
            $table->string('tracking_number');
            $table->string('ship_name');
            $table->string('ship_line1');
            $table->string('ship_line2')->nullable();
            $table->string('ship_city');
            $table->string('ship_postal_code');
            $table->string('ship_country', 2);
            $table->timestamps();

            $table->index(['user_id', 'placed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};

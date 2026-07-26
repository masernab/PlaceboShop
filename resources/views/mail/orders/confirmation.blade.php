@php
    $money = fn (int $cents): string => '$'.number_format($cents / 100, 2);
@endphp

<x-mail::message>
# {{ __('shop.email_greeting') }}

{{ __('shop.email_intro', ['number' => $order->order_number]) }}

<x-mail::table>
| {{ __('shop.email_item') }} | {{ __('shop.email_qty') }} | {{ __('shop.email_amount') }} |
|:--------------|:-----:|--------------:|
@foreach ($order->items as $item)
| {{ $item->localized($item->product_name) }} | {{ $item->quantity }} | {{ $money($item->unit_price_cents * $item->quantity) }} |
@endforeach
| {{ __('shop.email_subtotal') }} | | {{ $money($order->subtotal_cents) }} |
@if ($order->discount_cents > 0)
| {{ __('shop.email_discount') }} | | −{{ $money($order->discount_cents) }} |
@endif
| {{ __('shop.email_shipping') }} | | {{ $order->shipping_cents === 0 ? __('shop.email_free') : $money($order->shipping_cents) }} |
| **{{ __('shop.email_total') }}** | | **{{ $money($order->total_cents) }}** |
</x-mail::table>

{{ __('shop.email_card', ['brand' => ucfirst($order->card_brand), 'last4' => $order->card_last4]) }}

<x-mail::button :url="route('orders.show', $order)">
{{ __('shop.email_track') }}
</x-mail::button>

{{ __('shop.email_footer') }}

— PlaceboShop
</x-mail::message>

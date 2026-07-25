@php
    $t = $locale === 'es' ? [
        'greeting' => '¡Gracias por tu compra placebo! 🎉',
        'intro' => 'Tu pedido **:number** está confirmado. Como siempre: no se ha cobrado nada y no se enviará nada — pero la emoción es 100 % real.',
        'item' => 'Artículo',
        'qty' => 'Cant.',
        'amount' => 'Importe',
        'subtotal' => 'Subtotal',
        'discount' => 'Descuento',
        'shipping' => 'Envío',
        'free' => 'Gratis',
        'total' => 'Total',
        'card' => 'Pagado (de mentira) con :brand terminada en :last4.',
        'track' => 'Seguir mi pedido',
        'footer' => 'PlaceboShop es una tienda placebo: cada compra es simulada. Nunca se cobra dinero real ni se envía ningún producto.',
    ] : [
        'greeting' => 'Thanks for your placebo purchase! 🎉',
        'intro' => 'Your order **:number** is confirmed. As always: nothing was charged and nothing will ship — but the thrill is 100% real.',
        'item' => 'Item',
        'qty' => 'Qty',
        'amount' => 'Amount',
        'subtotal' => 'Subtotal',
        'discount' => 'Discount',
        'shipping' => 'Shipping',
        'free' => 'Free',
        'total' => 'Total',
        'card' => 'Paid (pretend) with :brand ending in :last4.',
        'track' => 'Track my order',
        'footer' => 'PlaceboShop is a placebo store: every purchase is simulated. No real money is ever charged and no products are ever shipped.',
    ];

    $money = fn (int $cents): string => '$'.number_format($cents / 100, 2);
@endphp

<x-mail::message>
# {{ $t['greeting'] }}

{{ str_replace(':number', $order->order_number, $t['intro']) }}

<x-mail::table>
| {{ $t['item'] }} | {{ $t['qty'] }} | {{ $t['amount'] }} |
|:--------------|:-----:|--------------:|
@foreach ($order->items as $item)
| {{ $item->localized($item->product_name) }} | {{ $item->quantity }} | {{ $money($item->unit_price_cents * $item->quantity) }} |
@endforeach
| {{ $t['subtotal'] }} | | {{ $money($order->subtotal_cents) }} |
@if ($order->discount_cents > 0)
| {{ $t['discount'] }} | | −{{ $money($order->discount_cents) }} |
@endif
| {{ $t['shipping'] }} | | {{ $order->shipping_cents === 0 ? $t['free'] : $money($order->shipping_cents) }} |
| **{{ $t['total'] }}** | | **{{ $money($order->total_cents) }}** |
</x-mail::table>

{{ str_replace([':brand', ':last4'], [ucfirst($order->card_brand), $order->card_last4], $t['card']) }}

<x-mail::button :url="route('orders.show', $order)">
{{ $t['track'] }}
</x-mail::button>

{{ $t['footer'] }}

— PlaceboShop
</x-mail::message>

<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderConfirmationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Order $order, string $locale)
    {
        $this->locale = $locale;
    }

    public function envelope(): Envelope
    {
        $subject = $this->locale === 'es'
            ? "Tu pedido {$this->order->order_number} está confirmado ✨"
            : "Your order {$this->order->order_number} is confirmed ✨";

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.orders.confirmation',
            with: ['locale' => $this->locale],
        );
    }
}

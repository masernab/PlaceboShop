<?php

namespace App\Support;

class CardBrand
{
    /**
     * Detect the card brand from the number's prefix. Purely cosmetic —
     * no real card ever touches this application.
     */
    public static function detect(string $number): string
    {
        $digits = (string) preg_replace('/\D/', '', $number);

        if ($digits === '') {
            return 'card';
        }

        $two = (int) substr($digits, 0, 2);
        $four = (int) substr($digits, 0, 4);

        return match (true) {
            $two === 34 || $two === 37 => 'amex',
            $digits[0] === '4' => 'visa',
            ($two >= 51 && $two <= 55) || ($four >= 2221 && $four <= 2720) => 'mastercard',
            default => 'card',
        };
    }
}

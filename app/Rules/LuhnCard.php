<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class LuhnCard implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $digits = is_string($value) ? str_replace(' ', '', $value) : '';

        if (in_array(preg_match('/^\d{13,19}$/', $digits), [0, false], true)) {
            $fail('shop.card_number_length')->translate();

            return;
        }

        if (! $this->passesLuhn($digits)) {
            $fail('shop.card_number_invalid')->translate();
        }
    }

    private function passesLuhn(string $digits): bool
    {
        $sum = 0;
        $length = strlen($digits);

        for ($i = 0; $i < $length; $i++) {
            $digit = (int) $digits[$length - 1 - $i];

            if ($i % 2 === 1) {
                $digit *= 2;

                if ($digit > 9) {
                    $digit -= 9;
                }
            }

            $sum += $digit;
        }

        return $sum % 10 === 0;
    }
}

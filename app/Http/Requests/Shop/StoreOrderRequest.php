<?php

namespace App\Http\Requests\Shop;

use App\Rules\LuhnCard;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class StoreOrderRequest extends FormRequest
{
    /**
     * The short list of countries the fake shop pretends to ship to.
     *
     * @var list<string>
     */
    public const array COUNTRIES = [
        'AR', 'CL', 'CO', 'DE', 'ES', 'FR', 'GB', 'IT', 'MX', 'PE', 'PT', 'US',
    ];

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'ship_name' => ['required', 'string', 'max:120'],
            'ship_line1' => ['required', 'string', 'max:191'],
            'ship_line2' => ['nullable', 'string', 'max:191'],
            'ship_city' => ['required', 'string', 'max:120'],
            'ship_postal_code' => ['required', 'string', 'max:20'],
            'ship_country' => ['required', Rule::in(self::COUNTRIES)],
            'card_name' => ['required', 'string', 'max:120'],
            'card_number' => ['required', 'string', new LuhnCard],
            'card_expiry' => [
                'required',
                'string',
                'regex:/^(0[1-9]|1[0-2])\/\d{2}$/',
                $this->expiryInFuture(...),
            ],
            'card_cvc' => ['required', 'digits_between:3,4'],
        ];
    }

    private function expiryInFuture(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || ! str_contains($value, '/')) {
            return;
        }

        [$month, $year] = explode('/', $value);

        $expiresAt = Carbon::create(2000 + (int) $year, (int) $month)?->endOfMonth();

        if ($expiresAt === null || $expiresAt->isPast()) {
            $fail('The card has expired.');
        }
    }
}

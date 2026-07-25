<?php

namespace App\Http\Requests\Admin;

use App\Enums\CouponType;
use App\Models\Coupon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class CouponRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if (is_string($this->input('code'))) {
            $this->merge(['code' => strtoupper(trim($this->input('code')))]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var Coupon|null $current */
        $current = $this->route('coupon');
        $isPercent = $this->input('type') === CouponType::Percent->value;

        return [
            'code' => [
                'required',
                'string',
                'max:32',
                'regex:/^[A-Z0-9\-]+$/',
                Rule::unique('coupons', 'code')->ignore($current?->id),
            ],
            'type' => ['required', Rule::enum(CouponType::class)],
            'value' => ['required', 'integer', 'min:1', $isPercent ? 'max:100' : 'max:99999'],
            'min_subtotal' => ['nullable', 'numeric', 'min:0', 'max:99999'],
            'max_uses' => ['nullable', 'integer', 'min:1', 'max:99999'],
            'starts_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'is_active' => ['boolean'],
        ];
    }

    /**
     * The validated payload mapped to coupon columns.
     *
     * @return array<string, mixed>
     */
    public function couponAttributes(): array
    {
        $validated = $this->validated();

        return [
            'code' => $validated['code'],
            'type' => $validated['type'],
            'value' => (int) $validated['value'],
            'min_subtotal_cents' => isset($validated['min_subtotal'])
                ? (int) round((float) $validated['min_subtotal'] * 100)
                : 0,
            'max_uses' => isset($validated['max_uses']) ? (int) $validated['max_uses'] : null,
            'starts_at' => isset($validated['starts_at'])
                ? Carbon::parse($validated['starts_at'])->startOfDay()
                : null,
            'expires_at' => isset($validated['expires_at'])
                ? Carbon::parse($validated['expires_at'])->endOfDay()
                : null,
            'is_active' => $this->boolean('is_active'),
        ];
    }
}

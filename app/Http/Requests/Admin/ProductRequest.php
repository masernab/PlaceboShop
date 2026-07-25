<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'category_id' => ['required', Rule::exists('categories', 'id')],
            'name.en' => ['required', 'string', 'max:150'],
            'name.es' => ['required', 'string', 'max:150'],
            'description.en' => ['required', 'string', 'max:2000'],
            'description.es' => ['required', 'string', 'max:2000'],
            'price' => ['required', 'numeric', 'min:0.01', 'max:99999'],
            'compare_at_price' => ['nullable', 'numeric', 'gt:price', 'max:99999'],
            'stock' => ['required', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['boolean'],
            'is_featured' => ['boolean'],
        ];
    }

    /**
     * The validated payload mapped to product columns (prices in cents).
     *
     * @return array<string, mixed>
     */
    public function productAttributes(): array
    {
        $validated = $this->validated();

        return [
            'category_id' => (int) $validated['category_id'],
            'name' => $validated['name'],
            'description' => $validated['description'],
            'price_cents' => (int) round((float) $validated['price'] * 100),
            'compare_at_price_cents' => isset($validated['compare_at_price'])
                ? (int) round((float) $validated['compare_at_price'] * 100)
                : null,
            'stock' => (int) $validated['stock'],
            'is_active' => $this->boolean('is_active'),
            'is_featured' => $this->boolean('is_featured'),
        ];
    }
}

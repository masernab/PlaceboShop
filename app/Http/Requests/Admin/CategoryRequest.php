<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class CategoryRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name.en' => ['required', 'string', 'max:100'],
            'name.es' => ['required', 'string', 'max:100'],
            'description.en' => ['nullable', 'string', 'max:500'],
            'description.es' => ['nullable', 'string', 'max:500'],
            'position' => ['required', 'integer', 'min:0', 'max:999'],
        ];
    }

    /**
     * The validated payload mapped to category columns.
     *
     * @return array<string, mixed>
     */
    public function categoryAttributes(): array
    {
        $validated = $this->validated();

        $description = array_filter([
            'en' => $validated['description']['en'] ?? null,
            'es' => $validated['description']['es'] ?? null,
        ], fn (?string $value): bool => $value !== null && $value !== '');

        return [
            'name' => $validated['name'],
            'description' => $description === [] ? null : $description,
            'position' => (int) $validated['position'],
        ];
    }
}

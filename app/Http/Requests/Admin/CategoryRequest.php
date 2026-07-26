<?php

namespace App\Http\Requests\Admin;

use App\Models\Category;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CategoryRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $category = $this->currentCategory();

        // The parent must itself be a root category, which caps the tree at
        // two levels. On update it may not be the category being edited.
        $parentExists = Rule::exists('categories', 'id')->whereNull('parent_id');

        if ($category !== null) {
            $parentExists->whereNot('id', $category->id);
        }

        return [
            'parent_id' => [
                'nullable',
                'integer',
                $parentExists,
                function (string $attribute, mixed $value, Closure $fail) use ($category): void {
                    if ($value !== null && $category?->children()->exists()) {
                        $fail('This category has subcategories, so it cannot become one itself.');
                    }
                },
            ],
            'name.en' => ['required', 'string', 'max:100'],
            'name.es' => ['required', 'string', 'max:100'],
            'description.en' => ['nullable', 'string', 'max:500'],
            'description.es' => ['nullable', 'string', 'max:500'],
            'position' => ['required', 'integer', 'min:0', 'max:999'],
        ];
    }

    /**
     * Get the custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'parent_id.exists' => 'Pick a valid top-level category as the parent.',
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
            // Always emitted, null included, so a subcategory can be promoted
            // back to top level on update.
            'parent_id' => isset($validated['parent_id'])
                ? (int) $validated['parent_id']
                : null,
            'name' => $validated['name'],
            'description' => $description === [] ? null : $description,
            'position' => (int) $validated['position'],
        ];
    }

    private function currentCategory(): ?Category
    {
        $category = $this->route('category');

        return $category instanceof Category ? $category : null;
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CategoryRequest;
use App\Http\Resources\Admin\CategoryResource;
use App\Models\Category;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class CategoryController extends Controller
{
    public function index(): Response
    {
        $categories = Category::query()
            ->roots()
            ->ordered()
            ->withCount('products')
            ->with(['children' => fn (Relation $query) => $query->withCount('products')])
            ->get();

        return Inertia::render('admin/categories/index', [
            'categories' => CategoryResource::collection($categories),
        ]);
    }

    public function store(CategoryRequest $request): RedirectResponse
    {
        $attributes = $request->categoryAttributes();

        Category::query()->create([
            ...$attributes,
            'slug' => $this->uniqueSlug($attributes['name']['en']),
        ]);

        return back();
    }

    public function update(CategoryRequest $request, Category $category): RedirectResponse
    {
        $category->update($request->categoryAttributes());

        return back();
    }

    public function destroy(Category $category): RedirectResponse
    {
        if ($category->children()->exists()) {
            return back()->withErrors([
                'category' => 'This category still has subcategories. Delete or move them first.',
            ]);
        }

        if ($category->products()->exists()) {
            return back()->withErrors([
                'category' => 'This category still has products. Move or delete them first.',
            ]);
        }

        $category->delete();

        return back();
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name);
        $slug = $base === '' ? 'category' : $base;
        $suffix = 2;

        while (Category::query()->where('slug', $slug)->exists()) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }
}

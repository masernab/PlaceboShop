<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Image\ImageException;
use Illuminate\Support\Facades\Image;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ProductImageController extends Controller
{
    private const string UPLOAD_PREFIX = 'uploads/';

    public function store(Request $request, Product $product): RedirectResponse
    {
        $request->validate([
            'images' => ['required', 'array', 'max:6'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp,gif,bmp', 'max:12288'],
        ]);

        $position = (int) $product->images()->max('position') + 1;

        /** @var UploadedFile $file */
        foreach ($request->file('images', []) as $index => $file) {
            $product->images()->create([
                'path' => self::UPLOAD_PREFIX.$this->compressAndStore($file, $index),
                'alt' => $product->name['en'] ?? $product->slug,
                'position' => $position,
            ]);

            $position++;
        }

        return back();
    }

    /**
     * Scale the upload down to the configured bounds, re-encode it and store it.
     */
    private function compressAndStore(UploadedFile $file, int $index): string
    {
        $config = config('images.products');

        try {
            $stored = Image::fromUpload($file)
                ->orient()
                ->scale($config['max_width'], $config['max_height'])
                ->optimize($config['format'], $config['quality'])
                ->store('products', 'public_uploads');
        } catch (ImageException $e) {
            report($e);

            throw ValidationException::withMessages([
                "images.{$index}" => __('This image could not be processed.'),
            ]);
        }

        if ($stored === false) {
            throw ValidationException::withMessages([
                "images.{$index}" => __('This image could not be stored.'),
            ]);
        }

        return $stored;
    }

    public function update(Request $request, ProductImage $productImage): RedirectResponse
    {
        $validated = $request->validate([
            'direction' => ['required', Rule::in(['up', 'down'])],
        ]);

        $neighbor = ProductImage::query()
            ->where('product_id', $productImage->product_id)
            ->when(
                $validated['direction'] === 'up',
                fn ($query) => $query->where('position', '<', $productImage->position)->orderByDesc('position'),
                fn ($query) => $query->where('position', '>', $productImage->position)->orderBy('position'),
            )
            ->first();

        if ($neighbor !== null) {
            [$productImage->position, $neighbor->position] = [$neighbor->position, $productImage->position];
            $productImage->save();
            $neighbor->save();
        }

        return back();
    }

    public function destroy(ProductImage $productImage): RedirectResponse
    {
        if (str_starts_with($productImage->path, self::UPLOAD_PREFIX)) {
            Storage::disk('public_uploads')->delete(
                substr($productImage->path, strlen(self::UPLOAD_PREFIX)),
            );
        }

        $productImage->delete();

        return back();
    }
}

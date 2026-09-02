<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ProductImageService
{
    public function store(Product $product, array $files): void
    {
        foreach ($files as $index => $file) {
            /** @var UploadedFile $file */
            $path = $file->store('products', 'public');

            $product->images()->create([
                'path' => $path,
                'is_main' => $index === 0 && $product->images()->count() === 0,
                'order' => $product->images()->count(),
            ]);
        }
    }

    public function delete(int $imageId): void
    {
        $image = $product = \App\Models\ProductImage::findOrFail($imageId);
        Storage::disk('public')->delete($image->path);
        $image->delete();
    }

    public function setMain(int $imageId): void
    {
        $image = \App\Models\ProductImage::findOrFail($imageId);

        $image->product->images()->update(['is_main' => false]);
        $image->update(['is_main' => true]);
    }
}

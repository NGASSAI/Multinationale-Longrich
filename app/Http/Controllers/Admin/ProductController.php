<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProductRequest;
use App\Http\Requests\Admin\UpdateProductRequest;
use App\Models\Product;
use App\Services\ProductImageService;
use Illuminate\Support\Str;
use Inertia\Inertia;

class ProductController extends Controller
{
    public function __construct(protected ProductImageService $imageService) {}

    public function index()
    {
        return Inertia::render('Admin/Products/Index', [
            'products' => Product::with(['category', 'mainImage'])
                ->latest()
                ->paginate(20),
        ]);
    }

    public function store(StoreProductRequest $request)
    {
        $data = $request->validated();
        $data['slug'] = Str::slug($data['name']) . '-' . Str::random(6);

        $product = Product::create($data);

        if ($request->hasFile('images')) {
            $this->imageService->store($product, $request->file('images'));
        }

        return back()->with('status', 'Produit créé avec succès.');
    }

    public function update(UpdateProductRequest $request, Product $product)
    {
        $data = $request->validated();
        $product->update($data);

        if ($request->hasFile('images')) {
            $this->imageService->store($product, $request->file('images'));
        }

        return back()->with('status', 'Produit mis à jour.');
    }

    public function destroy(Product $product)
    {
        foreach ($product->images as $image) {
            \Storage::disk('public')->delete($image->path);
        }

        $product->delete();

        return back()->with('status', 'Produit supprimé.');
    }
}

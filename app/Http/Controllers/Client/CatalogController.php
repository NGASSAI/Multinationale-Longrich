<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CatalogController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::query()
            ->with(['category', 'mainImage'])
            ->where('is_active', true)
            ->when($request->category, fn ($q, $slug) =>
                $q->whereHas('category', fn ($q) => $q->where('slug', $slug))
            )
            ->when($request->search, fn ($q, $search) =>
                $q->where('name', 'ilike', "%{$search}%")
            )
            ->when($request->sort === 'price_asc', fn ($q) => $q->orderBy('price', 'asc'))
            ->when($request->sort === 'price_desc', fn ($q) => $q->orderBy('price', 'desc'))
            ->when(!$request->sort, fn ($q) => $q->latest())
            ->paginate(12)
            ->withQueryString();

        return Inertia::render('Client/Catalog', [
            'products' => $products,
            'categories' => Category::where('is_active', true)->get(),
            'filters' => $request->only(['category', 'search', 'sort']),
        ]);
    }

    public function show(Product $product)
    {
        abort_if(!$product->is_active, 404);

        $product->increment('views_count');

        $product->load(['category', 'images', 'comments' => function ($q) {
            $q->where('is_approved', true)->with('user:id,name,avatar')->latest();
        }]);

        return Inertia::render('Client/ProductShow', [
            'product' => $product,
        ]);
    }
}

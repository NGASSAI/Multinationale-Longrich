<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_creation_decreases_stock(): void
    {
        $category = Category::create(["name" => "Test", "slug" => "test"]);
        $product = Product::create([
            "category_id" => $category->id, "name" => "Produit Test",
            "slug" => "produit-test", "description" => "Test",
            "price" => 1000, "stock" => 10,
        ]);

        $service = app(OrderService::class);
        $order = $service->createOrder([
            "client_name" => "Client Test", "client_phone" => "0600000000",
            "source" => "website", "payment_method" => "cash_on_delivery",
        ], [["product_id" => $product->id, "quantity" => 3]]);

        $this->assertEquals(7, $product->fresh()->stock);
        $this->assertEquals(3000, $order->total);
    }

    public function test_order_fails_with_insufficient_stock(): void
    {
        $category = Category::create(["name" => "Test", "slug" => "test"]);
        $product = Product::create([
            "category_id" => $category->id, "name" => "Produit Test",
            "slug" => "produit-test", "description" => "Test",
            "price" => 1000, "stock" => 2,
        ]);

        $this->expectException(\RuntimeException::class);

        app(OrderService::class)->createOrder([
            "client_name" => "Client Test", "client_phone" => "0600000000",
            "source" => "website", "payment_method" => "cash_on_delivery",
        ], [["product_id" => $product->id, "quantity" => 5]]);
    }
}

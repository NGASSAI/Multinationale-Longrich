<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderService
{
    /**
     * Crée une commande à partir d'un panier : [['product_id' => int, 'quantity' => int], ...]
     */
    public function createOrder(array $orderData, array $cartItems): Order
    {
        return DB::transaction(function () use ($orderData, $cartItems) {
            $total = 0;
            $itemsToCreate = [];

            foreach ($cartItems as $item) {
                $product = Product::findOrFail($item['product_id']);

                if ($product->stock < $item['quantity']) {
                    throw new \RuntimeException("Stock insuffisant pour \"{$product->name}\" (disponible : {$product->stock}).");
                }

                $unitPrice = $product->is_on_sale ? $product->promo_price : $product->price;
                $subtotal = $unitPrice * $item['quantity'];
                $total += $subtotal;

                $itemsToCreate[] = [
                    'product_id'   => $product->id,
                    'product_name' => $product->name,
                    'unit_price'   => $unitPrice,
                    'quantity'     => $item['quantity'],
                    'subtotal'     => $subtotal,
                ];

                // Réserve le stock immédiatement à la commande (évite la survente)
                $product->decrement('stock', $item['quantity']);
            }

            $order = Order::create([
                'order_number'   => 'LGH-' . strtoupper(Str::random(8)),
                'user_id'        => $orderData['user_id'] ?? null,
                'client_name'    => $orderData['client_name'],
                'client_phone'   => $orderData['client_phone'],
                'client_address' => $orderData['client_address'] ?? null,
                'source'         => $orderData['source'] ?? 'website',
                'payment_method' => $orderData['payment_method'] ?? 'cash_on_delivery',
                'notes'          => $orderData['notes'] ?? null,
                'total'          => $total,
                'status'         => 'pending',
            ]);

            foreach ($itemsToCreate as $itemData) {
                $order->items()->create($itemData);
            }

            return $order->load('items');
        });
    }
}

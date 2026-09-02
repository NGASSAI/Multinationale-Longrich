<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class StatsController extends Controller
{
    public function index(Request $request)
    {
        $period = $request->input('period', '30'); // jours
        $from = now()->subDays((int) $period);

        return Inertia::render('Admin/Stats/Index', [
            'summary'          => $this->summary($from),
            'revenueByDay'     => $this->revenueByDay($from),
            'topClients'       => $this->topClients($from),
            'topProducts'      => $this->topProducts($from),
            'ordersBySource'   => $this->ordersBySource($from),
            'conversionRate'   => $this->conversionRate($from),
            'period'           => $period,
        ]);
    }

    private function summary($from): array
    {
        $paidOrders = Order::where('payment_status', 'paid')->where('created_at', '>=', $from);

        return [
            'total_revenue'      => (clone $paidOrders)->sum('total'),
            'total_orders'       => Order::where('created_at', '>=', $from)->count(),
            'pending_orders'     => Order::where('status', 'pending')->count(),
            'total_clients'      => User::role('client')->count(),
            'new_clients'        => User::role('client')->where('created_at', '>=', $from)->count(),
            'total_products'     => Product::where('is_active', true)->count(),
            'low_stock_products' => Product::where('is_active', true)->where('stock', '<=', 5)->count(),
        ];
    }

    private function revenueByDay($from): array
    {
        return Order::where('payment_status', 'paid')
            ->where('created_at', '>=', $from)
            ->selectRaw('DATE(created_at) as date, SUM(total) as revenue, COUNT(*) as orders_count')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->toArray();
    }

    // Clients qui achètent le plus (montant total dépensé)
    private function topClients($from): array
    {
        return Order::query()
            ->whereNotNull('user_id')
            ->where('payment_status', 'paid')
            ->where('created_at', '>=', $from)
            ->join('users', 'users.id', '=', 'orders.user_id')
            ->selectRaw('users.id, users.name, users.email, SUM(orders.total) as total_spent, COUNT(orders.id) as orders_count')
            ->groupBy('users.id', 'users.name', 'users.email')
            ->orderByDesc('total_spent')
            ->limit(10)
            ->get()
            ->toArray();
    }

    // Produits les plus vendus (quantité) et les plus aimés
    private function topProducts($from): array
    {
        $bestSelling = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.created_at', '>=', $from)
            ->selectRaw('order_items.product_id, order_items.product_name, SUM(order_items.quantity) as total_sold, SUM(order_items.subtotal) as total_revenue')
            ->groupBy('order_items.product_id', 'order_items.product_name')
            ->orderByDesc('total_sold')
            ->limit(10)
            ->get();

        $mostLiked = Product::orderByDesc('likes_count')
            ->limit(10)
            ->get(['id', 'name', 'likes_count', 'comments_count']);

        return [
            'best_selling' => $bestSelling,
            'most_liked'   => $mostLiked,
        ];
    }

    // Répartition des commandes par canal (site, WhatsApp, téléphone, admin)
    private function ordersBySource($from): array
    {
        return Order::where('created_at', '>=', $from)
            ->selectRaw('source, COUNT(*) as count')
            ->groupBy('source')
            ->get()
            ->toArray();
    }

    // Taux de conversion approximatif : visiteurs (vues produits) vs commandes
    private function conversionRate($from): array
    {
        $totalViews = Product::sum('views_count');
        $totalOrders = Order::where('created_at', '>=', $from)->count();

        $rate = $totalViews > 0 ? round(($totalOrders / $totalViews) * 100, 2) : 0;

        return [
            'total_views'  => $totalViews,
            'total_orders' => $totalOrders,
            'rate'         => $rate,
        ];
    }
}


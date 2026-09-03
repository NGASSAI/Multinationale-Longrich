<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        return Inertia::render("SuperAdmin/Dashboard", [
            "system_health" => $this->systemHealth(),
            "recent_activity" => ActivityLog::with("user:id,name")->latest()->limit(20)->get(),
            "counts" => [
                "users" => User::count(),
                "admins" => User::role("admin")->count(),
                "products" => Product::count(),
                "orders" => Order::count(),
            ],
        ]);
    }

    private function systemHealth(): array
    {
        $dbStatus = "ok";
        $dbLatencyMs = null;

        try {
            $start = microtime(true);
            DB::select("select 1");
            $dbLatencyMs = round((microtime(true) - $start) * 1000, 2);
        } catch (\Exception $e) {
            $dbStatus = "error";
        }

        return [
            "database_status"  => $dbStatus,
            "database_latency" => $dbLatencyMs,
            "queue_connection" => config("queue.default"),
            "broadcast_driver" => config("broadcasting.default"),
            "app_env"          => config("app.env"),
            "php_version"      => PHP_VERSION,
            "laravel_version"  => app()->version(),
        ];
    }
}

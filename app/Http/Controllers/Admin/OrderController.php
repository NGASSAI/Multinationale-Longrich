<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreManualOrderRequest;
use App\Http\Requests\Admin\UpdateOrderStatusRequest;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Notifications\OrderStatusUpdated;
use App\Models\ActivityLog;
class OrderController extends Controller
{
    public function __construct(protected OrderService $orderService) {}

    public function index()
    {
        return Inertia::render('Admin/Orders/Index', [
            'orders' => Order::with(['items', 'user:id,name'])
                ->latest()
                ->paginate(20),
        ]);
    }

    public function show(Order $order)
    {
        return Inertia::render('Admin/Orders/Show', [
            'order' => $order->load(['items', 'user:id,name,email,phone', 'confirmedBy:id,name']),
        ]);
    }

    // Création manuelle par l'admin (client contacté via WhatsApp / téléphone)
    public function store(StoreManualOrderRequest $request)
    {
        $order = $this->orderService->createOrder([
            'client_name'    => $request->client_name,
            'client_phone'   => $request->client_phone,
            'client_address' => $request->client_address,
            'payment_method' => $request->payment_method,
            'notes'          => $request->notes,
            'source'         => $request->source, // whatsapp, phone_call, admin
        ], $request->items);


        return redirect()->route('admin.orders.show', $order)
            ->with('status', 'Commande créée avec succès.');
    }

    // Confirmation / mise à jour du statut
    public function updateStatus(UpdateOrderStatusRequest $request, Order $order)
    {
        $data = ['status' => $request->status];

        if ($request->status === 'confirmed' && $order->status === 'pending') {
            $data['confirmed_by'] = Auth::id();
            $data['confirmed_at'] = now();
        }

        $order->update($data);
        ActivityLog::record('order_status_changed', "Commande {$order->order_number} → {$request->status}");
        if ($order->user) {
    $order->user->notify(new OrderStatusUpdated($order));
}

        // TODO : notification temps réel au client (module suivant)

        return back()->with('status', "Commande mise à jour : {$request->status}.");
    }

    public function updatePaymentStatus(Order $order)
    {
        $order->update(['payment_status' => $order->payment_status === 'paid' ? 'unpaid' : 'paid']);

        return back()->with('status', 'Statut de paiement mis à jour.');
    }

    public function cancel(Order $order)
    {
        // Si annulée, on restitue le stock réservé
        if ($order->status !== 'cancelled') {
            foreach ($order->items as $item) {
                $item->product?->increment('stock', $item->quantity);
            }
        }

        $order->update(['status' => 'cancelled']);

        return back()->with('status', 'Commande annulée, stock restitué.');
    }
}

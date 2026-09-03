<?php


namespace App\Http\Controllers\Client;
use App\Notifications\NewOrderNotification;
use App\Http\Controllers\Controller;
use App\Http\Requests\Client\StoreOrderRequest;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Models\User;

class OrderController extends Controller
{
    public function __construct(protected OrderService $orderService) {}

    public function index()
    {
        $orders = Order::where('user_id', Auth::id())
            ->with('items')
            ->latest()
            ->paginate(10);

        return Inertia::render('Client/Orders/Index', ['orders' => $orders]);
    }

    public function store(StoreOrderRequest $request)
    {
        try {
            $order = $this->orderService->createOrder([
                'user_id'        => Auth::id(),
                'client_name'    => $request->client_name,
                'client_phone'   => $request->client_phone,
                'client_address' => $request->client_address,
                'payment_method' => $request->payment_method,
                'notes'          => $request->notes,
                'source'         => 'website',
            ], $request->items);
            $admins = User::role('admin')->get();
foreach ($admins as $admin) {
    $admin->notify(new NewOrderNotification($order));
}

            // TODO : notification temps réel à l'admin (module suivant)

            return redirect()->route('client.orders.show', $order)
                ->with('status', 'Commande passée avec succès ! Vous recevrez une confirmation sous peu.');
        } catch (\RuntimeException $e) {
            return back()->withErrors(['stock' => $e->getMessage()]);
        }
    }

   public function show(Order $order)
{
    $this->authorize('view', $order);

    return Inertia::render('Client/Orders/Show', [
        'order' => $order->load('items'),
    ]);
}
}

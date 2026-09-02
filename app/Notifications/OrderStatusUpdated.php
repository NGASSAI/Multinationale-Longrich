<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;

class OrderStatusUpdated extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Order $order)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toArray(object $notifiable): array
    {
        $labels = [
            'confirmed' => 'confirmée',
            'shipped'   => 'expédiée',
            'delivered' => 'livrée',
            'cancelled' => 'annulée',
        ];

        $label = $labels[$this->order->status] ?? $this->order->status;

        return [
            'title'    => 'Mise à jour de commande',
            'message'  => "Votre commande {$this->order->order_number} est maintenant {$label}.",
            'order_id' => $this->order->id,
            'url'      => route('client.orders.show', $this->order),
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }
}


<?php

use App\Models\Conversation;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('conversation.{conversationId}', function ($user, $conversationId) {
    $conversation = Conversation::find($conversationId);

    if (!$conversation) {
        return false;
    }

    // Autorisé si c'est le client de la conversation, ou n'importe quel admin
    return $conversation->client_id === $user->id || $user->hasRole('admin');
});

// Channel personnel pour les notifications (cloche)
Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

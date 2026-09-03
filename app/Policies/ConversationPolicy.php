<?php

namespace App\Policies;

use App\Models\Conversation;
use App\Models\User;

class ConversationPolicy
{
    public function view(User $user, Conversation $conversation): bool
    {
        return $user->hasRole("admin")
            || $user->hasRole("super_admin")
            || $conversation->client_id === $user->id;
    }
}

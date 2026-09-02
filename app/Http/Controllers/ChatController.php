<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Http\Requests\StoreMessageRequest;
use App\Models\Conversation;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ChatController extends Controller
{
    // Client : ouvre ou récupère sa conversation unique avec l'admin
    public function startOrShow()
    {
        $user = Auth::user();

        $conversation = Conversation::firstOrCreate(
            ['client_id' => $user->id],
            ['status' => 'open']
        );

        return Inertia::render('Client/Chat', [
            'conversation' => $conversation->load(['messages.sender:id,name,avatar']),
        ]);
    }

    // Admin : liste de toutes les conversations
    public function index()
    {
        $conversations = Conversation::with(['client:id,name,avatar', 'messages' => fn ($q) => $q->latest()->limit(1)])
            ->orderByDesc('last_message_at')
            ->paginate(20);

        return Inertia::render('Admin/Chat/Index', ['conversations' => $conversations]);
    }

    // Admin : ouvre une conversation précise
    public function show(Conversation $conversation)
    {
        // Assigne l'admin à la conversation s'il n'y en a pas encore
        if (!$conversation->admin_id) {
            $conversation->update(['admin_id' => Auth::id()]);
        }

        return Inertia::render('Admin/Chat/Show', [
            'conversation' => $conversation->load(['messages.sender:id,name,avatar', 'client:id,name,phone']),
        ]);
    }

    // Envoi de message (client ou admin, même endpoint)
    public function sendMessage(StoreMessageRequest $request, Conversation $conversation)
    {
        $user = Auth::user();

        abort_if(
            $conversation->client_id !== $user->id && !$user->hasRole('admin'),
            403
        );

        $message = $conversation->messages()->create([
            'sender_id' => $user->id,
            'type'      => $request->type ?? 'text',
            'content'   => $request->content,
        ]);

        $conversation->update(['last_message_at' => now()]);

        broadcast(new MessageSent($message))->toOthers();

        return response()->json(['message' => $message->load('sender:id,name,avatar')]);
    }

    // Marquer les messages comme lus
    public function markAsRead(Conversation $conversation)
    {
        $user = Auth::user();

        $conversation->messages()
            ->where('sender_id', '!=', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json(['status' => 'ok']);
    }
}

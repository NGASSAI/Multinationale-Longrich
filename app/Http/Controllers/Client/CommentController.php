<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\StoreCommentRequest;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    public function store(StoreCommentRequest $request, Product $product)
    {
        $product->comments()->create([
            'user_id'   => Auth::id(),
            'parent_id' => $request->parent_id,
            'comment'   => $request->comment,
        ]);

        $product->increment('comments_count');

        return back()->with('status', 'Commentaire publié.');
    }

    public function destroy(Product $product, \App\Models\ProductComment $comment)
    {
        // Seul l'auteur peut supprimer son propre commentaire
        if ($comment->user_id !== Auth::id()) {
            abort(403);
        }

        $comment->delete();
        $product->decrement('comments_count');

        return back()->with('status', 'Commentaire supprimé.');
    }
}

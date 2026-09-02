<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductComment;
use Inertia\Inertia;

class CommentModerationController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Comments/Index', [
            'comments' => ProductComment::with(['user:id,name', 'product:id,name,slug'])
                ->latest()
                ->paginate(20),
        ]);
    }

    public function toggleApproval(ProductComment $comment)
    {
        $comment->update(['is_approved' => !$comment->is_approved]);

        return back()->with('status', $comment->is_approved
            ? 'Commentaire republié.'
            : 'Commentaire masqué.');
    }

    public function destroy(ProductComment $comment)
    {
        $comment->product()->decrement('comments_count');
        $comment->delete();

        return back()->with('status', 'Commentaire supprimé par l\'administrateur.');
    }
}

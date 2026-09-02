<?php


namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;

class LikeController extends Controller
{
    public function toggle(Product $product)
{
    $user = Auth::user();

    /** @var \App\Models\ProductLike|null $like */
    $like = $product->likes()->where('user_id', $user->id)->first();

    if ($like) {
        $like->delete();
        $product->decrement('likes_count');
        $liked = false;
    } else {
        $product->likes()->create(['user_id' => $user->id]);
        $product->increment('likes_count');
        $liked = true;
    }

    return back()->with([
        'liked' => $liked,
        'likes_count' => $product->fresh()->likes_count,
    ]);
}
}

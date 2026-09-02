<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductComment extends Model
{
    protected $fillable = ['user_id', 'product_id', 'parent_id', 'comment', 'is_approved'];

    protected function casts(): array
    {
        return ['is_approved' => 'boolean'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(ProductComment::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(ProductComment::class, 'parent_id')->where('is_approved', true);
    }
}

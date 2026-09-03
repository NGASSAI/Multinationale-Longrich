<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    protected $fillable = ["user_id", "action", "description", "ip_address"];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function record(string $action, ?string $description = null): void
    {
        static::create([
            "user_id"    => auth()->id(),
            "action"     => $action,
            "description" => $description,
            "ip_address" => request()->ip(),
        ]);
    }
}


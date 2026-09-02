<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class VerifyIdentityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email'       => ['required', 'email'],
            'secret_name' => ['required', 'string'],
        ];
    }

    public function ensureIsNotRateLimited(): void
    {
        $key = $this->throttleKey();

        if (!RateLimiter::tooManyAttempts($key, 5)) {
            return;
        }

        event(new Lockout($this));
        $seconds = RateLimiter::availableIn($key);

        throw ValidationException::withMessages([
            'secret_name' => "Trop de tentatives. Réessayez dans {$seconds} secondes.",
        ]);
    }

    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->input('email')) . '|' . $this->ip());
    }
}

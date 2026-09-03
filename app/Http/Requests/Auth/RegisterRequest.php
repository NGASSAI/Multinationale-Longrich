<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

   public function rules(): array
{
    $passwordRule = Password::min(8)->mixedCase()->numbers()->symbols();

    if (!app()->environment('testing')) {
        $passwordRule->uncompromised();
    }

    return [
        'name'     => ['required', 'string', 'max:255'],
        'email'    => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
        'phone'    => ['nullable', 'string', 'max:20'],
        'password' => ['required', 'confirmed', $passwordRule],
    ];
}
    public function messages(): array
    {
        return [
            'email.unique' => 'Cet email est déjà utilisé.',
            'password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
        ];
    }
}

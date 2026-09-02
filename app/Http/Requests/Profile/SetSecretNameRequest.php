<?php
namespace App\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;

class SetSecretNameRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'secret_name'     => ['required', 'string', 'min:4', 'max:100'],
            'current_password' => ['required', 'current_password'],
        ];
    }

    public function messages(): array
    {
        return [
            'current_password.current_password' => 'Mot de passe actuel incorrect.',
            'secret_name.min' => 'Le nom secret doit contenir au moins 4 caractères.',
        ];
    }
}

<?php

namespace App\Http\Requests\Client;

use Illuminate\Foundation\Http\FormRequest;

class StoreCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'comment'   => ['required', 'string', 'min:2', 'max:1000'],
            'parent_id' => ['nullable', 'exists:product_comments,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'comment.required' => 'Le commentaire ne peut pas être vide.',
            'comment.max' => 'Le commentaire ne doit pas dépasser 1000 caractères.',
        ];
    }
}

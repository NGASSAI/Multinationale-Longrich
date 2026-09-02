<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('admin');
    }

    public function rules(): array
    {
        return [
            'category_id'       => ['required', 'exists:categories,id'],
            'name'              => ['required', 'string', 'max:255'],
            'description'       => ['required', 'string'],
            'price'             => ['required', 'numeric', 'min:0'],
            'promo_price'       => ['nullable', 'numeric', 'min:0', 'lt:price'],
            'stock'             => ['required', 'integer', 'min:0'],
            'sku'               => ['nullable', 'string', 'unique:products,sku'],
            'is_active'         => ['boolean'],
            'is_featured'       => ['boolean'],
            'meta_title'        => ['nullable', 'string', 'max:255'],
            'meta_description'  => ['nullable', 'string', 'max:500'],
            'images'            => ['nullable', 'array', 'max:8'],
            'images.*'          => ['image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'promo_price.lt' => 'Le prix promo doit être inférieur au prix normal.',
            'images.*.image' => 'Chaque fichier doit être une image valide.',
        ];
    }
}

<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreManualOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('admin');
    }

    public function rules(): array
    {
        return [
            'client_name'    => ['required', 'string', 'max:255'],
            'client_phone'   => ['required', 'string', 'max:20'],
            'client_address' => ['nullable', 'string', 'max:500'],
            'payment_method' => ['required', 'in:cash_on_delivery,mobile_money,other'],
            'source'         => ['required', 'in:whatsapp,phone_call,admin'],
            'notes'          => ['nullable', 'string', 'max:500'],

            'items'              => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity'   => ['required', 'integer', 'min:1'],
        ];
    }
}

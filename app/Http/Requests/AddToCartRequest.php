<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddToCartRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isCustomer() ?? false;
    }

    public function rules(): array
    {
        return [
            'product_variant_id' => ['required', 'integer', 'exists:product_variants,id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:99'],
        ];
    }

    public function messages(): array
    {
        return [
            'product_variant_id.required' => 'Pilih ukuran dan warna produk terlebih dahulu.',
            'product_variant_id.exists' => 'Varian produk tidak ditemukan.',
            'quantity.min' => 'Jumlah minimal 1 item.',
        ];
    }
}

<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'description' => ['required', 'string', 'max:5000'],
            'price' => ['required', 'integer', 'min:1000', 'max:999999999'],
            'compare_price' => ['nullable', 'integer', 'min:0', 'max:999999999', 'gt:price'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'is_active' => ['nullable', 'boolean'],
            'variants' => ['required', 'array', 'min:1'],
            'variants.*.id' => ['nullable', 'integer'],
            'variants.*.size' => ['required', 'string', 'max:30'],
            'variants.*.color' => ['required', 'string', 'max:50'],
            'variants.*.sku' => ['required', 'string', 'max:80', 'distinct'],
            'variants.*.stock' => ['required', 'integer', 'min:0', 'max:1000000'],
            'variants.*.is_active' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'variants.required' => 'Tambahkan minimal satu varian produk.',
            'variants.min' => 'Tambahkan minimal satu varian produk.',
            'variants.*.sku.distinct' => 'SKU tidak boleh duplikat dalam satu produk.',
            'variants.*.size.required' => 'Ukuran varian wajib diisi.',
            'variants.*.color.required' => 'Warna varian wajib diisi.',
            'variants.*.sku.required' => 'SKU varian wajib diisi.',
            'image.max' => 'Ukuran gambar maksimal 2 MB.',
            'image.mimes' => 'Gambar harus berformat JPEG, PNG, atau WebP.',
            'price.min' => 'Harga minimal Rp1.000.',
            'compare_price.gt' => 'Harga coret harus lebih besar dari harga jual.',
        ];
    }
}

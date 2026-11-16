<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isCustomer() ?? false;
    }

    public function rules(): array
    {
        return [
            'recipient_name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'string', 'max:30', 'regex:/^[0-9+\-\s()]+$/'],
            'province' => ['required', 'string', 'max:100'],
            'city' => ['required', 'string', 'max:100'],
            'address' => ['required', 'string', 'max:500'],
            'postal_code' => ['required', 'string', 'max:10', 'regex:/^[0-9]{4,10}$/'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'phone.regex' => 'Nomor telepon hanya boleh berisi angka dan simbol +, -, spasi.',
            'postal_code.regex' => 'Kode pos harus berupa 4–10 digit angka.',
            'address.required' => 'Alamat lengkap wajib diisi.',
        ];
    }
}

<?php

namespace App\Http\Requests\Admin;

use App\Enums\OrderStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOrderStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'order_status' => ['required', Rule::in(array_column(OrderStatus::cases(), 'value'))],
            'carrier' => ['nullable', 'string', 'max:60'],
            'tracking_number' => ['nullable', 'string', 'max:80'],
        ];
    }

    public function messages(): array
    {
        return [
            'order_status.required' => 'Pilih status pesanan tujuan.',
        ];
    }
}

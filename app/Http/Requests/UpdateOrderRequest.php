<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'order_items' => ['required', 'array', 'min:1'],
            'order_items.*.order_item_id' => ['nullable', 'exists:order_items,id'],
            'order_items.*.menu_item_id' => ['required', 'exists:menu_items,id'],
            'order_items.*.quantity' => ['required', 'integer', 'min:1', 'max:100'],
            'order_items.*.notes' => ['nullable', 'string', 'max:500'],
            'order_items.*.modifier_ids' => ['nullable', 'array'],
            'order_items.*.modifier_ids.*' => ['exists:modifiers,id'],
            'status' => ['sometimes', 'in:pending,confirmed,preparing,ready,completed,cancelled'],
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'order_items.required' => 'At least one order item is required.',
            'order_items.min' => 'At least one order item is required.',
            'order_items.*.menu_item_id.required' => 'Menu item is required for each order item.',
            'order_items.*.menu_item_id.exists' => 'One or more menu items do not exist.',
            'order_items.*.quantity.required' => 'Quantity is required for each order item.',
            'order_items.*.quantity.min' => 'Quantity must be at least 1.',
            'order_items.*.modifier_ids.*.exists' => 'One or more modifiers do not exist.',
            'status.in' => 'Invalid order status.',
        ];
    }
}

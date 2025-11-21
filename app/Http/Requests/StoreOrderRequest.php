<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
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
        $rules = [
            'order_type' => ['nullable', 'in:dine-in,takeaway'],
            'order_items' => ['required', 'array', 'min:1'],
            'order_items.*.menu_item_id' => ['required', 'exists:menu_items,id'],
            'order_items.*.quantity' => ['required', 'integer', 'min:1', 'max:100'],
            'order_items.*.notes' => ['nullable', 'string', 'max:500'],
            'order_items.*.modifier_ids' => ['nullable', 'array'],
            'order_items.*.modifier_ids.*' => ['exists:modifiers,id'],
        ];

        $orderType = $this->input('order_type', 'dine-in');

        // For dine-in orders, table_id is required
        if ($orderType === 'dine-in') {
            $rules['table_id'] = ['required', 'exists:tables,id'];
        } else {
            // For takeaway orders, table_id is optional
            $rules['table_id'] = ['nullable', 'exists:tables,id'];
        }

        // For takeaway orders, customer info is required
        if ($orderType === 'takeaway') {
            $rules['customer_name'] = ['required', 'string', 'max:255'];
            $rules['customer_phone'] = ['nullable', 'string', 'max:20'];
        } else {
            $rules['customer_name'] = ['nullable', 'string', 'max:255'];
            $rules['customer_phone'] = ['nullable', 'string', 'max:20'];
        }

        return $rules;
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'order_type.in' => 'Order type must be either dine-in or takeaway.',
            'table_id.required' => 'Table is required for dine-in orders.',
            'table_id.exists' => 'Selected table does not exist.',
            'customer_name.required' => 'Customer name is required for takeaway orders.',
            'customer_phone.required' => 'Customer phone is required for takeaway orders.',
            'order_items.required' => 'At least one order item is required.',
            'order_items.min' => 'At least one order item is required.',
            'order_items.*.menu_item_id.required' => 'Menu item is required for each order item.',
            'order_items.*.menu_item_id.exists' => 'One or more menu items do not exist.',
            'order_items.*.quantity.required' => 'Quantity is required for each order item.',
            'order_items.*.quantity.min' => 'Quantity must be at least 1.',
            'order_items.*.modifier_ids.*.exists' => 'One or more modifiers do not exist.',
        ];
    }
}

<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMenuItemRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Authorization is handled by middleware
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
            'name' => ['sometimes', 'string', 'max:255'],
            'price' => ['sometimes', 'numeric', 'min:0', 'max:99999.99'],
            'category_id' => ['sometimes', 'exists:categories,id'],
            'is_available' => ['sometimes', 'boolean'],
            'modifier_ids' => ['sometimes', 'array'],
            'modifier_ids.*' => ['exists:modifiers,id'],
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'name.string' => 'Menu item name must be a valid string.',
            'price.numeric' => 'Price must be a valid number.',
            'price.min' => 'Price cannot be negative.',
            'category_id.exists' => 'Selected category does not exist.',
            'modifier_ids.*.exists' => 'One or more selected modifiers do not exist.',
        ];
    }
}

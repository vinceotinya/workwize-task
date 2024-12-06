<?php

namespace App\Http\Requests\Product;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && (auth()->user()->isAdmin() || (auth()->user()->isSupplier() && $this->product->supplier_id === auth()->id()));
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
            'description' => ['nullable', 'string'],
            'price' => ['sometimes', 'numeric', 'min:0'],
            'sku' => ['sometimes', 'string', Rule::unique('products', 'sku')->ignore($this->product)],
            'stock' => ['sometimes', 'integer', 'min:0'],
            'status' => ['sometimes', 'in:active,inactive'],
            'supplier_id' => ['sometimes', 'integer', Rule::exists('users', 'id')->where('role', User::ROLE_SUPPLIER)],
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if (auth()->user()->isSupplier()) {
            $this->merge([
                'supplier_id' => auth()->id(),
            ]);
        }
    }
}

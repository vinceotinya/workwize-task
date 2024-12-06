<?php

namespace App\Http\Requests\Order;

use App\Models\OrderItem;
use Illuminate\Foundation\Http\FormRequest;

class UpdateDeliveryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->isSupplier() && $this->orderItem->product->supplier_id === auth()->id();
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'string', 'in:'. implode(',', OrderItem::STATUSES)],
            'delivery_time' => ['required', 'date', 'after:now'],
        ];
    }

    // protected function failedAuthorization()
    // {
    //     throw new \Illuminate\Auth\Access\AuthorizationException(
    //         'Only suppliers can update delivery information'
    //     );
    // }
}

<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Support\Str;

class OrderService
{
    /**
     * Create a new order.
     *
     * @param array $data
     * @param int $userId
     * @return Order
     */
    public function createOrder(array $data, int $userId): Order
    {
        $order = Order::create([
            'created_by' => $userId,
            'order_number' => 'ORD-' . Str::random(10),
            'status' => Order::STATUS_PENDING,
            'total' => 0,
        ]);

        $total = 0;
        foreach ($data['items'] as $item) {
            $product = Product::findOrFail($item['product_id']);
            $order->items()->create([
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'price' => $product->price
            ]);

            $total += $product->price * $item['quantity'];
        }

        $order->update(['total' => $total]);

        return $order->load('items.product');
    }

    /**
     * Update the delivery status of an order.
     *
     * @param OrderItem $orderItem
     * @param array $data
     * @return OrderItem
     */
    public function updateDelivery(OrderItem $orderItem, array $data): OrderItem
    {
        $orderItem->update([
            'status' => $data['status'],
            'delivery_time' => $data['delivery_time']
        ]);

        return $orderItem->fresh();
    }
}

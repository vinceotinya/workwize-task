<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    /**
     * Order item status constants.
     */
    public const STATUS_WAITING    = 'waiting';
    public const STATUS_DELIVERED  = 'delivered';
    public const STATUS_CANCELLED  = 'cancelled';
    public const STATUSES = [
        self::STATUS_WAITING,
        self::STATUS_DELIVERED,
        self::STATUS_CANCELLED,
    ];

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['order_id', 'product_id', 'quantity', 'price', 'status', 'delivery_time'];

    /**
     * Relationship with order.
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Relationship with product.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}

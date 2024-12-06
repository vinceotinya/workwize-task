<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    /**
     * Order status constants.
     */
    public const STATUS_PENDING    = 'pending';
    public const STATUS_DELIVERED  = 'delivered';
    public const STATUS_SHIPPED    = 'shipped';
    public const STATUS_PROCESSING = 'processing';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['created_by', 'order_number', 'status', 'total'];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'delivery_time' => 'datetime',
    ];

    /**
     * Relationship with order items.
     */
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Relationship with creator.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope to get orders by product supplier or all for admin.
     */
    public function scopeBySupplier($query, User $user)
    {
        return $query->when(
            $user->isAdmin(),
            function ($query) {
                return $query;
            },
            function ($query) use ($user) {
                return $query->whereHas('items.product.supplier', function ($query) use ($user) {
                    $query->where('id', $user->id);
            });
        });
    }
}

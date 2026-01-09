<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $table = 'order_items';

    /**
     * Mass assignable attributes
     */
    protected $fillable = [
        'order_id',
        'ItemCode',
        'Quantity',
        'Price',
    ];

    /**
     * Attribute casting
     */
    protected $casts = [
        'Quantity' => 'integer',
        'Price' => 'decimal:2',
    ];

    /**
     * Relationships
     */

    // Order item belongs to an Order
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}

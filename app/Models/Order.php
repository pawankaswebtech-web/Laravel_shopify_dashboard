<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $table = 'orders';

    /**
     * Mass assignable attributes
     */
    protected $fillable = [
        'user_id',
        'shopify_order_id',
        'clientemail',
        'clientname',
        'orderid',
        'shippingtypeName',
        'phone',
        'currency',
        'bill_name',
        'bill_street',
        'bill_street2',
        'bill_city',
        'bill_country',
        'bill_state',
        'bill_zipCode',
        'bill_phone',
        'ship_name',
        'ship_street',
        'ship_street2',
        'ship_city',
        'ship_country',
        'ship_state',
        'ship_zipCode',
        'ship_phone',
        'comments',
        'totalpaid',
        'coupon_code',
        'fromwebsite',
        'billingtype',
        'transactionid',
        'order_status',
        'payment_status',
        'fulfillment_status',
        'payment_method',
        'discount',
    ];

    /**
     * Attribute casting
     */
    protected $casts = [
        'totalpaid' => 'decimal:2',
    ];

    /**
     * Relationships
     */

    // Order belongs to a User (Shop)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Order has many Order Items
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
}

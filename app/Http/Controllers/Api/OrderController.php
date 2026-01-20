<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = Order::with('items')->get();
        $formattedOrders = $orders->map(function ($order) {
            return [
                'clientemail' => $order->clientemail,
                'clientname' => $order->clientname,
                'orderid' => $order->orderid,
                'shippingtypeName' => $order->shippingtypeName,
                'phone' => $order->phone,
                'currency' => $order->currency,
                'bill_name' => $order->bill_name,
                'bill_street' => $order->bill_street,
                'bill_street2' => $order->bill_street2,
                'bill_city' => $order->bill_city,
                'bill_country' => $order->bill_country,
                'bill_state' => $order->bill_state,
                'bill_zipCode' => $order->bill_zipCode,
                'bill_phone' => $order->bill_phone,
                'ship_name' => $order->ship_name,
                'ship_street' => $order->ship_street,
                'ship_street2' => $order->ship_street2,
                'ship_city' => $order->ship_city,
                'ship_country' => $order->ship_country,
                'ship_state' => $order->ship_state,
                'ship_zipCode' => $order->ship_zipCode,
                'ship_phone' => $order->ship_phone,
                'comments' => $order->comments,
                'totalpaid' => $order->totalpaid,
                'fromwebsite' => $order->fromwebsite,
                'billingtype' => $order->billingtype,
                'transactionid' => $order->transactionid,
                'payment_method' => $order->payment_method,
                'discount' => $order->discount,
                'coupon_code' => $order->coupon_code,
                'items' => $order->items->map(function ($item) {
                    return [
                        'item_code' => $item->ItemCode,
                        'quantity' => $item->Quantity,
                        'price' => $item->Price,
                    ];
                }),
            ];
        });
        return response()->json([
            'success' => true,
            'count'   => $formattedOrders->count(),
            'orders'  => $formattedOrders,
        ]);
    }

   public function show($userId)
{
    $order = Order::with('items')
        ->where('user_id', $userId)
        ->firstOrFail();

    $formattedOrder = [
        'clientemail' => $order->clientemail,
        'clientname' => $order->clientname,
        'orderid' => $order->orderid,
        'shippingtypeName' => $order->shippingtypeName,
        'phone' => $order->phone,
        'currency' => $order->currency,

        'bill_name' => $order->bill_name,
        'bill_street' => $order->bill_street,
        'bill_street2' => $order->bill_street2,
        'bill_city' => $order->bill_city,
        'bill_country' => $order->bill_country,
        'bill_state' => $order->bill_state,
        'bill_zipCode' => $order->bill_zipCode,
        'bill_phone' => $order->bill_phone,

        'ship_name' => $order->ship_name,
        'ship_street' => $order->ship_street,
        'ship_street2' => $order->ship_street2,
        'ship_city' => $order->ship_city,
        'ship_country' => $order->ship_country,
        'ship_state' => $order->ship_state,
        'ship_zipCode' => $order->ship_zipCode,
        'ship_phone' => $order->ship_phone,

        'comments' => $order->comments,
        'totalpaid' => $order->totalpaid,
        'fromwebsite' => $order->fromwebsite,
        'billingtype' => $order->billingtype,
        'transactionid' => $order->transactionid,
        'payment_method' => $order->payment_method,
        'coupon_code' => $order->coupon_code,
        'discount' => $order->discount,

        // ✅ ITEMS FROM order_items TABLE
        'items' => $order->items->map(function ($item) {
            return [
                'item_code' => $item->ItemCode,
                'quantity' => $item->Quantity,
                'price' => $item->Price,
            ];
        }),
    ];

    return response()->json([
        'success' => true,
        'order' => $formattedOrder,
    ]);
}

}

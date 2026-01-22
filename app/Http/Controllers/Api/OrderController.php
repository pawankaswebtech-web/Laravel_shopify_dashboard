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
                'id' => $order->id ,
                'store_id' => $order->user_id ,
                'clientname' => $order->clientname,
                'clientemail' => $order->clientemail,
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
            ->get();

            $formattedOrderbyid = $order->map(function ($order) {
                return [
                    'id' => $order->id ,
                    'id' => $order->user_id ,
                    'clientname' => $order->clientname,
                    'clientemail' => $order->clientemail,
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
                'count'   => $formattedOrderbyid->count(),
                'orders'  => $formattedOrderbyid,
            ]);
    }
    public function showOrderPrefix($orderId){
        
        $orderId = urldecode($orderId);
        $orderId = ltrim($orderId, '#');
    
        // Extract alphabetic prefix
        preg_match('/^([A-Za-z]+)/', $orderId, $matches);
        $alphaPrefix = $matches[1] ?? '';
    
        // Extract numeric part
        $numericPart = substr($orderId, strlen($alphaPrefix));
    
        // Remove last 4 digits ONLY if numeric part length > 4
        if (strlen($numericPart) > 4) {
            $numericPart = substr($numericPart, 0, -4);
        }
    
        // Final prefix
        $finalPrefix = $alphaPrefix . $numericPart;
    
        // Fetch orders
        $orders = Order::with('items')
            ->where('orderid', 'LIKE', $finalPrefix . '%')
            ->get();

        if ($orders->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No orders found for this prefix'
            ], 404);
        }
        return response()->json([
            'success' => true,
            'count' => $orders->count(),
            'prefix_used' => $finalPrefix,
            'orders' => $orders->map(function ($order) {
                return [
                    'id' => $order->id,
                    'storeid' => $order->user_id,
                    'clientname' => $order->clientname,
                    'clientemail' => $order->clientemail,
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
        
                    // 👇 items relation
                    'items' => $order->items->map(function ($item) {
                        return [
                            'item_code' => $item->ItemCode,
                            'quantity'  => $item->Quantity,
                            'price'     => $item->Price,
                        ];
                    }),
                ];
            }),
        ]);
        
    }
    public function showOrderId($Id)
    {
        $order = Order::with('items')
            ->where('id', $Id)
            ->first();

            $formattedOrder = [
                'id' => $order->id,
                'user_id' => $order->user_id,
            
                'clientname' => $order->clientname,
                'clientemail' => $order->clientemail,
                'orderid' => $order->orderid,
            
                'shippingtypeName' => $order->shippingtypeName,
                'phone' => $order->phone,
                'currency' => $order->currency,
            
                // Billing
                'bill_name' => $order->bill_name,
                'bill_street' => $order->bill_street,
                'bill_street2' => $order->bill_street2,
                'bill_city' => $order->bill_city,
                'bill_country' => $order->bill_country,
                'bill_state' => $order->bill_state,
                'bill_zipCode' => $order->bill_zipCode,
                'bill_phone' => $order->bill_phone,
            
                // Shipping
                'ship_name' => $order->ship_name,
                'ship_street' => $order->ship_street,
                'ship_street2' => $order->ship_street2,
                'ship_city' => $order->ship_city,
                'ship_country' => $order->ship_country,
                'ship_state' => $order->ship_state,
                'ship_zipCode' => $order->ship_zipCode,
                'ship_phone' => $order->ship_phone,
            
                // Meta
                'comments' => $order->comments,
                'totalpaid' => $order->totalpaid,
                'fromwebsite' => $order->fromwebsite,
                'billingtype' => $order->billingtype,
                'transactionid' => $order->transactionid,
                'payment_method' => $order->payment_method,
                'discount' => $order->discount,
                'coupon_code' => $order->coupon_code,
            
                // Items
                'items' => $order->items->map(function ($item) {
                    return [
                        'item_code' => $item->ItemCode,
                        'quantity'  => $item->Quantity,
                        'price'     => $item->Price,
                    ];
                }),
            ];
            
            return response()->json([
                'success' => true,
                'order'   => $formattedOrder,
            ]);
            
    }
    


}

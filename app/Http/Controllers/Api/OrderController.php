<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="OrderItem",
 *     type="object",
 *     @OA\Property(property="item_code", type="string", example="SKU123"),
 *     @OA\Property(property="quantity", type="integer", example=2),
 *     @OA\Property(property="price", type="number", format="float", example=49.99)
 * )
 * 
 * @OA\Schema(
 *     schema="Order",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="store_id", type="integer", example=10),
 *     @OA\Property(property="storeid", type="integer", example=10),
 *     @OA\Property(property="user_id", type="integer", example=5),
 *     @OA\Property(property="clientname", type="string", example="John Doe"),
 *     @OA\Property(property="clientemail", type="string", example="john@example.com"),
 *     @OA\Property(property="orderid", type="string", example="ORD123"),
 *     @OA\Property(property="shippingtypeName", type="string", example="Express"),
 *     @OA\Property(property="phone", type="string", example="9876543210"),
 *     @OA\Property(property="currency", type="string", example="USD"),
 *     @OA\Property(property="bill_name", type="string", example="John Doe"),
 *     @OA\Property(property="bill_street", type="string", example="Street 1"),
 *     @OA\Property(property="bill_street2", type="string", example="Street 2"),
 *     @OA\Property(property="bill_city", type="string", example="New York"),
 *     @OA\Property(property="bill_country", type="string", example="USA"),
 *     @OA\Property(property="bill_state", type="string", example="NY"),
 *     @OA\Property(property="bill_zipCode", type="string", example="10001"),
 *     @OA\Property(property="bill_phone", type="string", example="9876543210"),
 *     @OA\Property(property="ship_name", type="string", example="John Doe"),
 *     @OA\Property(property="ship_street", type="string", example="Street 1"),
 *     @OA\Property(property="ship_street2", type="string", example="Street 2"),
 *     @OA\Property(property="ship_city", type="string", example="New York"),
 *     @OA\Property(property="ship_country", type="string", example="USA"),
 *     @OA\Property(property="ship_state", type="string", example="NY"),
 *     @OA\Property(property="ship_zipCode", type="string", example="10001"),
 *     @OA\Property(property="ship_phone", type="string", example="9876543210"),
 *     @OA\Property(property="comments", type="string", example="Handle with care"),
 *     @OA\Property(property="totalpaid", type="number", format="float", example=150.75),
 *     @OA\Property(property="fromwebsite", type="string", example="Shopify"),
 *     @OA\Property(property="billingtype", type="string", example="Prepaid"),
 *     @OA\Property(property="transactionid", type="string", example="TXN12345"),
 *     @OA\Property(property="payment_method", type="string", example="credit_card"),
 *     @OA\Property(property="discount", type="number", format="float", example=10),
 *     @OA\Property(property="coupon_code", type="string", example="NEW10"),
 *     @OA\Property(
 *         property="items",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/OrderItem")
 *     )
 * )
 * 
 * @OA\Schema(
 *     schema="OrdersResponse",
 *     type="object",
 *     @OA\Property(property="success", type="boolean", example=true),
 *     @OA\Property(property="count", type="integer", example=2),
 *     @OA\Property(
 *         property="orders",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/Order")
 *     )
 * )
 * 
 * @OA\Schema(
 *     schema="SingleOrderResponse",
 *     type="object",
 *     @OA\Property(property="success", type="boolean", example=true),
 *     @OA\Property(property="order", ref="#/components/schemas/Order")
 * )
 * 
 * @OA\Schema(
 *     schema="OrderPrefixResponse",
 *     type="object",
 *     @OA\Property(property="success", type="boolean", example=true),
 *     @OA\Property(property="count", type="integer", example=2),
 *     @OA\Property(property="prefix_used", type="string", example="ORD123"),
 *     @OA\Property(
 *         property="orders",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/Order")
 *     )
 * )
 */
class OrderController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/ordersdetail",
     *     summary="Get all orders with items",
     *     description="Returns a list of orders along with their items",
     *     tags={"Orders"},
     *     @OA\Response(
     *         response=200,
     *         description="Orders fetched successfully",
     *         @OA\JsonContent(ref="#/components/schemas/OrdersResponse")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function index(Request $request)
    {
        $orders = Order::with('items')->get();
        $formattedOrders = $orders->map(function ($order) {
            return [
                'id' => $order->id,
                'store_id' => $order->user_id,
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

    /**
     * @OA\Get(
     *     path="/api/storeid/{userId}",
     *     summary="Get orders by user ID",
     *     description="Returns all orders with items for a specific user",
     *     tags={"Orders"},
     *     @OA\Parameter(
     *         name="userId",
     *         in="path",
     *         required=true,
     *         description="User ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Orders fetched successfully",
     *         @OA\JsonContent(ref="#/components/schemas/OrdersResponse")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Orders not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="No orders found")
     *         )
     *     )
     * )
     */
    public function show($userId)
    {
        $orders = Order::with('items')
            ->where('user_id', $userId)
            ->get();

        if ($orders->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No orders found'
            ], 404);
        }

        $formattedOrderbyid = $orders->map(function ($order) {
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

    /**
     * @OA\Get(
     *     path="/api/orderprefix/{orderId}",
     *     summary="Get orders by Order ID prefix",
     *     description="Returns all orders matching the order ID prefix",
     *     tags={"Orders"},
     *     @OA\Parameter(
     *         name="orderId",
     *         in="path",
     *         required=true,
     *         description="Order ID or prefix (e.g., ORD123, #ORD1234567)",
     *         @OA\Schema(type="string", example="ORD123")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Orders fetched successfully",
     *         @OA\JsonContent(ref="#/components/schemas/OrderPrefixResponse")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Orders not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="No orders found for this prefix")
     *         )
     *     )
     * )
     */
    public function showOrderPrefix($orderId)
    {
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

    /**
     * @OA\Get(
     *     path="/api/orderid/{id}",
     *     summary="Get order by ID",
     *     description="Returns a single order with items for a specific ID",
     *     tags={"Orders"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Order ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Order fetched successfully",
     *         @OA\JsonContent(ref="#/components/schemas/SingleOrderResponse")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Order not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Order not found")
     *         )
     *     )
     * )
     */
    public function showOrderId($Id)
    {
        $order = Order::with('items')
            ->where('id', $Id)
            ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found'
            ], 404);
        }

        $formattedOrder = [
            'id' => $order->id,
            'user_id' => $order->user_id,
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
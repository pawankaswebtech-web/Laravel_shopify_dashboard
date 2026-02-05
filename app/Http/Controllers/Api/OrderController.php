<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use OpenApi\Annotations as OA;
use Carbon\Carbon;

/**
 * @OA\Schema(
 *     schema="OrderItem",
 *     type="object",
 *     @OA\Property(property="item_code", type="string", example="RNG001"),
 *     @OA\Property(property="quantity", type="integer", example=1),
 *     @OA\Property(property="price", type="number", format="float", example=1500.50)
 * )
 * 
 * @OA\Schema(
 *     schema="Order",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="store_id", type="integer", example=10),
 *     @OA\Property(property="clientname", type="string", example="John Doe"),
 *     @OA\Property(property="clientemail", type="string", example="john@example.com"),
 *     @OA\Property(property="orderid", type="string", example="ORD123"),
 *     @OA\Property(property="shippingtypeName", type="string", example="Express"),
 *     @OA\Property(property="phone", type="string", example="9876543210"),
 *     @OA\Property(property="date", type="string", format="date", example="2024-01-15"),
 *     @OA\Property(property="currency", type="string", example="USD"),
 *     @OA\Property(property="bill_name", type="string", example="John Doe"),
 *     @OA\Property(property="bill_street", type="string", example="123 Main St"),
 *     @OA\Property(property="bill_street2", type="string", example="Apt 4B"),
 *     @OA\Property(property="bill_city", type="string", example="New York"),
 *     @OA\Property(property="bill_country", type="string", example="USA"),
 *     @OA\Property(property="bill_state", type="string", example="NY"),
 *     @OA\Property(property="bill_zipCode", type="string", example="10001"),
 *     @OA\Property(property="bill_phone", type="string", example="9876543210"),
 *     @OA\Property(property="ship_name", type="string", example="John Doe"),
 *     @OA\Property(property="ship_street", type="string", example="123 Main St"),
 *     @OA\Property(property="ship_street2", type="string", example="Apt 4B"),
 *     @OA\Property(property="ship_city", type="string", example="New York"),
 *     @OA\Property(property="ship_country", type="string", example="USA"),
 *     @OA\Property(property="ship_state", type="string", example="NY"),
 *     @OA\Property(property="ship_zipCode", type="string", example="10001"),
 *     @OA\Property(property="ship_phone", type="string", example="9876543210"),
 *     @OA\Property(property="comments", type="string", example="Handle with care"),
 *     @OA\Property(property="totalpaid", type="number", format="float", example=1500.50),
 *     @OA\Property(property="fromwebsite", type="string", example="Shopify"),
 *     @OA\Property(property="billingtype", type="string", example="Prepaid"),
 *     @OA\Property(property="transactionid", type="string", example="TXN12345"),
 *     @OA\Property(property="payment_method", type="string", example="credit_card"),
 *     @OA\Property(property="order_status", type="string", enum={"pending","paid"}, example="paid"),
 *     @OA\Property(property="fulfillment_status", type="string", enum={"fulfilled","unfulfilled","pending","cancelled","partial"}, example="fulfilled"),
 *     @OA\Property(property="discount", type="number", format="float", example=50.00),
 *     @OA\Property(property="coupon_code", type="string", example="SAVE10"),
 *     @OA\Property(property="items", type="array", @OA\Items(ref="#/components/schemas/OrderItem"))
 * )
 * 
 * @OA\Schema(
 *     schema="OrdersResponse",
 *     type="object",
 *     @OA\Property(property="success", type="boolean", example=true),
 *     @OA\Property(property="count", type="integer", example=2),
 *     @OA\Property(property="orders", type="array", @OA\Items(ref="#/components/schemas/Order"))
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
 *     @OA\Property(property="orders", type="array", @OA\Items(ref="#/components/schemas/Order"))
 * )
 * 
 * @OA\Schema(
 *     schema="OrderStatus",
 *     type="object",
 *     @OA\Property(property="success", type="boolean", example=true),
 *     @OA\Property(property="count", type="integer", example=5),
 *     @OA\Property(property="orders", type="array", @OA\Items(ref="#/components/schemas/Order"))
 * )
 * 
 * @OA\Schema(
 *     schema="OrderByDate",
 *     type="object",
 *     @OA\Property(property="success", type="boolean", example=true),
 *     @OA\Property(property="date", type="string", format="date", example="2024-01-15"),
 *     @OA\Property(property="count", type="integer", example=3),
 *     @OA\Property(property="orders", type="array", @OA\Items(ref="#/components/schemas/Order"))
 * )
 * 
 * @OA\Schema(
 *     schema="ErrorResponse",
 *     type="object",
 *     @OA\Property(property="success", type="boolean", example=false),
 *     @OA\Property(property="message", type="string", example="No orders found")
 * )
 * 
 * @OA\Schema(
 *     schema="ValidationErrorResponse",
 *     type="object",
 *     @OA\Property(property="success", type="boolean", example=false),
 *     @OA\Property(property="errors", type="object", @OA\Property(property="field_name", type="array", @OA\Items(type="string", example="The field is required.")))
 * )
 */
class OrderController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/ordersdetail",
     *     summary="Get orders with filters",
     *     description="Filter orders using JSON request body",
     *     tags={"Orders"},
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="storeid", type="integer", example=15),
     *             @OA\Property(property="order_status", type="string", enum={"pending","paid"}, example="paid"),
     *             @OA\Property(property="start_date", type="string", format="date", example="2024-01-01"),
     *             @OA\Property(property="end_date", type="string", format="date", example="2024-01-31")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Orders fetched successfully", @OA\JsonContent(ref="#/components/schemas/OrdersResponse")),
     *     @OA\Response(response=404, description="No orders found", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response=422, description="Validation error", @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse"))
     * )
     */
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'storeid'      => 'nullable|integer',
            'order_status' => 'nullable|in:pending,paid',
            'start_date'   => 'nullable|date_format:Y-m-d',
            'end_date'     => 'nullable|date_format:Y-m-d|after_or_equal:start_date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors()
            ], 422);
        }

        $orders = Order::with('items')
            ->when($request->storeid, function ($q) use ($request) {
                $q->where('user_id', $request->storeid);
            })
            ->when($request->order_status, function ($q) use ($request) {
                $q->where('order_status', $request->order_status);
            })
            ->when($request->start_date && $request->end_date, function ($q) use ($request) {
                $q->whereBetween('created_at', [
                    $request->start_date . ' 00:00:00',
                    $request->end_date . ' 23:59:59',
                ]);
            })
            ->get();

        if ($orders->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No orders found'
            ], 404);
        }

        $formattedOrders = $orders->map(function ($order) {
            return [
                'id' => $order->id,
                'store_id' => $order->user_id,
                'clientname' => $order->clientname,
                'clientemail' => $order->clientemail,
                'orderid' => $order->orderid,
                'shippingtypeName' => $order->shippingtypeName,
                'phone' => $order->phone,
                'date' => $order->created_at->toDateString(),
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
                'order_status' => $order->order_status,
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
 * @OA\Post(
 *     path="/api/ordersdetail/storeid/{storeid}",
 *     summary="Get orders by user ID (with filters)",
 *     description="Returns all orders with items for a specific user using JSON body filters",
 *     tags={"Orders"},
 *
 *     @OA\Parameter(
 *         name="storeid",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="integer", example=15)
 *     ),
 *
 *     @OA\RequestBody(
 *         required=false,
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="order_status", type="string", enum={"pending","paid"}, example="paid"),
 *             @OA\Property(property="start_date", type="string", format="date", example="2024-01-01"),
 *             @OA\Property(property="end_date", type="string", format="date", example="2024-01-31")
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=200,
 *         description="Orders fetched successfully"
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Orders not found"
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Validation error"
 *     )
 * )
 */

    public function show(Request $request, $storeid)
{
    $validator = Validator::make($request->all(), [
        'order_status' => 'nullable|in:pending,paid',
        'start_date'   => 'nullable|date_format:Y-m-d',
        'end_date'     => 'nullable|date_format:Y-m-d|after_or_equal:start_date',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'errors'  => $validator->errors()
        ], 422);
    }

    $orders = Order::with('items')
        ->where('user_id', $storeid)
        ->when($request->order_status, fn($q) =>
            $q->where('order_status', $request->order_status)
        )
        ->when($request->start_date && $request->end_date, fn($q) =>
            $q->whereBetween('created_at', [
                $request->start_date . ' 00:00:00',
                $request->end_date . ' 23:59:59',
            ])
        )
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
                'store_id' => $order->user_id,
                'clientname' => $order->clientname,
                'clientemail' => $order->clientemail,
                'orderid' => $order->orderid,
                'shippingtypeName' => $order->shippingtypeName,
                'phone' => $order->phone,
                'date' => $order->created_at->toDateString(),
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
 * @OA\Post(
 *     path="/api/ordersdetail/orderprefix/{orderId}",
 *     summary="Get orders by Order ID prefix",
 *     description="Returns all orders matching the order ID prefix",
 *     tags={"Orders"},
 *
 *     @OA\Parameter(
 *         name="orderId",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="string", example="ABC123")
 *     ),
 *
 *     @OA\RequestBody(
 *         required=false,
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="storeid", type="integer", example=15),
 *             @OA\Property(property="order_status", type="string", enum={"pending","paid"}, example="paid"),
 *             @OA\Property(property="start_date", type="string", format="date", example="2024-01-01"),
 *             @OA\Property(property="end_date", type="string", format="date", example="2024-01-31")
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=200,
 *         description="Orders fetched successfully"
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Orders not found"
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Validation error"
 *     )
 * )
 */

    public function showOrderPrefix(Request $request, $orderId)
    {
        $validator = Validator::make($request->all(), [
            'storeid'      => 'nullable|integer',
            'order_status' => 'nullable|in:pending,paid',
            'start_date'   => 'nullable|date_format:Y-m-d',
            'end_date'     => 'nullable|date_format:Y-m-d|after_or_equal:start_date',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors()
            ], 422);
        }
        
        $status = $request->query('status'); 
        $date   = $request->query('date');  

        $orderId = urldecode($orderId);
        $orderId = ltrim($orderId, '#');

        preg_match('/^([A-Za-z]+)/', $orderId, $matches);
        $alphaPrefix = $matches[1] ?? '';
        $numericPart = substr($orderId, strlen($alphaPrefix));

        if (strlen($numericPart) > 4) {
            $numericPart = substr($numericPart, 0, -4);
        }

        $finalPrefix = $alphaPrefix . $numericPart;

        $orders = Order::with('items')
            ->where('orderid', 'LIKE', $finalPrefix . '%')
            ->when($request->storeid, function ($q) use ($request) {
                $q->where('user_id', $request->storeid);
            })
            ->when($request->order_status, function ($q) use ($request) {
                $q->where('order_status', $request->order_status);
            })
            ->when($request->start_date && $request->end_date, function ($q) use ($request) {
                $q->whereBetween('created_at', [
                    $request->start_date . ' 00:00:00',
                    $request->end_date . ' 23:59:59',
                ]);
            })
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
                    'store_id' => $order->user_id,
                    'clientname' => $order->clientname,
                    'clientemail' => $order->clientemail,
                    'orderid' => $order->orderid,
                    'shippingtypeName' => $order->shippingtypeName,
                    'phone' => $order->phone,
                    'date' => $order->created_at->toDateString(),
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
 * @OA\Post(
 *     path="/api/ordersdetail/orderid/{id}",
 *     summary="Get order by ID",
 *     description="Returns a single order with items for a specific ID",
 *     tags={"Orders"},
 *
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="integer", example=15)
 *     ),
 *
 *     @OA\RequestBody(
 *         required=false,
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="storeid", type="integer", example=15),
 *             @OA\Property(property="order_status", type="string", enum={"pending","paid"}, example="paid"),
 *             @OA\Property(property="start_date", type="string", format="date", example="2024-01-01"),
 *             @OA\Property(property="end_date", type="string", format="date", example="2024-01-31")
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=200,
 *         description="Orders fetched successfully"
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Orders not found"
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Validation error"
 *     )
 * )
 */

    public function showOrderId(Request $request, $Id)
    {
        $validator = Validator::make($request->all(), [
            'storeid'      => 'nullable|integer',
            'order_status' => 'nullable|in:pending,paid',
            'start_date'   => 'nullable|date_format:Y-m-d',
            'end_date'     => 'nullable|date_format:Y-m-d|after_or_equal:start_date',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors()
            ], 422);
        }
        
        $status = $request->query('status'); 
        $date   = $request->query('date');  

        $order = Order::with('items')
            ->where('id', $Id)
            ->when($request->storeid, function ($q) use ($request) {
                $q->where('user_id', $request->storeid);
            })
            ->when($request->order_status, function ($q) use ($request) {
                $q->where('order_status', $request->order_status);
            })
            ->when($request->start_date && $request->end_date, function ($q) use ($request) {
                $q->whereBetween('created_at', [
                    $request->start_date . ' 00:00:00',
                    $request->end_date . ' 23:59:59',
                ]);
            })
            ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found'
            ], 404);
        }

        $formattedOrder = [
            'id' => $order->id,
            'store_id' => $order->user_id,
            'clientname' => $order->clientname,
            'clientemail' => $order->clientemail,
            'orderid' => $order->orderid,
            'shippingtypeName' => $order->shippingtypeName,
            'phone' => $order->phone,
            'date' => $order->created_at->toDateString(),
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

    /**
     * @OA\Get(
     *     path="/api/ordersdetail/orderstatus",
     *     summary="Get orders by fulfillment status",
     *     description="Fetch all orders filtered by fulfillment status",
     *     operationId="getOrdersByStatus",
     *     tags={"Orders"},
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="order_status", type="string", enum={"pending","paid"}, example="paid"),
     *         )
     *     ),
     *     @OA\Response(response=200, description="Orders fetched successfully", @OA\JsonContent(ref="#/components/schemas/OrderStatus")),
     *     @OA\Response(response=404, description="Orders not found", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response=422, description="Validation error", @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse"))
     * )
     */
    public function getOrdersByStatus(Request $request)
    {
        $request->validate([
            'status' => 'required|in:fulfilled,unfulfilled'
        ]);
        
        $status = $request->query('status');

        $orders = Order::with('items')
            ->when($status, function ($query) use ($status) {
                $query->where('fulfillment_status', $status);
            })
            ->get();

        if ($orders->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No orders found'
            ], 404);
        }

        $formattedOrderbystatus = $orders->map(function ($order) {
            return [
                'id' => $order->id,
                'store_id' => $order->user_id,
                'clientname' => $order->clientname,
                'clientemail' => $order->clientemail,
                'orderid' => $order->orderid,
                'shippingtypeName' => $order->shippingtypeName,
                'phone' => $order->phone,
                'date' => $order->created_at->toDateString(),
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
            'count'   => $formattedOrderbystatus->count(),
            'orders'  => $formattedOrderbystatus,
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/ordersdetail/orderdate",
     *     summary="Get orders by created date",
     *     description="Fetch all orders by matching DATE",
     *     tags={"Orders"},
     *    @OA\RequestBody(
     *         required=false,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="start_date", type="string", format="date", example="2024-01-01"),
     *             @OA\Property(property="end_date", type="string", format="date", example="2024-01-31")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Orders fetched successfully", @OA\JsonContent(ref="#/components/schemas/OrderByDate")),
     *     @OA\Response(response=404, description="Orders not found", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response=422, description="Validation error", @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse"))
     * )
     */
    public function getOrdersByDate(Request $request)
    {
        $request->validate([
            'date' => 'required|date'
        ]);
    
        $date = Carbon::parse($request->query('date'))->toDateString();
    
        $orders = Order::with('items')
            ->whereDate('created_at', $date)
            ->get();
    
        if ($orders->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No orders found for date: ' . $date
            ], 404);
        }
    
        $formattedOrders = $orders->map(function ($order) {
            return [
                'id' => $order->id,
                'store_id' => $order->user_id,
                'clientname' => $order->clientname,
                'clientemail' => $order->clientemail,
                'orderid' => $order->orderid,
                'fulfillment_status' => $order->fulfillment_status,
                'shippingtypeName' => $order->shippingtypeName,
                'phone' => $order->phone,
                'date' => optional($order->created_at)->toDateString(),
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
        });
    
        return response()->json([
            'success' => true,
            'date'    => $date,
            'count'   => $formattedOrders->count(),
            'orders'  => $formattedOrders,
        ]);
    }
}
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
 *     @OA\Property(property="item_code", type="string"),
 *     @OA\Property(property="quantity", type="integer"),
 *     @OA\Property(property="price", type="number", format="float")
 * )
 * 
 * @OA\Schema(
 *     schema="OrderStatus",
 *     type="object",
 *    @OA\Property(property="success", type="boolean"),
 *     @OA\Property(property="status", type="string"),
 *     @OA\Property(property="count", type="integer")
 * )
 * @OA\Schema(
 *     schema="OrderByDate",
 *     type="object",
 *     @OA\Property(property="success", type="boolean"),
 *      @OA\Property(property="date", type="string"),
 *      @OA\Property(property="count", type="integer")
 * )
 * 
 * @OA\Schema(
 *     schema="Order",
 *     type="object",
 *     @OA\Property(property="id", type="integer"),
 *     @OA\Property(property="store_id", type="integer"),
 *     @OA\Property(property="clientname", type="string"),
 *     @OA\Property(property="clientemail", type="string"),
 *     @OA\Property(property="orderid", type="string"),
 *     @OA\Property(property="shippingtypeName", type="string"),
 *     @OA\Property(property="phone", type="string"),
 *     @OA\Property(property="currency", type="string"),
 *     @OA\Property(property="bill_name", type="string"),
 *     @OA\Property(property="bill_street", type="string"),
 *     @OA\Property(property="bill_street2", type="string"),
 *     @OA\Property(property="bill_city", type="string"),
 *     @OA\Property(property="bill_country", type="string"),
 *     @OA\Property(property="bill_state", type="string"),
 *     @OA\Property(property="bill_zipCode", type="string"),
 *     @OA\Property(property="bill_phone", type="string"),
 *     @OA\Property(property="ship_name", type="string"),
 *     @OA\Property(property="ship_street", type="string"),
 *     @OA\Property(property="ship_street2", type="string"),
 *     @OA\Property(property="ship_city", type="string"),
 *     @OA\Property(property="ship_country", type="string"),
 *     @OA\Property(property="ship_state", type="string"),
 *     @OA\Property(property="ship_zipCode", type="string"),
 *     @OA\Property(property="ship_phone", type="string"),
 *     @OA\Property(property="comments", type="string"),
 *     @OA\Property(property="totalpaid", type="number", format="float"),
 *     @OA\Property(property="fromwebsite", type="string"),
 *     @OA\Property(property="billingtype", type="string"),
 *     @OA\Property(property="transactionid", type="string"),
 *     @OA\Property(property="payment_method", type="string"),
 *     @OA\Property(property="discount", type="number", format="float"),
 *     @OA\Property(property="coupon_code", type="string"),
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
 *     @OA\Property(property="success", type="boolean"),
 *     @OA\Property(property="count", type="integer"),
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
 *     @OA\Property(property="success", type="boolean"),
 *     @OA\Property(property="order", ref="#/components/schemas/Order")
 * )
 * 
 * @OA\Schema(
 *     schema="OrderPrefixResponse",
 *     type="object",
 *     @OA\Property(property="success", type="boolean"),
 *     @OA\Property(property="count", type="integer"),
 *     @OA\Property(property="prefix_used", type="string"),
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
 *     summary="Get orders with optional status and date filters",
 *     description="Returns a list of orders along with their items. Filters can be applied using status and date.",
 *     tags={"Orders"},
 *
 *     @OA\Parameter(
 *         name="status",
 *         in="query",
 *         required=false,
 *         description="Filter orders by fulfillment status",
 *         @OA\Schema(
 *             type="string",
 *             example="fulfilled"
 *         )
 *     ),
 *
 *     @OA\Parameter(
 *         name="date",
 *         in="query",
 *         required=false,
 *         description="Filter orders by created date (YYYY-MM-DD)",
 *         @OA\Schema(
 *             type="string",
 *             format="date",
 *             example="2024-01-15"
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=200,
 *         description="Orders fetched successfully",
 *         @OA\JsonContent(ref="#/components/schemas/OrdersResponse")
 *     ),
 *
 *     @OA\Response(
 *         response=404,
 *         description="No orders found"
 *     ),
 *
 *     @OA\Response(
 *         response=401,
 *         description="Unauthorized"
 *     )
 * )
 */

    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'nullable|in:fulfilled,unfulfilled',
            'date'   => 'nullable|date_format:Y-m-d',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors()
            ], 422);
        }
        $status = $request->query('status'); 
        $date   = $request->query('date');   
    
        $orders = Order::with('items')
            ->when($status, function ($query) use ($status) {
                $query->where('fulfillment_status', $status);
            })
            ->when($date, function ($query) use ($date) {
                $query->whereDate('created_at', $date);
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
     *     path="/api/ordersdetail/storeid/{userId}",
     *     summary="Get orders by user ID",
     *     description="Returns all orders with items for a specific user",
     *     tags={"Orders"},
     * @OA\Parameter(
 *         name="status",
 *         in="query",
 *         required=false,
 *         description="Filter orders by fulfillment status",
 *         @OA\Schema(
 *             type="string",
 *             example="fulfilled"
 *         )
 *     ),
 *
 *     @OA\Parameter(
 *         name="date",
 *         in="query",
 *         required=false,
 *         description="Filter orders by created date (YYYY-MM-DD)",
 *         @OA\Schema(
 *             type="string",
 *             format="date",
 *             example="2024-01-15"
 *         )
 *     ),
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
        $validator = Validator::make($request->all(), [
            'status' => 'nullable|in:fulfilled,unfulfilled',
            'date'   => 'nullable|date_format:Y-m-d',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors()
            ], 422);
        }
        $status = $request->query('status'); 
        $date   = $request->query('date');   
        $orders = Order::with('items')
            ->where('user_id', $userId)
            ->when($status, function ($query) use ($status) {
                $query->where('fulfillment_status', $status);
            })
            ->when($date, function ($query) use ($date) {
                $query->whereDate('created_at', $date);
            })
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
     * @OA\Get(
     *     path="/api/ordersdetail/orderprefix/{orderId}",
     *     summary="Get orders by Order ID prefix",
     *     description="Returns all orders matching the order ID prefix",
     *     tags={"Orders"},
     * @OA\Parameter(
 *         name="status",
 *         in="query",
 *         required=false,
 *         description="Filter orders by fulfillment status",
 *         @OA\Schema(
 *             type="string",
 *             example="fulfilled"
 *         )
 *     ),
 *     @OA\Parameter(
 *         name="date",
 *         in="query",
 *         required=false,
 *         description="Filter orders by created date (YYYY-MM-DD)",
 *         @OA\Schema(
 *             type="string",
 *             format="date",
 *             example="2024-01-15"
 *         )
 *     ),
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
        $validator = Validator::make($request->all(), [
            'status' => 'nullable|in:fulfilled,unfulfilled',
            'date'   => 'nullable|date_format:Y-m-d',
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
            ->when($status, function ($query) use ($status) {
                $query->where('fulfillment_status', $status);
            })
            ->when($date, function ($query) use ($date) {
                $query->whereDate('created_at', $date);
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
     * @OA\Get(
     *     path="/api/ordersdetail/orderid/{id}",
     *     summary="Get order by ID",
     *     description="Returns a single order with items for a specific ID",
     *     tags={"Orders"},
     *  * @OA\Parameter(
 *         name="status",
 *         in="query",
 *         required=false,
 *         description="Filter orders by fulfillment status",
 *         @OA\Schema(
 *             type="string",
 *             example="fulfilled"
 *         )
 *     ),
 *     @OA\Parameter(
 *         name="date",
 *         in="query",
 *         required=false,
 *         description="Filter orders by created date (YYYY-MM-DD)",
 *         @OA\Schema(
 *             type="string",
 *             format="date",
 *             example="2024-01-15"
 *         )
 *     ),
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
        $validator = Validator::make($request->all(), [
            'status' => 'nullable|in:fulfilled,unfulfilled',
            'date'   => 'nullable|date_format:Y-m-d',
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
            ->when($status, function ($query) use ($status) {
                $query->where('fulfillment_status', $status);
            })
            ->when($date, function ($query) use ($date) {
                $query->whereDate('created_at', $date);
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
 *     description="Fetch all orders filtered by fulfillment status (fulfilled, unfulfilled, pending, cancelled)",
 *     operationId="getOrdersByStatus",
 *     tags={"Orders"},
 *
 *     @OA\Parameter(
 *         name="status",
 *         in="query",
 *         required=true,
 *         description="Fulfillment status of the order",
 *         @OA\Schema(
 *             type="string",
 *             enum={"fulfilled","unfulfilled","pending","cancelled","partial"},
 *             example="fulfilled"
 *         )
 *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Order fetched successfully",
     *         @OA\JsonContent(ref="#/components/schemas/OrderStatus")
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
    public function getOrdersByStatus(Request $request)
    {
        $request->validate([
            'status' => 'required|in:fulfilled,unfulfilled'
        ]);
        $status = $request->query('status'); // fulfilled | unfulfilled

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
 *
 *     @OA\Parameter(
 *         name="date",
 *         in="query",
 *         required=true,
 *         description="Order creation date (YYYY-MM-DD)",
 *         @OA\Schema(
 *             type="string",
 *             format="date",
 *             example="2024-01-15"
 *         )
 *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Order fetched successfully",
     *         @OA\JsonContent(ref="#/components/schemas/OrderByDate")
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
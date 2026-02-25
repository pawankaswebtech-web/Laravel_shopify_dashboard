<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File;
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
 *     @OA\Property(property="Comments", type="string", example="Handle with care"),
 *     @OA\Property(property="TotalPaid", type="number", format="float", example=1500.50),
 *     @OA\Property(property="FromWebsite", type="string", example="Shopify"),
 *     @OA\Property(property="BillingType", type="string", example="Prepaid"),
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

        try {
            //  Shopify auth
            $shop = Auth::user();
            if (!$shop) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

            $orders = Order::with('items')
                ->where('user_id', $shop->id)

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

                    'Comments' => $order->Comments,
                    'TotalPaid' => $order->TotalPaid,
                    'FromWebsite' => $order->FromWebsite,
                    'BillingType' => $order->BillingType,
                    'transactionid' => $order->transactionid,
                    'payment_method' => $order->payment_method,
                    'order_status' => $order->order_status,
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

            // External log
            $this->shopifyLog('api-orders', 'Orders fetched via API', [
                'shop_id' => $shop->id,
                'filters' => $request->all(),
                'count'   => $formattedOrders->count(),
            ]);

            return response()->json([
                'success' => true,
                'count'   => $formattedOrders->count(),
                'orders'  => $formattedOrders,
            ]);

        } catch (\Exception $e) {

            $this->shopifyLog('api-orders-error', 'Failed to fetch orders', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong'
            ], 500);
        }
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
        // ✅ Validation
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

        try {
            //  Shopify auth check
            $shop = Auth::user();
            if (!$shop) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

            //  Ensure same store
            if ((int)$storeid !== (int)$shop->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied for this store'
                ], 403);
            }

            $orders = Order::with('items')
                ->where('user_id', $shop->id)

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

                    'Comments' => $order->Comments,
                    'TotalPaid' => $order->TotalPaid,
                    'FromWebsite' => $order->FromWebsite,
                    'BillingType' => $order->BillingType,
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

            //  External log
            $this->shopifyLog('api-orders-store', 'Orders fetched by store ID', [
                'store_id' => $shop->id,
                'filters'  => $request->all(),
                'count'    => $formattedOrders->count(),
            ]);

            return response()->json([
                'success' => true,
                'count'   => $formattedOrders->count(),
                'orders'  => $formattedOrders,
            ]);

        } catch (\Exception $e) {

            $this->shopifyLog('api-orders-store-error', 'Failed to fetch store orders', [
                'store_id' => $storeid,
                'error'    => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong'
            ], 500);
        }
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

            try {
            
                $shop = Auth::user();
                if (!$shop) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Unauthorized'
                    ], 403);
                }

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

                    ->where('user_id', $request->storeid ?? $shop->id)

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

                $this->shopifyLog('api-orders-prefix', 'Orders fetched by order prefix', [
                    'prefix_used' => $finalPrefix,
                    'count'       => $orders->count(),
                    'shop_id'     => $shop->id,
                ]);

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

                            'Comments' => $order->Comments,
                            'TotalPaid' => $order->TotalPaid,
                            'FromWebsite' => $order->FromWebsite,
                            'BillingType' => $order->BillingType,
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

            } catch (\Exception $e) {

                $this->shopifyLog('api-orders-prefix-error', 'Failed to fetch orders by prefix', [
                    'prefix' => $orderId,
                    'error'  => $e->getMessage(),
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Something went wrong'
                ], 500);
            }
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

        try {
            $shop = Auth::user();
            if (!$shop) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

            $order = Order::with('items')
                ->where('id', $Id)

                ->where('user_id', $request->storeid ?? $shop->id)

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

                'Comments' => $order->Comments,
                'TotalPaid' => $order->TotalPaid,
                'FromWebsite' => $order->FromWebsite,
                'BillingType' => $order->BillingType,
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

            $this->shopifyLog('api-order-id', 'Single order fetched', [
                'order_id' => $order->id,
                'shop_id'  => $order->user_id,
            ]);

            return response()->json([
                'success' => true,
                'order'   => $formattedOrder,
            ]);

        } catch (\Exception $e) {

            $this->shopifyLog('api-order-id-error', 'Failed to fetch order', [
                'order_id' => $Id,
                'error'    => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong'
            ], 500);
        }
    }
 /**
 * @OA\GET(
 *     path="/api/ordersdetail/orderstatus",
 *     summary="Get orders by order status",
 *     description="Fetch all orders filtered by order status",
 *     operationId="getOrdersByStatus",
 *     tags={"Orders"},
 *     
 *     @OA\Parameter(
 *         name="status",
 *         in="query",
 *         required=true,
 *         description="Order status filter",
 *         @OA\Schema(
 *             type="string",
 *             enum={"pending","paid"},
 *             example="paid"
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

   public function getOrdersByStatus(Request $request)
    {
    
        $request->validate([
            'status' => 'required|in:paid,pending'
        ]);

        try {
            $shop = Auth::user();
            if (!$shop) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

            $status = $request->query('status');

            $orders = Order::with('items')
                ->where('user_id', $shop->id)
                ->where('order_status', $status)
                ->get();

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

                    'Comments' => $order->Comments,
                    'TotalPaid' => $order->TotalPaid,
                    'FromWebsite' => $order->FromWebsite,
                    'BillingType' => $order->BillingType,
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

            $this->shopifyLog('api-order-status', 'Orders fetched by status', [
                'shop_id' => $shop->id,
                'status'  => $status,
                'count'   => $formattedOrderbystatus->count(),
            ]);

            return response()->json([
                'success' => true,
                'count'   => $formattedOrderbystatus->count(),
                'orders'  => $formattedOrderbystatus,
            ]);

        } catch (\Exception $e) {

            $this->shopifyLog('api-order-status-error', 'Failed to fetch orders by status', [
                'status' => $request->query('status'),
                'error'  => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong'
            ], 500);
        }
    }

/**
 * @OA\POST(
 *     path="/api/ordersdetail/orders/{id}/status",
 *     summary="Update order status",
 *     operationId="updateOrderStatus",
 *     tags={"Orders"},
 *
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="Order ID",
 *         @OA\Schema(type="integer", example=10)
 *     ),
 *
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"status"},
 *             @OA\Property(
 *                 property="status",
 *                 type="string",
 *                 enum={"pending","paid"},
 *                 example="paid"
 *             )
 *         )
 *     ),
 *
 *     @OA\Response(response=200, description="Order status updated successfully"),
 *     @OA\Response(response=404, description="Order not found"),
 *     @OA\Response(response=422, description="Validation error")
 * )
 */
    public function updateOrderStatus(Request $request, $id)
    {
      
        $request->validate([
            'status' => 'required|in:paid,pending'
        ]);

        try {
           
            $shop = Auth::user();
            if (!$shop) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

            $order = Order::where('id', $id)
                        ->where('user_id', $shop->id)
                        ->first();

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found'
                ], 404);
            }

            $order->order_status = $request->status;
            $order->save();

          
            $this->shopifyLog('api-update-status', 'Order status updated via API', [
                'order_id' => $order->id,
                'shop_id'  => $shop->id,
                'status'   => $request->status,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Order status updated successfully',
                'order' => [
                    'id' => $order->id,
                    'order_status' => $order->order_status
                ]
            ]);

        } catch (\Exception $e) {

            $this->shopifyLog('api-update-error', 'Failed to update order status', [
                'order_id' => $id,
                'error'    => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong while updating order'
            ], 500);
        }
    }

    private function shopifyLog($type, $message, $data = [])
    {
        $path = storage_path('logs/shopifylogs');

        if (!File::exists($path)) {
            File::makeDirectory($path, 0755, true);
        }

        $fileName = $type . '-' . date('Y-m-d') . '.log';

        $log = [
            'time'    => now()->toDateTimeString(),
            'type'    => $type,
            'message' => $message,
            'data'    => $data,
        ];

        File::append(
            $path . '/' . $fileName,
            json_encode($log, JSON_PRETTY_PRINT) . PHP_EOL . PHP_EOL
        );
    }
   
}
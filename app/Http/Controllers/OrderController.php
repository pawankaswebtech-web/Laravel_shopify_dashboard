<?php
namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\User;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
class OrderController extends Controller
{
    // Display orders list
    public function index(Request $request)
    {
        $shop = Auth::user();

        $query = Order::with('items')->where('user_id', $shop->id);

        // Filter by status if provided
        if ($request->has('status') && $request->status != 'all') {
            $query->where('order_status', $request->status);
        }

        $orders = $query->orderBy('created_at', 'desc')->paginate(50);

        return view('orders.index', compact('orders'));
    }
    public function orderDetail(Request $request)
    {
        $data = User::orderBy('created_at', 'desc')->get();
        return view('orders.order-detail', compact( 'data'));
    }
    public function orderDetailView(Request $request, $userId)
    {
        $orderview = Order::where('user_id', $userId)
        ->orderBy('created_at', 'desc')
        ->get();
        return view('orders.order-detail-view', compact( 'orderview'));
    }
    // Fetch orders API (for external use)
    public function fetchOrders(Request $request)
    {
        $shop = Auth::user();

        $query = Order::with('items')->where('user_id', $shop->id);

        if ($request->has('status')) {
            $query->where('order_status', $request->status);
        }

        $orders = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'orders' => $orders
        ]);
    }

    // Update order status
    public function updateStatus(Request $request, $id)
    {
        $shop = Auth::user();

        $order = Order::where('user_id', $shop->id)
            ->where('id', $id)
            ->firstOrFail();

        $order->update([
            'order_status' => $request->order_status ?? $order->order_status,
            'payment_status' => $request->payment_status ?? $order->payment_status,
            'fulfillment_status' => $request->fulfillment_status ?? $order->fulfillment_status
        ]);

        // Optional: Sync back to Shopify using GraphQL
        $this->syncToShopify($shop, $order, $request);

        return response()->json([
            'success' => true,
            'order' => $order
        ]);
    }

    private function syncToShopify($shop, $order, $request)
    {
        // 1. Handle Payment Status Update (Mark as Paid)
        if ($request->has('payment_status') && $request->payment_status === 'paid' && $order->payment_status !== 'paid') {
            // Note: This is a simplification. In reality, you'd likely capture a transaction.
            // Using GraphQL orderMarkAsPaid (if available/applicable) or creating a transaction.
            // For this demo, let's try creating a transaction to mark it as paid.
            $mutation = <<<'GRAPHQL'
            mutation orderMarkAsPaid($input: OrderMarkAsPaidInput!) {
                orderMarkAsPaid(input: $input) {
                    order {
                        id
                        displayFinancialStatus
                    }
                    userErrors {
                        field
                        message
                    }
                }
            }
            GRAPHQL;

            $shop->api()->graph($mutation, [
                'input' => [
                    'id' => "gid://shopify/Order/{$order->shopify_order_id}"
                ]
            ]);
        }

        // 2. Handle Fulfillment (Mark as Fulfilled)
        if ($request->has('fulfillment_status') && $request->fulfillment_status === 'fulfilled' && $order->fulfillment_status !== 'fulfilled') {
            $mutation = <<<'GRAPHQL'
            mutation fulfillmentCreateV2($fulfillment: FulfillmentV2Input!) {
                fulfillmentCreateV2(fulfillment: $fulfillment) {
                    fulfillment {
                        id
                        status
                    }
                    userErrors {
                        field
                        message
                    }
                }
            }
            GRAPHQL;

            // We need the fulfillment order ID first. For simplicity, we'll fetch it.
            // But to avoid too much complexity in this snippet, we'll assume we can just try to fulfill everything.
            // fetching fulfillment orders is required for V2.

            // Step A: Get Fulfillment Order ID
            $query = <<<'GRAPHQL'
            query getFulfillmentOrders($id: ID!) {
                order(id: $id) {
                    fulfillmentOrders(first: 1) {
                        edges {
                            node {
                                id
                            }
                        }
                    }
                }
            }
            GRAPHQL;

            $response = $shop->api()->graph($query, ['id' => "gid://shopify/Order/{$order->shopify_order_id}"]);
            $fulfillmentOrderId = $response['body']['data']['order']['fulfillmentOrders']['edges'][0]['node']['id'] ?? null;

            if ($fulfillmentOrderId) {
                $shop->api()->graph($mutation, [
                    'fulfillment' => [
                        'lineItemsByFulfillmentOrder' => [
                            [
                                'fulfillmentOrderId' => $fulfillmentOrderId
                            ]
                        ],
                        'notifyCustomer' => true
                    ]
                ]);
            }
        }
    }

    // Download Order JSON
    public function downloadJson($id)
    {
        $shop = Auth::user();
        $order = Order::where('user_id', $shop->id)->where('id', $id)->firstOrFail();

        // Construct filename: shopDomain-orderName (or orderId if name is null)
        $filename = $shop->name . '-' . ($order->orderid ?: $order->shopify_order_id) . '.json';
        $path = storage_path('app/internal_logs/requests/' . $filename);

        if (!file_exists($path)) {
            // Fallback try with just ID if name-based fails (for older logs)
            $altPath = storage_path('app/internal_logs/requests/' . $order->shopify_order_id . '.json');
            if (file_exists($altPath)) {
                return response()->download($altPath);
            }
            return back()->with('error', 'Log file not found.');
        }

        return response()->download($path);
    }
}
<?php
namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\User;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use App\Services\Shopify\OrderService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class OrderController extends Controller
{
      
    public function HomeRoute(Request $request)
    {
        $shop = Auth::user();
        $orders = Order::where('user_id', $shop->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        return view('welcome', compact('orders'));
    }
    
    // Display orders list
    public function index(Request $request)
    {
        $shop = Auth::user();

        $orders = Order::with('items')
            ->where('user_id', $shop->id)
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        return view('orders.index', compact('orders'));
    }
    public function orderDetail(Request $request)
    {
        $data = User::orderBy('created_at', 'desc')->get();
        return view('orders.order-detail', compact('data'));
    }
    public function orderDetailView(Request $request, $userId)
    {
        $orderview = Order::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();
        return view('orders.order-detail-view', compact('orderview'));
    }
    public function showOrderStatusForm(Request $request, $shopOrderId)
    {
        $order = Order::where('id', $shopOrderId)
            ->orderBy('created_at', 'desc')
            ->firstOrFail();

        return view('orders.order-status-form', compact('order'));
    }

    public function OrderStatus(Request $request, $shopOrderId, OrderService $orderService)
    {
       // try {
            $orderstatus = Order::where('id', $shopOrderId)
                ->orderBy('created_at', 'desc')
                ->firstOrFail();

            // Update only fulfillment status in local database
            $orderstatus->update([
                'fulfillment_status' => $request->fulfillment_status ?? $orderstatus->fulfillment_status,
            ]);

            // Sync to Shopify
            $orderService->syncStatus($orderstatus, $request);

            return redirect()->route('orders.detailview', ['userId' => $orderstatus->user_id])
                ->with('success', 'Fulfillment status updated successfully');
        // } catch (\Exception $e) {
        //     Log::error('Failed to update fulfillment status: ' . $e->getMessage());
        //     return back()->with('error', 'Failed to update fulfillment status: ' . $e->getMessage());
        // }
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

    // Update fulfillment status
    public function updateStatus(Request $request, $id)
    {
        $shop = Auth::user();

        $order = Order::where('user_id', $shop->id)
            ->where('id', $id)
            ->firstOrFail();

        $order->update([
            'fulfillment_status' => $request->fulfillment_status ?? $order->fulfillment_status
        ]);

        // Sync fulfillment status to Shopify using OrderService
        $orderService = new OrderService();
        $orderService->syncStatus($order, $request);

        return response()->json([
            'success' => true,
            'order' => $order
        ]);
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

    // Webhook implementation for internal system updates
    public function webhookUpdateStatus(Request $request)
    {
        $request->validate([
            'id' => 'required', // This is our internal DB ID
            // Add other validations as needed
        ]);

        // 1. Find the order by ID (without user_id constraint initially)
        $order = Order::findOrFail($request->id);

        // 2. Load the associated Shop (User)
        $shop = $order->user;

        if (!$shop) {
            return response()->json(['success' => false, 'message' => 'Shop not found for this order'], 404);
        }

        // 3. Update local order status
        $order->update([
            'order_status' => $request->order_status ?? $order->order_status,
            'payment_status' => $request->payment_status ?? $order->payment_status,
            'fulfillment_status' => $request->fulfillment_status ?? $order->fulfillment_status,
            // You might want to save tracking info if you have columns for it in DB, 
            // otherwise just pass it to Shopify sync
        ]);

        // 4. Sync to Shopify (Status & Tracking)
        try {
            // Use OrderService to sync status to Shopify
            $orderService = new OrderService();
            $orderService->syncStatus($order, $request);

        } catch (\Exception $e) {
            Log::error('Shopify Sync Failed: ' . $e->getMessage());
            // We return success for the local update, but warn about sync
            return response()->json([
                'success' => true,
                'message' => 'Order updated locally, but Shopify sync failed',
                'error' => $e->getMessage(),
                'order' => $order
            ]);
        }

        return response()->json([
            'success' => true,
            'order' => $order
        ]);
    }
    public function resendOrderData($id)
{
    $shop = Auth::user();

    // Fetch your order with items and shipping/billing info
    $shopifyOrder = Order::where('user_id', $shop->id)
                         ->where('id', $id)
                         ->firstOrFail();

    // Prepare billing and shipping arrays
    $billing = [
        'name' => $shopifyOrder->bill_name ?? '',
        'address1' => $shopifyOrder->bill_street ?? '',
        'address2' => $shopifyOrder->bill_street2 ?? '',
        'city' => $shopifyOrder->bill_city ?? '',
        'country_code' => $shopifyOrder->bill_country ?? '',
        'province_code' => $shopifyOrder->bill_state ?? '',
        'zip' => $shopifyOrder->bill_zipCode ?? '',
        'phone' => $shopifyOrder->bill_phone ?? '0000000000',
    ];

    $shipping = [
        'name' => $shopifyOrder->ship_name ?? '',
        'address1' => $shopifyOrder->ship_street ?? '',
        'address2' => $shopifyOrder->ship_street2 ?? '',
        'city' => $shopifyOrder->ship_city ?? '',
        'country_code' => $shopifyOrder->ship_country ?? '',
        'province_code' => $shopifyOrder->ship_state ?? '',
        'zip' => $shopifyOrder->ship_zipCode ?? '',
        'phone' => $shopifyOrder->ship_phone ?? '0000000000',
    ];

    $shippingCode = $shopifyOrder->shippingtypeName ?? 'Standard';
    $couponCode = $shopifyOrder->coupon_code ?? '';

    // Prepare order items to fetch
    $rows = $shopifyOrder->items->map(function($item){
        return [
            'product_id' => $item->product_id,
            'title' => $item->title ?? '',
            'quantity' => $item->quantity,
            'price' => $item->price,
            'sku' => $item->sku ?? '',
        ];
    })->toArray();

    // Construct payload exactly like your example
    $orderData = [
        'clientemail' => $shopifyOrder->email ?? '',
        'clientname' => trim(($shopifyOrder->first_name ?? '') . ' ' . ($shopifyOrder->last_name ?? '')),
        'orderid' => ltrim($shopifyOrder->name ?? '', '#'),
        'shippingtypeName' => $shippingCode,
        'phone' => $shopifyOrder->phone ?? '0000000000',
        'currency' => $shopifyOrder->currency ?? '',
        'bill_name' => $billing['name'],
        'bill_street' => $billing['address1'],
        'bill_street2' => $billing['address2'],
        'bill_city' => $billing['city'],
        'bill_country' => $billing['country_code'],
        'bill_state' => $billing['province_code'],
        'bill_zipCode' => $billing['zip'],
        'bill_phone' => $billing['phone'],
        'ship_name' => $shipping['name'],
        'ship_street' => $shipping['address1'],
        'ship_street2' => $shipping['address2'],
        'ship_city' => $shipping['city'],
        'ship_country' => $shipping['country_code'],
        'ship_state' => $shipping['province_code'],
        'ship_zipCode' => $shipping['zip'],
        'ship_phone' => $shipping['phone'],
        'Comments' => $shopifyOrder->note ?? '',
        'TotalPaid' => $shopifyOrder->total_price ?? 0,
        'payment_method' => $shopifyOrder->payment_gateway_names ?? '',
        'discount' => $shopifyOrder->total_discounts ?? 0,
        'date' => Carbon::parse($shopifyOrder->created_at ?? now())->toDateString(),
        'FromWebsite' => $this->shopDomain ?? '',
        'BillingType' => $shopifyOrder->payment_gateway_names ?? '',
        'rows' => $rows,
        'transactionid' => (string) ($shopifyOrder->id ?? ''),
        'coupon_code' => $couponCode,
    ];

    // Send POST request
    try {
        $response = Http::withToken('coQFSMG*M3Ra2NKIcqUE32L2d')
                        ->post('http://52.210.3.93/qms-funnel/orders', $orderData);

        if($response->successful()){
            return redirect()->back()->with('success', 'Order data re-sent successfully!');
        } else {
            return redirect()->back()->with('error', 'Failed to resend order: ' . $response->body());
        }
    } catch (\Exception $e) {
        return redirect()->back()->with('error', 'Error sending order: ' . $e->getMessage());
    }
}

}
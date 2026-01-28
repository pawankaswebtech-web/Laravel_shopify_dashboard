<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class OrdersCreateJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $shopDomain;
    public $data;

    /**
     * Create a new job instance.
     */
    public function __construct($shopDomain, $data)
    {
        $this->shopDomain = $shopDomain;
        $this->data = $data;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        try {
            // Convert data to array (deep)
            $this->data = json_decode(json_encode($this->data), true);

            // Find the shop
            $shop = \App\Models\User::where('name', $this->shopDomain)->first();

            if (!$shop) {
                Log::error('Shop not found: ' . $this->shopDomain);
                return;
            }
            Log::info($this->data);


          
            // Transform Shopify order data for Internal System (and DB)
            $internalData = $this->transformForInternalSystem($this->data);

            // Log to JSON file
            $filename = $this->shopDomain . '-' . ($this->data['name'] ?? $this->data['id']);
            $this->logToJsonFile($filename, $internalData);
            $this->sendToInternalSystem($filename, $internalData);
            $dbData = $this->mapToDb($internalData, $shop->id);

            // Create or update order
            $order = Order::updateOrCreate(
                ['shopify_order_id' => $this->data['id']],
                $dbData
            );

            // Create order items
            if (isset($internalData['rows'])) {
                // Delete existing items
                $order->items()->delete();

                foreach ($internalData['rows'] as $item) {
                    OrderItem::create([
                        'order_id' => $order->id,
                        'ItemCode' => $item['ItemCode'],
                        'Quantity' => $item['Quantity'],
                        'Price'    => $item['Price'],
                        'discount' => $item['Discount'] ?? 0,
                    ]);
                }
                
            }

            Log::info('Order processed successfully', [
                'order_id' => $order->id,
                'shopify_order_id' => $this->data['id']
            ]);

        } catch (\Exception $e) {
            Log::error('Order processing failed: ' . $e->getMessage(), [
                'shop' => $this->shopDomain,
                'error' => $e->getTraceAsString()
            ]);
        }
    }

    private function logToJsonFile($filename, $data)
    {
        $path = storage_path('app/internal_logs/requests/' . $filename . '.json');
        file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT));
    }

    private function sendToInternalSystem($filename, $data)
    {
        // Mock sending to internal system
        // $url = env('INTERNAL_SYSTEM_URL');
        // if ($url) {
        //     Http::post($url, $data);
        // }
        Log::info($filename, ['data' => $data]);
       
    }

    private function transformForInternalSystem($shopifyOrder)
    {
        $billing = $shopifyOrder['billing_address'] ?? [];
        $shipping = $shopifyOrder['shipping_address'] ?? [];
        $customer = $shopifyOrder['customer'] ?? [];

        $rows = [];
        if (isset($shopifyOrder['line_items'])) {
            foreach ($shopifyOrder['line_items'] as $item) {

                $itemDiscount = 0;
            
                if (!empty($item['discount_allocations'])) {
                    foreach ($item['discount_allocations'] as $allocation) {
                        $itemDiscount += (float) $allocation['amount'];
                    }
                }
            
                $rows[] = [
                    'ItemCode' => $item['sku'] ?? $item['product_id'],
                    'Quantity' => $item['quantity'],
                    'Price'    => $item['price'],
                    'Discount' => $itemDiscount,
                ];
            }
        }

        $shippingCode = 'GROUND_HOME_DELIVERY'; // Default
        if (isset($shopifyOrder['shipping_lines']) && count($shopifyOrder['shipping_lines']) > 0) {
            $shippingCode = $shopifyOrder['shipping_lines'][0]['code'] ?? $shippingCode;
        }

        $couponCode = null;
        if (isset($shopifyOrder['discount_codes']) && count($shopifyOrder['discount_codes']) > 0) {
            $couponCode = implode(',', array_column($shopifyOrder['discount_codes'], 'code'));
        }

        return [
            'clientemail' => $shopifyOrder['email'] ?? '',
            'clientname' => trim(($customer['first_name'] ?? '') . ' ' . ($customer['last_name'] ?? '')),
           'orderid' => ltrim($shopifyOrder['name'] ?? '', '#'),
            'shippingtypeName' => $shippingCode,
            'phone' => $shopifyOrder['phone'] ?? $customer['phone'] ?? '0000000000',
            'currency' => $shopifyOrder['currency'] ?? '',
            'bill_name' => $billing['name'] ?? '',
            'bill_street' => $billing['address1'] ?? '',
            'bill_street2' => $billing['address2'] ?? '',
            'bill_city' => $billing['city'] ?? '',
            'bill_country' => $billing['country_code'] ?? '',
            'bill_state' => $billing['province_code'] ?? '',
            'bill_zipCode' => $billing['zip'] ?? '',
            'bill_phone' => $billing['phone'] ?? '0000000000',
            'ship_name' => $shipping['name'] ?? '',
            'ship_street' => $shipping['address1'] ?? '',
            'ship_street2' => $shipping['address2'] ?? '',
            'ship_city' => $shipping['city'] ?? '',
            'ship_country' => $shipping['country_code'] ?? '',
            'ship_state' => $shipping['province_code'] ?? '',
            'ship_zipCode' => $shipping['zip'] ?? '',
            'ship_phone' => $shipping['phone'] ?? '0000000000',
            'comments' => $shopifyOrder['note'] ?? '',
            'totalpaid' => $shopifyOrder['total_price'] ?? 0,
            'payment_method' => $shopifyOrder['payment_gateway_names'][0] ?? 0,
            'discount' => $shopifyOrder['total_discounts'] ?? 0,
            'discount' => $shopifyOrder['total_discounts'] ?? 0,
           'date' => \Carbon\Carbon::parse($shopifyOrder['created_at'])->toDateString(),


            'fromwebsite' =>  $this->shopDomain , // As per req example
            'billingtype' =>  $shopifyOrder['payment_gateway_names'][0] ?? 0, // As per req example or map from gateway
            'rows' => $rows,
            'transactionid' => (string) $shopifyOrder['id'],
            'coupon_code' => $couponCode
        ];
    }

    private function mapToDb($data, $userId)
    {
        // Maps the internal system format back to the flat DB structure we created earlier
        return [
            'user_id' => $userId,
            'shopify_order_id' => $data['transactionid'], // Using ID as transactionID based on observing 'id' in req
            'clientemail' => $data['clientemail'],
            'clientname' => $data['clientname'],
            'orderid' => $data['orderid'] ?? '',


            'shippingtypeName' => $data['shippingtypeName'],
            'phone' => $data['phone'],
            'currency' => $data['currency'],
            'bill_name' => $data['bill_name'],
            'bill_street' => $data['bill_street'],
            'bill_street2' => $data['bill_street2'],
            'bill_city' => $data['bill_city'],
            'bill_country' => $data['bill_country'],
            'bill_state' => $data['bill_state'],
            'bill_zipCode' => $data['bill_zipCode'],
            'bill_phone' => $data['bill_phone'],
            'ship_name' => $data['ship_name'],
            'ship_street' => $data['ship_street'],
            'ship_street2' => $data['ship_street2'],
            'ship_city' => $data['ship_city'],
            'ship_country' => $data['ship_country'],
            'ship_state' => $data['ship_state'],
            'ship_zipCode' => $data['ship_zipCode'],
            'ship_phone' => $data['ship_phone'],
            'comments' => $data['comments'],
            'totalpaid' => $data['totalpaid'],
            'fromwebsite' => $data['fromwebsite'],
            'billingtype' => $data['billingtype'],
            'transactionid' => $data['transactionid'],
            'discount' => $data['discount'] ?? 0,
            'date' => \Carbon\Carbon::parse($data['created_at'])->toDateString(),

            'payment_method' => $data['payment_method'] ?? null,
            // Default statuses as they are not in the JSON structure for PUSH
            'order_status' => 'pending',
            'payment_status' => 'pending',
            'fulfillment_status' => 'unfulfilled',
            'coupon_code' => $data['coupon_code'] ?? null,

        ];
    }
}



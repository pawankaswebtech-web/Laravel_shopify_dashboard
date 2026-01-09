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

            // Transform Shopify order data for Internal System (and DB)
            $internalData = $this->transformForInternalSystem($this->data);

            // Log to JSON file
            $filename = $this->shopDomain . '-' . ($this->data['name'] ?? $this->data['id']);
            $this->logToJsonFile($filename, $internalData);

            // Send to Internal API
            $this->sendToInternalSystem($internalData);

            // Transform for Local DB (Flattened structure if needed, or mapping)
            // Note: The original code used a flat structure for the Order model.
            // We need to map the 'internalData' back to the flat DB structure 
            // OR reuse the transformation logic. 
            // For simplicity and correctness, I will use a separate mapping for the DB 
            // since the DB schema is flat but the JSON requirement has 'rows'.

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
                        'Price' => $item['Price']
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

    private function sendToInternalSystem($data)
    {
        // Mock sending to internal system
        // $url = env('INTERNAL_SYSTEM_URL');
        // if ($url) {
        //     Http::post($url, $data);
        // }
        Log::info('Sent to internal system (Mock)', ['data' => $data]);
    }

    private function transformForInternalSystem($shopifyOrder)
    {
        $billing = $shopifyOrder['billing_address'] ?? [];
        $shipping = $shopifyOrder['shipping_address'] ?? [];
        $customer = $shopifyOrder['customer'] ?? [];

        $rows = [];
        if (isset($shopifyOrder['line_items'])) {
            foreach ($shopifyOrder['line_items'] as $item) {
                $rows[] = [
                    'ItemCode' => $item['sku'] ?? $item['product_id'] ?? 'N/A',
                    'Quantity' => $item['quantity'],
                    'Price' => $item['price']
                ];
            }
        }

        $shippingCode = 'GROUND_HOME_DELIVERY'; // Default
        if (isset($shopifyOrder['shipping_lines']) && count($shopifyOrder['shipping_lines']) > 0) {
            $shippingCode = $shopifyOrder['shipping_lines'][0]['code'] ?? $shippingCode;
        }

        return [
            'clientemail' => $shopifyOrder['email'] ?? '',
            'clientname' => trim(($customer['first_name'] ?? '') . ' ' . ($customer['last_name'] ?? '')),
            'orderid' => $shopifyOrder['name'] ?? '',
            'shippingtypeName' => $shippingCode,
            'phone' => $shopifyOrder['phone'] ?? $customer['phone'] ?? '0000000000',
            'currency' => '$', // Hardcoded as per req example, or use $shopifyOrder['currency']
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
            'fromwebsite' => 'Renelif', // As per req example
            'billingtype' => 'authnetcim', // As per req example or map from gateway
            'rows' => $rows,
            'transactionid' => (string) $shopifyOrder['id']
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
            'orderid' => $data['orderid'],
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
            // Default statuses as they are not in the JSON structure for PUSH
            'order_status' => 'pending',
            'payment_status' => 'pending',
            'fulfillment_status' => 'unfulfilled'
        ];
    }
}



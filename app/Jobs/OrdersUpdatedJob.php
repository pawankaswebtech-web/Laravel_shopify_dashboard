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

class OrdersUpdatedJob implements ShouldQueue
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

            $order = Order::where('shopify_order_id', $this->data['id'])->first();

            if ($order) {
                $order->update([
                    'order_status' => $this->data['financial_status'] ?? 'pending',
                    'payment_status' => $this->data['financial_status'] ?? 'pending',
                    'fulfillment_status' => $this->data['fulfillment_status'] ?? 'unfulfilled'
                ]);

                Log::info('Order updated successfully', ['order_id' => $order->id]);
            }

        } catch (\Exception $e) {
            Log::error('Order update failed: ' . $e->getMessage());
        }
    }
}

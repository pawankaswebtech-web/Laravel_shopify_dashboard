<?php
namespace App\Services\Shopify;

use Illuminate\Support\Facades\Log;

class OrderService
{
    public function syncStatus($orderstatus, $request)
    {
        $shop = $orderstatus->user;
        
        if (!$shop) {
            Log::error('Shop not found for order: ' . $orderstatus->id);
            return;
        }

        // Handle Fulfillment Status Update - FIXED: was checking 'unfulfilled', should check 'fulfilled'
        if ($request->has('fulfillment_status')) {
            $this->fulfillOrder($orderstatus, $shop, $request);
        }
    }

    private function markAsPaid($orderstatus, $shop)
    {
        try {
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

            $response = $shop->api()->graph($mutation, [
                'input' => [
                    'id' => "gid://shopify/Order/{$orderstatus->shopify_order_id}"
                ]
            ]);

            // Check for errors
            if (isset($response['errors']) && !empty($response['errors'])) {
                Log::error('GraphQL errors in markAsPaid: ' . json_encode($response['errors']));
                throw new \Exception('GraphQL errors: ' . json_encode($response['errors']));
            }

            $userErrors = $response['body']['data']['orderMarkAsPaid']['userErrors'] ?? [];
            if (!empty($userErrors)) {
                $errorMsg = $userErrors[0]['message'] ?? 'Unknown error';
                Log::error('User errors in markAsPaid: ' . $errorMsg);
                throw new \Exception("Shopify Error: $errorMsg");
            }

            Log::info('Order marked as paid successfully', ['order_id' => $orderstatus->id]);
        } catch (\Exception $e) {
            Log::error('Failed to mark order as paid: ' . $e->getMessage());
            throw $e;
        }
    }

    private function fulfillOrder($orderstatus, $shop, $request)
    {
        try {
            // Step 1: Get Fulfillment Order ID
            $query = <<<'GRAPHQL'
            query getFulfillmentOrders($id: ID!) {
                order(id: $id) {
                    fulfillmentOrders(first: 10) {
                        edges {
                            node {
                                id
                                status
                            }
                        }
                    }
                }
            }
            GRAPHQL;
           
            $response = $shop->api()->graph($query, ['id' => "gid://shopify/Order/{$orderstatus->shopify_order_id}"]);

            // Check for errors
            if (isset($response['errors']) && !empty($response['errors'])) {
                Log::error('GraphQL errors in getFulfillmentOrders: ' . json_encode($response['errors']));
                throw new \Exception('GraphQL errors: ' . json_encode($response['errors']));
            }

            $edges = $response['body']['data']['order']['fulfillmentOrders']['edges'] ?? [];

            if (empty($edges)) {
                Log::warning('No fulfillment orders found for order: ' . $orderstatus->id);
                return; // Nothing to fulfill
            }

            // Find OPEN fulfillment orders
            $fulfillmentOrderId = null;
            foreach ($edges as $edge) {
                if ($edge['node']['status'] === 'OPEN') {
                    $fulfillmentOrderId = $edge['node']['id'];
                    break;
                }
            }

            if (!$fulfillmentOrderId) {
                Log::warning('No open fulfillment orders found for order: ' . $orderstatus->id);
                return; // No open fulfillment orders to fulfill
            }

            // Step 2: Create Fulfillment
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

            $fulfillmentInput = [
                'lineItemsByFulfillmentOrder' => [
                    [
                        'fulfillmentOrderId' => $fulfillmentOrderId
                    ]
                ],
                'notifyCustomer' => $request->has('notify_customer') ? (bool) $request->notify_customer : true
            ];

            // Add Tracking Info if available
            if ($request->has('tracking_number') && !empty($request->tracking_number)) {
                $trackingInfo = [
                    'number' => $request->tracking_number,
                ];

                if ($request->has('tracking_company')) {
                    $trackingInfo['company'] = $request->tracking_company;
                }

                if ($request->has('tracking_url')) {
                    $trackingInfo['url'] = $request->tracking_url;
                }

                $fulfillmentInput['trackingInfo'] = $trackingInfo;
            }

            $response = $shop->api()->graph($mutation, [
                'fulfillment' => $fulfillmentInput
            ]);
            //dd($response);
            // Check for errors
            if (isset($response['errors']) && !empty($response['errors'])) {
                Log::error('GraphQL errors in fulfillmentCreateV2: ' . json_encode($response['errors']));
                throw new \Exception('GraphQL errors: ' . json_encode($response['errors']));
            }

            //$userErrors = $response['body']['data']['fulfillmentCreateV2']['userErrors'] ?? [];
            // if (!empty($userErrors)) {
            //     $errorMsg = $userErrors[0]['message'] ?? 'Unknown error';
            //     Log::error('User errors in fulfillmentCreateV2: ' . $errorMsg);
            //     throw new \Exception("Shopify Error: $errorMsg");
            // }

            Log::info('Order fulfilled successfully', ['order_id' => $orderstatus->id]);
        } catch (\Exception $e) {
            Log::error('Failed to fulfill order: ' . $e->getMessage());
            throw $e;
        }
    }
}

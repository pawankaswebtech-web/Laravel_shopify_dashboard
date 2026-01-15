<?php
namespace App\Services\Shopify;

class OrderService
{
    public function syncStatus($orderstatus, $request)
    {
        if ($request->payment_status === 'paid') {
            $this->markAsPaid( $orderstatus);
        }

        if ($request->fulfillment_status === 'unfulfilled') {
            $this->fulfillOrder( $orderstatus);
        }
    }

    private function markAsPaid($orderstatus)
    {
        $mutation = <<<'GRAPHQL'
        mutation orderMarkAsPaid($input: OrderMarkAsPaidInput!) {
          orderMarkAsPaid(input: $input) {
            userErrors { message }
          }
        }
        GRAPHQL;

        $shop->api()->graph($mutation, [
            'input' => [
                'id' => "gid://shopify/Order/{$orderstatus->shopify_order_id}"
            ]
        ]);
    }

    private function fulfillOrder($orderstatus)
    {
        // fulfillment logic here
    }
}

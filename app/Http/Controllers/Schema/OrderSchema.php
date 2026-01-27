<?php

namespace App\Http\Controllers\Schemas;

/**
 * @OA\Schema(
 *     schema="OrderItem",
 *     type="object",
 *     @OA\Property(property="item_code", type="string", example="SKU123"),
 *     @OA\Property(property="quantity", type="integer", example=2),
 *     @OA\Property(property="price", type="number", format="float", example=49.99)
 * )
 * 
 * @OA\Schema(
 *     schema="Order",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="store_id", type="integer", example=10),
 *     @OA\Property(property="user_id", type="integer", example=5),
 *     @OA\Property(property="clientname", type="string", example="John Doe"),
 *     @OA\Property(property="clientemail", type="string", example="john@example.com"),
 *     @OA\Property(property="orderid", type="string", example="ORD123"),
 *     @OA\Property(property="shippingtypeName", type="string", example="Express"),
 *     @OA\Property(property="phone", type="string", example="9876543210"),
 *     @OA\Property(property="currency", type="string", example="USD"),
 *     @OA\Property(property="bill_name", type="string", example="John Doe"),
 *     @OA\Property(property="bill_street", type="string", example="Street 1"),
 *     @OA\Property(property="bill_street2", type="string", example="Street 2"),
 *     @OA\Property(property="bill_city", type="string", example="New York"),
 *     @OA\Property(property="bill_country", type="string", example="USA"),
 *     @OA\Property(property="bill_state", type="string", example="NY"),
 *     @OA\Property(property="bill_zipCode", type="string", example="10001"),
 *     @OA\Property(property="bill_phone", type="string", example="9876543210"),
 *     @OA\Property(property="ship_name", type="string", example="John Doe"),
 *     @OA\Property(property="ship_street", type="string", example="Street 1"),
 *     @OA\Property(property="ship_street2", type="string", example="Street 2"),
 *     @OA\Property(property="ship_city", type="string", example="New York"),
 *     @OA\Property(property="ship_country", type="string", example="USA"),
 *     @OA\Property(property="ship_state", type="string", example="NY"),
 *     @OA\Property(property="ship_zipCode", type="string", example="10001"),
 *     @OA\Property(property="ship_phone", type="string", example="9876543210"),
 *     @OA\Property(property="comments", type="string", example="Handle with care"),
 *     @OA\Property(property="totalpaid", type="number", format="float", example=150.75),
 *     @OA\Property(property="fromwebsite", type="string", example="Shopify"),
 *     @OA\Property(property="billingtype", type="string", example="Prepaid"),
 *     @OA\Property(property="transactionid", type="string", example="TXN12345"),
 *     @OA\Property(property="payment_method", type="string", example="credit_card"),
 *     @OA\Property(property="discount", type="number", format="float", example=10),
 *     @OA\Property(property="coupon_code", type="string", example="NEW10"),
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
 *     @OA\Property(property="success", type="boolean", example=true),
 *     @OA\Property(property="count", type="integer", example=2),
 *     @OA\Property(
 *         property="orders",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/Order")
 *     )
 * )
 */
class OrderSchemas
{
    // This class is just for holding schema definitions
}
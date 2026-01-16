@extends('shopify-app::layouts.default')

@section('content')
    <div class="container mx-auto p-6">
        <h1 class="text-2xl font-bold mb-4">Orders</h1>

        <table class="w-full border">
            <thead>
                <tr class="bg-gray-100">
                    <th class="border p-2">Order ID</th>
                    <th class="border p-2">Customer</th>
                    <th class="border p-2">Total</th>
                    <th class="border p-2">Date</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                    <tr>
                        <td class="border p-2">{{ $order->orderid }}</td>
                        <td class="border p-2">{{ $order->clientname }}</td>
                        <td class="border p-2">${{ number_format($order->totalpaid, 2) }}</td>
                        <td class="border p-2">{{ $order->created_at->format('Y-m-d H:i') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="border p-4 text-center">No orders found</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="mt-4">
            {{ $orders->links() }}
        </div>
    </div>
@endsection
@extends('shopify-app::layouts.default')

@section('content')
    <div
        style="font-family: -apple-system, BlinkMacSystemFont, 'San Francisco', 'Segoe UI', Roboto, 'Helvetica Neue', sans-serif; padding: 20px; max-width: 1200px; margin: 0 auto;">

        <div
            style="margin-bottom: 2rem; border-bottom: 1px solid #e1e3e5; padding-bottom: 1rem; display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h1 style="font-size: 24px; font-weight: 600; color: #202223; margin: 0;">
                    {{ $shopDomain ?? Auth::user()->name }}
                    
                </h1>
                <p style="color: #6d7175; margin-top: 4px;"><a href="/log-viewer" >View Logs</a></p>
            </div>
            <ui-title-bar title="Dashboard"></ui-title-bar>
        </div>

        <div
            style="background: #fff; border-radius: 8px; box-shadow: 0 0 0 1px rgba(63, 63, 68, 0.05), 0 1px 3px 0 rgba(63, 63, 68, 0.15);">
            <div style="padding: 16px; border-bottom: 1px solid #e1e3e5;">
                <h2 style="font-size: 16px; font-weight: 600; margin: 0;">Recent Orders</h2>
            </div>

            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 14px;">
                    <thead>
                        <tr style="background-color: #f7f8fa; color: #5c5f62;">
                            <th style="padding: 12px 16px; font-weight: 500;">Order ID</th>
                            <th style="padding: 12px 16px; font-weight: 500;">Customer</th>
                            <th style="padding: 12px 16px; font-weight: 500;">Date</th>
                            <th style="padding: 12px 16px; font-weight: 500;">Fulfillment</th>
                            <th style="padding: 12px 16px; font-weight: 500; text-align: right;">Total</th>
                            <th style="padding: 12px 16px; font-weight: 500; text-align: center;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $order)
                            <tr style="border-top: 1px solid #e1e3e5; transition: background-color 0.2s;">
                                <td style="padding: 12px 16px; font-weight: 600; color: #202223;">
                                    {{ $order->orderid ?: $order->shopify_order_id }}
                                </td>
                                <td style="padding: 12px 16px; color: #202223;">
                                    {{ $order->clientname }}
                                    <div style="color: #6d7175; font-size: 12px;">{{ $order->clientemail }}</div>
                                </td>
                                <td style="padding: 12px 16px; color: #6d7175;">
                                    {{ $order->created_at->format('M d, Y') }}
                                </td>
                                <td style="padding: 12px 16px;">
                                    <span
                                        style="background: {{ $order->fulfillment_status === 'fulfilled' ? '#c3f4d6' : '#e4e5e7' }}; color: {{ $order->fulfillment_status === 'fulfilled' ? '#1f4836' : '#454f5b' }}; padding: 2px 8px; border-radius: 4px; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">
                                        {{ $order->fulfillment_status }}
                                    </span>
                                </td>
                                <td style="padding: 12px 16px; text-align: right; color: #202223;">
                                    ${{ number_format($order->totalpaid, 2) }}
                                </td>
                                <td style="padding: 12px 16px; text-align: center;">
                                    <a href="{{ route('orders.download.json', $order->id) }}" style="display: inline-block; padding: 6px 12px; border: 1px solid #c9cccf; border-radius: 4px; color: #202223; text-decoration: none; font-size: 13px; font-weight: 500; background: #fff; transition: background-color 0.2s;">
                                       Download JSON
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" style="padding: 32px; text-align: center; color: #6d7175;">
                                    No orders found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($orders->hasPages())
                <div style="padding: 12px 16px; border-top: 1px solid #e1e3e5; display: flex; justify-content: center;">
                    {{ $orders->links('pagination::simple-bootstrap-4') }}
                </div>
            @endif
        </div>

        <div style="margin-top: 2rem; text-align: center; color: #6d7175; font-size: 13px;">
            <p>Syncing orders automatically from Shopify</p>
        </div>
    </div>
@endsection
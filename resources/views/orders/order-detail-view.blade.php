<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail view</title>
    <!-- Bootstrap CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
</head>
<body>
    <div class="container mx-auto p-6">
        <h1 class="text-2xl font-bold mb-4">Order Details View</h1>

        <!-- <div class="mb-4">
            <form method="GET" action="{{ route('orders.index') }}">
                <select name="status" onchange="this.form.submit()" class="border p-2 rounded">
                    <option value="all"> Order Details</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                    <option value="fulfilled" {{ request('status') == 'fulfilled' ? 'selected' : '' }}>Fulfilled</option>
                </select>
            </form>
        </div> -->

       <div class="table-responsive">
       <table class="w-100 bordered bg-light">
            <thead>
                <tr class="bg-gray-100">
                    <th class="border p-2"> ID</th>
                    <th class="border p-2" style="min-width: 100px;">name</th>
                    <th class="border p-2" style="min-width: 100px;">email </th>
                    <th class="border p-2" style="min-width: 100px;">phone </th>
                    <th class="border p-2" style="min-width: 180px;">Billing Name </th>
                    <th class="border p-2" style="min-width: 180px;">Billing Street </th>
                    <th class="border p-2" style="min-width: 180px;">Billing Street2 </th>
                    <th class="border p-2" style="min-width: 180px;">Billing City </th>
                    <th class="border p-2" style="min-width: 180px;">Billing Country </th>
                    <th class="border p-2" style="min-width: 180px;">Billing State </th>
                    <th class="border p-2" style="min-width: 180px;">Billing Zipcode </th>
                    <th class="border p-2" style="min-width: 180px;">Shipping Street </th>
                    <th class="border p-2" style="min-width: 180px;">Shipping Street2 </th>
                    <th class="border p-2" style="min-width: 180px;">Shipping City </th>
                    <th class="border p-2" style="min-width: 180px;">Shipping Country </th>
                    <th class="border p-2" style="min-width: 180px;">Shipping State </th>
                    <th class="border p-2" style="min-width: 180px;">Shipping Zipcode </th>
                    <th class="border p-2" style="min-width: 180px;">Shipping phone </th>
                    <th class="border p-2" style="min-width: 100px;">Comments</th>
                    <th class="border p-2" style="min-width: 100px;">TotalPaid </th>
                    <th class="border p-2" style="min-width: 100px;">coupon_code </th>
                    <th class="border p-2" style="min-width: 100px;">BillingType </th>
                    <th class="border p-2" style="min-width: 100px;">transactionid </th>
                    <th class="border p-2" style="min-width: 100px;">From Website </th>
                    <th class="border p-2" style="min-width: 100px;">Currency </th>
                    <th class="border p-2" style="min-width: 100px;">Payment Method </th>
                    <th class="border p-2" style="min-width: 100px;">Discount amount </th>
                    <th class="border p-2" style="min-width: 100px;">fulfillment_status </th>
                    <th class="border p-2" style="min-width: 100px;">Date</th>
                    <th class="border p-2" style="min-width: 100px;">Action</th>
                </tr>
            </thead>
            <tbody>
            @forelse($orderview as $view)
            <?php 
            // echo "<pre>";
            // print_r($orderview);
            // echo "</pre>";
            ?>
                @if($view)
                    <tr>
                        <td class="border p-2">{{ $view->orderid ?? '' }}</td>
                        <td class="border p-2">{{ $view->clientname ?? '' }}</td>
                        <td class="border p-2">{{ $view->clientemail ?? '' }}</td>
                        <td class="border p-2">{{ $view->phone ?? '' }}</td>
                        <td class="border p-2">{{ $view->bill_name ?? '' }}</td>
                        <td class="border p-2">{{ $view->bill_street ?? '' }}</td>
                        <td class="border p-2">{{ $view->bill_street2 ?? '' }}</td>
                        <td class="border p-2">{{ $view->bill_city ?? '' }}</td>
                        <td class="border p-2">{{ $view->bill_country ?? '' }}</td>
                        <td class="border p-2">{{ $view->bill_state ?? '' }}</td>
                        <td class="border p-2">{{ $view->bill_zipCode ?? '' }}</td>
                        <td class="border p-2">{{ $view->ship_street ?? '' }}</td>
                        <td class="border p-2">{{ $view->ship_street2 ?? '' }}</td>
                        <td class="border p-2">{{ $view->ship_city ?? '' }}</td>
                        <td class="border p-2">{{ $view->ship_country ?? '' }}</td>
                        <td class="border p-2">{{ $view->ship_state ?? '' }}</td>
                        <td class="border p-2">{{ $view->ship_zipCode ?? '' }}</td>
                        <td class="border p-2">{{ $view->ship_phone ?? '' }}</td>
                        <td class="border p-2">{{ $view->Comments ?? '' }}</td>
                        <td class="border p-2">{{ $view->TotalPaid ?? '' }}</td>
                        <td class="border p-2">{{ $view->coupon_code ?? '' }}</td>
                        <td class="border p-2">{{ $view->BillingType ?? '' }}</td>
                        <td class="border p-2">{{ $view->transactionid ?? '' }}</td>
                        <td class="border p-2">{{ $view->FromWebsite ?? '' }}</td>
                        <td class="border p-2">{{ $view->currency ?? '' }}</td>
                        <td class="border p-2">{{ $view->payment_method ?? '' }}</td>
                        <td class="border p-2">{{ $view->discount ?? '' }}</td>
                        <td class="border p-2">{{ $view->fulfillment_status ?? '' }}</td>
                        <td class="border p-2">{{ $view->created_at ? $view->created_at->format('Y-m-d H:i') : "" }}</td>
                        <td class="border p-2"><a href="{{ route('orders.status',['shopOrderId' => $view->id]) }}" class="btn btn-info">Manage Status</a></td>
                    </tr>
                @endif
            @empty
                <tr>
                    <td colspan="4" class="border p-4 text-center">No orders found</td>
                </tr>
            @endforelse

            </tbody>
        </table>
       </div>

        <div class="mt-4">
        </div>
    </div>
    <!-- Bootstrap JS Bundle CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>
</html>
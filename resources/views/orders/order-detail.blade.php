<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders</title>
    <!-- Bootstrap CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
</head>
<body>
    <div class="container mx-auto p-6">
        <h1 class="text-2xl font-bold mb-4">Order Details</h1>

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
                    <th class="border p-2">name</th>
                    <th class="border p-2">email </th>
                    <th class="border p-2">Date</th>
                    <th class="border p-2">Action</th>
                </tr>
            </thead>
            <tbody>
            @forelse($data as $details)
                @if($details)
                    <tr>
                        <td class="border p-2">{{ $details->id ?? '' }}</td>
                        <td class="border p-2">{{ $details->name ?? '' }}</td>
                        <td class="border p-2">{{ $details->email ?? '' }}</td>
                        <td class="border p-2">{{ $details->created_at->format('Y-m-d H:i') }}</td>
                        <td class="border p-2"><a href="{{ route('orders.detailview',['userId' => $details->id]) }}" class="btn btn-info">View Details</a></td>
                    </tr>
                @endif
            @empty
                <tr>
                    <td colspan="4" class="border p-4 text-center">No users found</td>
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
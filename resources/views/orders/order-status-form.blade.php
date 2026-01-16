<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Order Status</title>
    <!-- Bootstrap CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="mb-0">Update Fulfillment Status</h3>
                    </div>
                    <div class="card-body">
                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        @if(session('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                {{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        <div class="mb-3">
                            <strong>Order ID:</strong> {{ $order->orderid ?? $order->shopify_order_id }}<br>
                            <strong>Customer:</strong> {{ $order->clientname ?? 'N/A' }}<br>
                            <strong>Email:</strong> {{ $order->clientemail ?? 'N/A' }}
                        </div>

                        <form method="POST" action="{{ route('orders.status.update', ['shopOrderId' => $order->id]) }}">
                            @csrf

                            <div class="mb-3">
                                <label for="fulfillment_status" class="form-label">Fulfillment Status</label>
                                <select class="form-select" id="fulfillment_status" name="fulfillment_status">
                                    <option value="unfulfilled" {{ $order->fulfillment_status === 'unfulfilled' ? 'selected' : '' }}>Unfulfilled</option>
                                    <option value="fulfilled" {{ $order->fulfillment_status === 'fulfilled' ? 'selected' : '' }}>Fulfilled</option>
                                    <option value="partial" {{ $order->fulfillment_status === 'partial' ? 'selected' : '' }}>Partially Fulfilled</option>
                                </select>
                                <small class="form-text text-muted">Selecting "Fulfilled" will create a fulfillment in Shopify</small>
                            </div>

                            <div class="mb-3" id="tracking-section" style="display: none;">
                                <h5>Tracking Information (Optional)</h5>
                                
                                <div class="mb-2">
                                    <label for="tracking_number" class="form-label">Tracking Number</label>
                                    <input type="text" class="form-control" id="tracking_number" name="tracking_number" placeholder="Enter tracking number">
                                </div>

                                <div class="mb-2">
                                    <label for="tracking_company" class="form-label">Carrier/Company</label>
                                    <input type="text" class="form-control" id="tracking_company" name="tracking_company" placeholder="e.g., UPS, FedEx, USPS">
                                </div>

                                <div class="mb-2">
                                    <label for="tracking_url" class="form-label">Tracking URL</label>
                                    <input type="url" class="form-control" id="tracking_url" name="tracking_url" placeholder="https://tracking.example.com/...">
                                </div>

                                <div class="mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="notify_customer" name="notify_customer" value="1" checked>
                                        <label class="form-check-label" for="notify_customer">
                                            Notify customer
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="{{ route('orders.detailview', ['userId' => $order->user_id]) }}" class="btn btn-secondary">Cancel</a>
                                <button type="submit" class="btn btn-primary">Update Fulfillment</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    
    <script>
        // Show tracking section when fulfillment status is set to "fulfilled"
        document.getElementById('fulfillment_status').addEventListener('change', function() {
            const trackingSection = document.getElementById('tracking-section');
            if (this.value === 'fulfilled') {
                trackingSection.style.display = 'block';
            } else {
                trackingSection.style.display = 'none';
            }
        });

        // Trigger on page load if already fulfilled
        if (document.getElementById('fulfillment_status').value === 'fulfilled') {
            document.getElementById('tracking-section').style.display = 'block';
        }
    </script>
</body>
</html>

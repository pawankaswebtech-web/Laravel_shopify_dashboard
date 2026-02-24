<!DOCTYPE html>
<html>
<head>
    <title>Shopify Logs</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>🧾 Shopify Logs</h4>
<div>
        <!-- Back Button -->
        <a href="#" class="btn btn-secondary btn-sm">
            ← Back
        </a>
       
    </div>
        <form method="POST" action="{{ route('logs.deleteAll') }}"
              onsubmit="return confirm('Are you sure you want to delete all Shopify logs?');">
            @csrf
            @method('DELETE')
            <button class="btn btn-danger btn-sm">
                🗑️ Delete All Logs
            </button>
        </form>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="card">
        <div class="card-body p-0">
            <table class="table table-bordered mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>Type</th>
                        <th>Shop</th>
                        <th>Message</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr>
                            <td>
                                <span class="badge bg-{{ $log->type === 'error' ? 'danger' : 'info' }}">
                                    {{ strtoupper($log->type) }}
                                </span>
                            </td>
                            <td>{{ $log->shop_domain ?? '-' }}</td>
                            <td><pre>{{ json_encode($log->payload, JSON_PRETTY_PRINT) }}</pre></td>
                            <td>{{ $log->created_at->format('d M Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted">
                                No Shopify logs found
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        {{ $logs->links() }}
    </div>

</div>

</body>
</html>
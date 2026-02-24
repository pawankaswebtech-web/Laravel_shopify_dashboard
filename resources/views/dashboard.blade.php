<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body {
            background-color: #f4f6f9;
        }
        .dashboard-card {
            transition: all 0.3s ease;
            border-radius: 12px;
        }
        .dashboard-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .icon-box {
            font-size: 32px;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark px-3">
    <a class="navbar-brand fw-bold" href="#">🚀 My Dashboard</a>

    <div class="ms-auto dropdown">
        <a class="nav-link dropdown-toggle text-white" href="#" role="button" data-bs-toggle="dropdown">
            <i class="bi bi-person-circle"></i> Admin
        </a>
        <ul class="dropdown-menu dropdown-menu-end">
            <li>
                <a class="dropdown-item" href="#" >
                    <i class="bi bi-gear"></i> Settings
                </a>
            </li>
            <li><hr class="dropdown-divider"></li>
            <li>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="dropdown-item text-danger">
                        <i class="bi bi-box-arrow-right"></i> Logout
                    </button>
                </form>
            </li>
        </ul>
    </div>
</nav>

<div class="container mt-5">
    <h4 class="mb-4">Welcome Back 👋</h4>

    <div class="row g-4">

        <div class="col-md-3">
            <a href="{{ route('swaggeres') }}" class="text-decoration-none text-dark">
                <div class="card dashboard-card p-4">
                    <div class="icon-box text-primary mb-2">📘</div>
                    <h5>Swagger</h5>
                    <p class="text-muted small">API Swagger UI</p>
                </div>
            </a>
        </div>

        <div class="col-md-3">
            <div class="card dashboard-card p-4">
                <div class="icon-box text-success mb-2">🏬</div>
                <h5>Store</h5>
                <p class="text-muted small">Manage Store</p>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card dashboard-card p-4">
                <div class="icon-box text-warning mb-2">📄</div>
                <h5>API Documentation</h5>
                <p class="text-muted small">View API Docs</p>
            </div>
        </div>

        <div class="col-md-3">
            <a href="{{ route('logs.index') }}" class="text-decoration-none text-dark">
            <div class="card dashboard-card p-4">
                <div class="icon-box text-danger mb-2">🧾</div>
                <h5>Logs</h5>
                <p class="text-muted small">System Logs</p>
            </div>
            </a>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
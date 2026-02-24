
<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
</head>

<body class="bg-light">

<div class="container d-flex justify-content-center align-items-center vh-100">
    <div class="card shadow p-4" style="width:500px;">
        
        <h3 class="text-center mb-4">Register</h3>

        @if ($errors->any())
            <div class="alert alert-danger">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ url('/register') }}">
            @csrf

            <input type="text" name="name" placeholder="Name" class="form-control" required>

            <input type="email" name="email" placeholder="Email" class="form-control mt-2" required>

            <input type="password" name="password" placeholder="Password" class="form-control mt-2" required>

            <input type="password" name="password_confirmation" placeholder="Confirm Password" class="form-control mt-2" required>

            <button class="btn btn-primary mt-3 w-100">
                Register
            </button>
             <div class="text-center mt-2">
                <span>Already have an account?</span>
                <a href="{{ route('login.form') }}">Login here</a>
            </div>
        </form>
    </div>
</div>



<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
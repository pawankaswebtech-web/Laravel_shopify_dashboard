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
        
        <h3 class="text-center mb-4">Login</h3>

        @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
        @endif

        @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
        @endif

        {{-- Login errors sirf dikho jab token na ho --}}
        @if ($errors->any() && !request()->has('token'))
        <div class="alert alert-danger">
            {{ $errors->first() }}
        </div>
        @endif

        <form method="POST" action="{{ route('login.submit') }}">
            @csrf

            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" required>
            </div>

            <div class="mb-3 position-relative">
                <label class="form-label">Password</label>
                <input type="password" 
                    name="password" 
                    id="password"
                    class="form-control pe-5"
                    required>
                <i class="bi bi-eye-slash position-absolute"
                id="togglePassword"
                style="top: 38px; right: 15px; cursor: pointer;"></i>
            </div>

            <button type="submit" class="btn btn-primary w-100">Login</button>

            <div class="text-center mt-3">
                <a href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#forgotPasswordModal">
                    Forgot Password?
                </a>
            </div>
            <div class="text-center mt-2">
                <span>Don't have an account?</span>
                <a href="{{ route('register') }}">Register here</a>
            </div>
        </form>

    </div>
</div>

{{-- ===================== --}}
{{-- FORGOT PASSWORD MODAL --}}
{{-- ===================== --}}
<div class="modal fade" id="forgotPasswordModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Forgot Password</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                @if (session('status'))
                    <div class="alert alert-success">{{ session('status') }}</div>
                @endif

                @if ($errors->has('email') && !request()->has('token'))
                    <div class="alert alert-danger">{{ $errors->first('email') }}</div>
                @endif

                <form method="POST" action="{{ route('password.email') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Email Address</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        Send Reset Link
                    </button>
                </form>

            </div>
        </div>
    </div>
</div>

{{-- ==================== --}}
{{-- RESET PASSWORD MODAL --}}
{{-- ==================== --}}
@if(request()->has('token'))
<div class="modal fade" id="resetPasswordModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Reset Password</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                @if ($errors->has('password'))
                    <div class="alert alert-danger">{{ $errors->first('password') }}</div>
                @endif

                <form method="POST" action="{{ route('password.update') }}">
                    @csrf
                    <input type="hidden" name="token" value="{{ request()->get('token') }}">
                    <input type="hidden" name="email" value="{{ request()->get('email') }}">

                    <div class="mb-3">
                        <label class="form-label">New Password</label>
                        <input type="password" name="password" class="form-control" 
                               placeholder="Min 8 characters" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Confirm Password</label>
                        <input type="password" name="password_confirmation" 
                               class="form-control" required>
                    </div>

                    <button type="submit" class="btn btn-success w-100">
                        Reset Password
                    </button>
                </form>

            </div>
        </div>
    </div>
</div>
@endif

{{-- Hidden flags for JS --}}
<input type="hidden" id="hasEmailError" value="{{ $errors->has('email') && !request()->has('token') ? '1' : '0' }}">
<input type="hidden" id="hasToken" value="{{ request()->has('token') ? '1' : '0' }}">

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // Password toggle
    document.getElementById('togglePassword').addEventListener('click', function () {
        const password = document.getElementById('password');
        const icon = document.getElementById('togglePassword');
        if (password.type === 'password') {
            password.type = 'text';
            icon.classList.replace('bi-eye-slash', 'bi-eye');
        } else {
            password.type = 'password';
            icon.classList.replace('bi-eye', 'bi-eye-slash');
        }
    });

    // Forgot modal auto open on email error
    if (document.getElementById('hasEmailError').value === '1') {
        var forgotModal = new bootstrap.Modal(document.getElementById('forgotPasswordModal'));
        forgotModal.show();
    }

    // Reset modal auto open jab token ho URL mein
    if (document.getElementById('hasToken').value === '1') {
        var resetModal = new bootstrap.Modal(document.getElementById('resetPasswordModal'));
        resetModal.show();
    }
</script>

</body>
</html>
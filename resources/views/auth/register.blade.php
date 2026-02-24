
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
           <div class="mb-3 position-relative">
                <label class="form-label">Password</label>

                <input type="password" 
                    name="password_confirmation" 
                    id="password"
                    class="form-control pe-5"
                    placeholder="Confirm Password"
                    required>

                <i class="bi bi-eye-slash position-absolute"
                id="togglePassword"
                style="top: 38px; right: 15px; cursor: pointer;"></i>
            </div>


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
<script>
document.getElementById("togglePassword").addEventListener("click", function () {
    const password = document.getElementById("password");
    const icon = this;

    if (password.type === "password") {
        password.type = "text";
        icon.classList.remove("bi-eye-slash");
        icon.classList.add("bi-eye");
    } else {
        password.type = "password";
        icon.classList.remove("bi-eye");
        icon.classList.add("bi-eye-slash");
    }
});
</script>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
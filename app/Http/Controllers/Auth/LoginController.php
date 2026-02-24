<?php
namespace App\Http\Controllers\Auth;
use App\Models\LoginUser;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

   public function login(Request $request)
{
    $request->validate([
        'email' => 'required|email',
        'password' => 'required'
    ]);

    $user = LoginUser::where('email', $request->email)->first();

    if (!$user) {
        return back()->withErrors(['email' => 'User not found']);
    }

    if (!Hash::check($request->password, $user->password)) {
        return back()->withErrors(['password' => 'Incorrect password']);
    }

    Auth::login($user);
    $request->session()->regenerate();

    return redirect()->route('dashboard');
}

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login.form');
    }
}
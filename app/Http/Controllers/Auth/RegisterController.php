<?php

namespace App\Http\Controllers\Auth;
use App\Models\LoginUser;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;

class RegisterController extends Controller
{
     // Show register page
    public function showRegister()
    {
        return view('auth.register');
    }

    // Register user functionality
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:login_users,email',
            'password' => 'required|min:6|confirmed'
        ]);

        LoginUser::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password)
        ]);

        return redirect()->route('login')->with('success','Registration successful!');
    }
}

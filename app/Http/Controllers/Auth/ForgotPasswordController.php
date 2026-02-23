<?php 
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class ForgotPasswordController extends Controller
{
    // Show forgot password form
    public function showLinkRequestForm()
    {
        return view('auth.forgot-password');
    }

    // Send reset link to email
    public function sendResetLinkEmail(Request $request)
{
    $request->validate([
        'email' => 'required|email|exists:users,email',
    ]);

    $user = User::where('email', $request->email)->first();

    $token = Str::random(64);

    DB::table('password_reset_tokens')->updateOrInsert(
        ['email' => $user->email],
        [
            'email' => $user->email,
            'token' => $token, // ✅ NO bcrypt here
            'created_at' => now()
        ]
    );

    $resetLink = url('/reset-password/'.$token.'?email='.$user->email);
    dd($resetLink);

    try {
        Mail::send('emails.reset-password', ['link' => $resetLink], function($message) use ($user) {
            $message->to($user->email);
            $message->subject('Reset Your Password');
        });

        return back()->with('success', 'Mail Sent Successfully');

    } catch (\Exception $e) {
        return $e->getMessage();
    }
}
public function resetPassword(Request $request)
{
    $request->validate([
        'email' => 'required|email|exists:users,email',
        'password' => 'required|min:6|confirmed',
        'token' => 'required'
    ]);

    $record = DB::table('password_reset_tokens')
        ->where('email', $request->email)
        ->where('token', $request->token)
        ->first();

    if (!$record) {
        return back()->withErrors(['email' => 'Invalid token']);
    }

    User::where('email', $request->email)
        ->update(['password' => bcrypt($request->password)]);

    DB::table('password_reset_tokens')
        ->where('email', $request->email)
        ->delete();

  return back()->with('success', 'Password reset successfully');
}
   
}
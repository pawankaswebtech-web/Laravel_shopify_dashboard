<?php 
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;

class ForgotPasswordController extends Controller
{
    use SendsPasswordResetEmails;
    
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

        // Generate token
        $token = Str::random(64);

        // Save token in password_reset_tokens table
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $user->email],
            [
                'email' => $user->email,
                'token' => bcrypt($token),
                'created_at' => now()
            ]
        );

        // Create reset link
        $resetLink = url('/reset-password/'.$token.'?email='.$user->email);

        // Send Mail
       try {
        Mail::send('emails.reset-password', ['link' => $resetLink], function($message) use ($user) {
            $message->to($user->email);
            $message->subject('Reset Your Password');
        });

        return back()->with('success', 'Mail Sent Successfully');

    } catch (\Exception $e) {
        return $e->getMessage();
    }

        return back()->with('success', 'Reset link sent to your email.');
    }
   
}
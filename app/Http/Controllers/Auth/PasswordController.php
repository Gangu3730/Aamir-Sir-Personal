<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;

/** Forgot-password + the invitation "set your password" flow (same token mechanism). */
class PasswordController extends Controller
{
    public function request()
    {
        return view('auth.forgot-password');
    }

    public function email(Request $request)
    {
        $request->validate(['email' => ['required', 'email']]);

        // Only approved users may receive a link; always respond identically.
        $user = User::where('email', $request->email)->where('status', User::STATUS_APPROVED)->first();
        if ($user) {
            Password::sendResetLink(['email' => $user->email]);
        }

        return back()->with('status', 'If that email belongs to an approved member, a password link has been sent.');
    }

    public function showReset(Request $request, string $token)
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email'),
            'setup' => $request->boolean('setup'),
        ]);
    }

    public function reset(Request $request)
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', PasswordRule::min(10)->letters()->numbers()],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                abort_unless($user->status === User::STATUS_APPROVED, 403);
                $user->forceFill(['password' => $password, 'remember_token' => Str::random(60), 'email_verified_at' => $user->email_verified_at ?? now()])->save();
                event(new PasswordReset($user));
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('status', 'Your password has been saved. You can now log in.')
            : back()->withInput($request->only('email'))->withErrors(['email' => 'This link is invalid or has expired. Please request a new one.']);
    }
}

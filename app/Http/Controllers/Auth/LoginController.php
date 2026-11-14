<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class LoginController extends Controller
{
    public function show()
    {
        return view('auth.login');
    }

    public function store(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $key = Str::lower($credentials['email']).'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $mins = ceil(RateLimiter::availableIn($key) / 60);

            return back()->withInput($request->only('email'))
                ->withErrors(['email' => "Too many attempts. Try again in {$mins} minute(s)."]);
        }

        $user = User::where('email', $credentials['email'])->first();

        if ($user && $user->password && password_verify($credentials['password'], $user->password)) {
            $blocked = match ($user->status) {
                User::STATUS_PENDING => 'Your application is still under review. You will receive an email once it is approved.',
                User::STATUS_REJECTED => 'Your application was not approved.',
                User::STATUS_SUSPENDED => 'Your account has been suspended. Please contact the administrator.',
                default => null,
            };

            if ($blocked) {
                return back()->withInput($request->only('email'))->withErrors(['email' => $blocked]);
            }

            RateLimiter::clear($key);
            Auth::login($user, $request->boolean('remember'));
            $request->session()->regenerate();
            $user->forceFill(['last_login_at' => now()])->save();

            return redirect()->intended($user->isModerator() ? route('admin.dashboard') : route('community.index'));
        }

        RateLimiter::hit($key, 300);

        return back()->withInput($request->only('email'))->withErrors(['email' => 'These credentials do not match our records.']);
    }

    public function destroy(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}

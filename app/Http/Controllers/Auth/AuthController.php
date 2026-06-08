<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\LoginHistory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) return redirect()->route('dashboard');
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $key = 'login.' . Str::lower($request->email) . '.' . $request->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            throw ValidationException::withMessages([
                'email' => "Too many login attempts. Try again in {$seconds} seconds.",
            ]);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            RateLimiter::hit($key, 300);
            $this->recordFailedLogin($request, null);
            throw ValidationException::withMessages(['email' => 'These credentials do not match our records.']);
        }

        if ($user->isLocked()) {
            $this->recordFailedLogin($request, $user, 'locked');
            throw ValidationException::withMessages([
                'email' => 'Account is temporarily locked. Try again in ' . now()->diffForHumans($user->locked_until, true) . '.',
            ]);
        }

        if (!$user->is_active) {
            throw ValidationException::withMessages(['email' => 'Your account has been deactivated. Contact your administrator.']);
        }

        if (!Hash::check($request->password, $user->password)) {
            RateLimiter::hit($key, 300);
            $user->increment('failed_login_attempts');
            if ($user->failed_login_attempts >= 5) {
                $user->update(['locked_until' => now()->addMinutes(30)]);
            }
            $this->recordFailedLogin($request, $user);
            throw ValidationException::withMessages(['email' => 'These credentials do not match our records.']);
        }

        // Success
        $user->update(['failed_login_attempts' => 0, 'locked_until' => null]);
        RateLimiter::clear($key);

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        $this->recordSuccessLogin($request, $user);

        return redirect()->intended(route('dashboard'));
    }

    public function logout(Request $request)
    {
        $loginHistory = LoginHistory::where('user_id', Auth::id())
            ->whereNull('logged_out_at')
            ->latest('logged_in_at')
            ->first();

        if ($loginHistory) {
            $loginHistory->update(['logged_out_at' => now()]);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Logged out successfully.');
    }

    public function showForgotPassword()
    {
        return view('auth.forgot-password');
    }

    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink($request->only('email'));

        return $status === Password::RESET_LINK_SENT
            ? back()->with('success', 'Password reset link sent to your email.')
            : back()->withErrors(['email' => __($status)]);
    }

    public function showResetPassword(string $token)
    {
        return view('auth.reset-password', ['token' => $token]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill(['password' => Hash::make($password)])->save();
                $user->tokens()->delete();
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('success', 'Password reset successfully. Please login.')
            : back()->withErrors(['email' => [__($status)]]);
    }

    private function recordSuccessLogin(Request $request, User $user): void
    {
        $parsed = LoginHistory::parseUserAgent($request->userAgent() ?? '');
        LoginHistory::create([
            'user_id' => $user->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'device_type' => $parsed['device'],
            'browser' => $parsed['browser'],
            'platform' => $parsed['platform'],
            'status' => 'success',
            'logged_in_at' => now(),
        ]);
    }

    private function recordFailedLogin(Request $request, ?User $user, string $status = 'failed'): void
    {
        if (!$user) return;
        $parsed = LoginHistory::parseUserAgent($request->userAgent() ?? '');
        LoginHistory::create([
            'user_id' => $user->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'device_type' => $parsed['device'],
            'browser' => $parsed['browser'],
            'platform' => $parsed['platform'],
            'status' => $status,
            'logged_in_at' => now(),
        ]);
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LoginHistory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class AuthApiController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'device_name' => 'required|string|max:255',
        ]);

        $key = 'api.login.' . Str::lower($request->email) . '.' . $request->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            return response()->json(['message' => 'Too many login attempts.'], 429);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            RateLimiter::hit($key, 300);
            return response()->json(['message' => 'Invalid credentials.'], 401);
        }

        if (!$user->is_active) {
            return response()->json(['message' => 'Account is deactivated.'], 403);
        }

        if ($user->isLocked()) {
            return response()->json(['message' => 'Account is temporarily locked.'], 423);
        }

        $user->update(['failed_login_attempts' => 0, 'locked_until' => null]);
        RateLimiter::clear($key);

        $token = $user->createToken($request->device_name)->plainTextToken;

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

        return response()->json([
            'token' => $token,
            'user' => $this->formatUser($user),
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out successfully.']);
    }

    public function me(Request $request)
    {
        return response()->json(['user' => $this->formatUser($request->user())]);
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
        ]);

        if ($request->filled('current_password')) {
            $request->validate([
                'current_password' => 'required',
                'new_password' => 'required|min:8|confirmed',
            ]);
            if (!Hash::check($request->current_password, $user->password)) {
                return response()->json(['message' => 'Current password is incorrect.'], 422);
            }
            $validated['password'] = Hash::make($request->new_password);
        }

        $user->update($validated);
        return response()->json(['user' => $this->formatUser($user->fresh())]);
    }

    private function formatUser(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'role' => $user->role,
            'role_label' => $user->getRoleLabel(),
            'employee_id' => $user->employee_id,
            'designation' => $user->designation,
            'avatar_url' => $user->avatar_url,
            'is_active' => $user->is_active,
            'joining_date' => $user->joining_date?->format('Y-m-d'),
        ];
    }
}

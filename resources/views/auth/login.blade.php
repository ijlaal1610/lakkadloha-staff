@extends('layouts.auth')
@section('title', 'Sign In')

@section('content')
<div class="auth-title">Welcome back</div>
<div class="auth-subtitle">Sign in to your account to continue</div>

@if(session('success'))
    <div class="alert alert-success py-2 mb-3" style="background:var(--success-bg);border-color:var(--success);color:var(--success);font-size:13px;border-radius:8px;">
        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
    </div>
@endif

<form action="{{ route('login.post') }}" method="POST" data-loading>
    @csrf

    <div class="mb-3">
        <label class="form-label">Email Address</label>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                placeholder="you@example.com" value="{{ old('email') }}" required autofocus />
        </div>
        @error('email')
            <div class="text-danger mt-1" style="font-size:12px;">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center mb-1">
            <label class="form-label mb-0">Password</label>
            <a href="{{ route('password.request') }}" style="font-size:12px;color:var(--primary);text-decoration:none;">
                Forgot password?
            </a>
        </div>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-lock"></i></span>
            <input type="password" name="password" id="passwordField"
                class="form-control @error('password') is-invalid @enderror"
                placeholder="••••••••" required />
            <button type="button" class="input-group-text" onclick="togglePassword()">
                <i class="bi bi-eye" id="eyeIcon"></i>
            </button>
        </div>
        @error('password')
            <div class="text-danger mt-1" style="font-size:12px;">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-4 d-flex align-items-center gap-2">
        <input type="checkbox" name="remember" id="remember" class="form-check-input" style="margin:0;" />
        <label for="remember" style="font-size:13px;color:var(--text-2);cursor:pointer;">Remember me for 8 hours</label>
    </div>

    <button type="submit" class="btn btn-primary w-100" style="padding:12px;">
        <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
    </button>
</form>

<p class="text-center mt-4" style="font-size:12px;color:var(--text-3);">
    Protected by rate limiting & account lockout security
</p>

<script>
function togglePassword() {
    const field = document.getElementById('passwordField');
    const icon  = document.getElementById('eyeIcon');
    if (field.type === 'password') {
        field.type = 'text';
        icon.className = 'bi bi-eye-slash';
    } else {
        field.type = 'password';
        icon.className = 'bi bi-eye';
    }
}
</script>
@endsection

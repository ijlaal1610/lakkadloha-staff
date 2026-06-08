@extends('layouts.auth')
@section('title', 'Reset Password')

@section('content')
<div class="auth-title">Set new password</div>
<div class="auth-subtitle">Choose a strong password for your account</div>

<form action="{{ route('password.update') }}" method="POST" data-loading>
    @csrf
    <input type="hidden" name="token" value="{{ $token }}" />

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

    <div class="mb-3">
        <label class="form-label">New Password</label>
        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror"
            placeholder="Min. 8 characters" required minlength="8" />
        @error('password')
            <div class="text-danger mt-1" style="font-size:12px;">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-4">
        <label class="form-label">Confirm New Password</label>
        <input type="password" name="password_confirmation" class="form-control"
            placeholder="Re-enter new password" required minlength="8" />
    </div>

    <button type="submit" class="btn btn-primary w-100" style="padding:12px;">
        <i class="bi bi-check2-circle me-2"></i>Reset Password
    </button>
</form>

<div class="text-center mt-4">
    <a href="{{ route('login') }}" style="font-size:13px;color:var(--primary);text-decoration:none;">
        <i class="bi bi-arrow-left me-1"></i>Back to login
    </a>
</div>
@endsection

@extends('layouts.auth')
@section('title', 'Forgot Password')

@section('content')
<div class="auth-title">Reset password</div>
<div class="auth-subtitle">Enter your email and we'll send you a reset link</div>

@if(session('success'))
    <div class="alert mb-3 py-2" style="background:var(--success-bg);border-color:var(--success);color:var(--success);font-size:13px;border-radius:8px;">
        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
    </div>
@endif

<form action="{{ route('password.email') }}" method="POST" data-loading>
    @csrf
    <div class="mb-4">
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

    <button type="submit" class="btn btn-primary w-100" style="padding:12px;">
        <i class="bi bi-send me-2"></i>Send Reset Link
    </button>
</form>

<div class="text-center mt-4">
    <a href="{{ route('login') }}" style="font-size:13px;color:var(--primary);text-decoration:none;">
        <i class="bi bi-arrow-left me-1"></i>Back to login
    </a>
</div>
@endsection

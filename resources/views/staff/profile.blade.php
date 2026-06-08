@extends('layouts.app')
@section('title', 'My Profile')

@section('breadcrumb')
    <li class="breadcrumb-item active">Profile</li>
@endsection

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">My Profile</h1>
        <p class="page-subtitle">Manage your account information and password</p>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="card text-center mb-4">
            <div class="card-body p-4">
                <div style="position:relative;display:inline-block;margin-bottom:16px;">
                    <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}"
                        style="width:96px;height:96px;border-radius:50%;object-fit:cover;border:3px solid var(--primary);" />
                    <span class="badge {{ $user->role === 'super_admin' ? 'bg-danger' : ($user->role === 'manager' ? 'bg-primary' : 'bg-secondary') }}"
                        style="position:absolute;bottom:0;right:0;font-size:10px;padding:4px 6px;">
                        {{ $user->getRoleLabel() }}
                    </span>
                </div>
                <h5 style="font-size:18px;font-weight:700;color:var(--text);">{{ $user->name }}</h5>
                <p style="font-size:13px;color:var(--text-3);">{{ $user->designation ?? $user->getRoleLabel() }}</p>
                @if($user->employee_id)
                <span style="font-size:12px;font-family:monospace;background:var(--bg-secondary);padding:4px 10px;border-radius:6px;color:var(--text-2);">
                    {{ $user->employee_id }}
                </span>
                @endif
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h5 class="card-title">Account Info</h5></div>
            <div class="card-body p-0">
                <div class="info-row px-4"><span class="info-label">Email</span><span class="info-value" style="font-size:12px;">{{ $user->email }}</span></div>
                <div class="info-row px-4"><span class="info-label">Phone</span><span class="info-value">{{ $user->phone ?? '—' }}</span></div>
                <div class="info-row px-4"><span class="info-label">Joined</span><span class="info-value">{{ $user->joining_date ? $user->joining_date->format('d M Y') : '—' }}</span></div>
                <div class="info-row px-4"><span class="info-label">Member Since</span><span class="info-value">{{ $user->created_at->format('d M Y') }}</span></div>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header"><h5 class="card-title">Edit Profile</h5></div>
            <div class="card-body">
                <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" data-loading>
                    @csrf @method('PUT')

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Full Name</label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                value="{{ old('name', $user->name) }}" required />
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" class="form-control"
                                value="{{ old('phone', $user->phone) }}" placeholder="+91 9876543210" />
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Address</label>
                        <textarea name="address" class="form-control" rows="2">{{ old('address', $user->address) }}</textarea>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Profile Photo</label>
                        <input type="file" name="avatar" class="form-control" accept="image/*" onchange="previewAvatar(this)" />
                        <div id="avatarPreviewWrap" style="{{ $user->avatar ? '' : 'display:none;' }}margin-top:10px;">
                            <img id="avatarPreview" src="{{ $user->avatar ? asset('storage/'.$user->avatar) : '' }}" style="height:64px;border-radius:50%;border:2px solid var(--border);object-fit:cover;" />
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check2-circle me-2"></i>Save Changes
                    </button>
                </form>
            </div>
        </div>

        <!-- Change Password -->
        <div class="card">
            <div class="card-header"><h5 class="card-title">Change Password</h5></div>
            <div class="card-body">
                <form action="{{ route('profile.update') }}" method="POST" data-loading>
                    @csrf @method('PUT')

                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-label">Current Password</label>
                            <input type="password" name="current_password" class="form-control @error('current_password') is-invalid @enderror"
                                placeholder="••••••••" />
                            @error('current_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">New Password</label>
                            <input type="password" name="new_password" class="form-control @error('new_password') is-invalid @enderror"
                                placeholder="Min. 8 characters" minlength="8" />
                            @error('new_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Confirm New Password</label>
                            <input type="password" name="new_password_confirmation" class="form-control" placeholder="Confirm" minlength="8" />
                        </div>
                    </div>

                    <button type="submit" class="btn btn-outline-primary">
                        <i class="bi bi-lock me-2"></i>Update Password
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function previewAvatar(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            document.getElementById('avatarPreview').src = e.target.result;
            document.getElementById('avatarPreviewWrap').style.display = 'block';
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endsection

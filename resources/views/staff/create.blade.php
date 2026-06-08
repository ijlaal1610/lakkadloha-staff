@extends('layouts.app')
@section('title', 'Add Staff Member')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('staff.index') }}" style="color:var(--text-2);text-decoration:none;">Staff</a></li>
    <li class="breadcrumb-item active">Add Member</li>
@endsection

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Add Staff Member</h1>
        <p class="page-subtitle">Create a new account for a team member</p>
    </div>
    <a href="{{ route('staff.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-2"></i>Back
    </a>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header"><h5 class="card-title">Account Information</h5></div>
            <div class="card-body">
                <form action="{{ route('staff.store') }}" method="POST" enctype="multipart/form-data" data-loading>
                    @csrf

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                value="{{ old('name') }}" required placeholder="e.g. Rajan Verma" autofocus />
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email Address <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                value="{{ old('email') }}" required placeholder="rajan@example.com" />
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Role <span class="text-danger">*</span></label>
                            <select name="role" class="form-select @error('role') is-invalid @enderror" required>
                                <option value="">— Select Role —</option>
                                <option value="manager" {{ old('role') === 'manager' ? 'selected' : '' }}>Manager</option>
                                <option value="staff" {{ old('role') === 'staff' ? 'selected' : '' }}>Staff</option>
                            </select>
                            @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Designation</label>
                            <input type="text" name="designation" class="form-control"
                                value="{{ old('designation') }}" placeholder="e.g. Sales Executive" />
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Phone</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-phone"></i></span>
                                <input type="text" name="phone" class="form-control" value="{{ old('phone') }}" placeholder="+91 9876543210" />
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Joining Date</label>
                            <input type="date" name="joining_date" class="form-control"
                                value="{{ old('joining_date', today()->format('Y-m-d')) }}" />
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Address</label>
                        <textarea name="address" class="form-control" rows="2" placeholder="Home address…">{{ old('address') }}</textarea>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Password <span class="text-danger">*</span></label>
                            <input type="password" name="password" id="passwordInput"
                                class="form-control @error('password') is-invalid @enderror"
                                required minlength="8" placeholder="Min. 8 characters" />
                            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Confirm Password <span class="text-danger">*</span></label>
                            <input type="password" name="password_confirmation" class="form-control"
                                required minlength="8" placeholder="Re-enter password" />
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Profile Photo (optional)</label>
                        <input type="file" name="avatar" class="form-control" accept="image/*" onchange="previewAvatar(this)" />
                        <div id="avatarPreview" style="display:none;margin-top:12px;">
                            <img id="avatarImg" src="" alt="" style="width:72px;height:72px;border-radius:50%;object-fit:cover;border:2px solid var(--border);" />
                        </div>
                    </div>

                    <div class="d-flex gap-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-person-plus me-2"></i>Create Account
                        </button>
                        <a href="{{ route('staff.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header"><h5 class="card-title">Role Permissions</h5></div>
            <div class="card-body p-0">
                <div class="p-3 border-bottom" style="border-color:var(--border-light)!important;">
                    <div style="font-size:13px;font-weight:700;color:var(--primary);margin-bottom:8px;"><i class="bi bi-shield-check me-1"></i>Manager</div>
                    <ul style="font-size:12px;color:var(--text-2);margin:0;padding-left:16px;line-height:2;">
                        <li>View all reports & inventory</li>
                        <li>Create & edit inventory</li>
                        <li>Manage attendance & salary</li>
                        <li>View all staff sales</li>
                    </ul>
                </div>
                <div class="p-3">
                    <div style="font-size:13px;font-weight:700;color:var(--text-2);margin-bottom:8px;"><i class="bi bi-person me-1"></i>Staff</div>
                    <ul style="font-size:12px;color:var(--text-2);margin:0;padding-left:16px;line-height:2;">
                        <li>Create sales</li>
                        <li>View own attendance</li>
                        <li>View own salary records</li>
                        <li>View own profile</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function previewAvatar(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            document.getElementById('avatarImg').src = e.target.result;
            document.getElementById('avatarPreview').style.display = 'block';
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endsection

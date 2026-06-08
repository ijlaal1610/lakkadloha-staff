@extends('layouts.app')
@section('title', 'Edit ' . $staff->name)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('staff.index') }}" style="color:var(--text-2);text-decoration:none;">Staff</a></li>
    <li class="breadcrumb-item"><a href="{{ route('staff.show', $staff) }}" style="color:var(--text-2);text-decoration:none;">{{ $staff->name }}</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Edit Staff Member</h1>
        <p class="page-subtitle">Update {{ $staff->name }}'s account information</p>
    </div>
    <a href="{{ route('staff.show', $staff) }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-2"></i>Back
    </a>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header"><h5 class="card-title">Account Information</h5></div>
            <div class="card-body">
                <form action="{{ route('staff.update', $staff) }}" method="POST" enctype="multipart/form-data" data-loading>
                    @csrf @method('PUT')

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                value="{{ old('name', $staff->name) }}" required />
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                value="{{ old('email', $staff->email) }}" required />
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-label">Role <span class="text-danger">*</span></label>
                            <select name="role" class="form-select">
                                @if(auth()->user()->isSuperAdmin())
                                <option value="super_admin" {{ old('role', $staff->role) === 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                                @endif
                                <option value="manager" {{ old('role', $staff->role) === 'manager' ? 'selected' : '' }}>Manager</option>
                                <option value="staff" {{ old('role', $staff->role) === 'staff' ? 'selected' : '' }}>Staff</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Designation</label>
                            <input type="text" name="designation" class="form-control" value="{{ old('designation', $staff->designation) }}" />
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Status</label>
                            <select name="is_active" class="form-select">
                                <option value="1" {{ (old('is_active', $staff->is_active) ? 'selected' : '') }}>Active</option>
                                <option value="0" {{ (!old('is_active', $staff->is_active) ? 'selected' : '') }}>Inactive</option>
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" class="form-control" value="{{ old('phone', $staff->phone) }}" />
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Joining Date</label>
                            <input type="date" name="joining_date" class="form-control"
                                value="{{ old('joining_date', $staff->joining_date ? $staff->joining_date->format('Y-m-d') : '') }}" />
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Address</label>
                        <textarea name="address" class="form-control" rows="2">{{ old('address', $staff->address) }}</textarea>
                    </div>

                    <hr style="border-color:var(--border-light);">
                    <p style="font-size:13px;color:var(--text-3);margin-bottom:16px;">Leave password fields blank to keep current password.</p>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label">New Password</label>
                            <input type="password" name="password" class="form-control" minlength="8" placeholder="Leave blank to keep current" />
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Confirm Password</label>
                            <input type="password" name="password_confirmation" class="form-control" placeholder="Repeat new password" />
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Profile Photo</label>
                        @if($staff->avatar)
                        <div class="mb-2"><img src="{{ $staff->avatar_url }}" style="height:60px;border-radius:50%;border:2px solid var(--border);" /></div>
                        @endif
                        <input type="file" name="avatar" class="form-control" accept="image/*" />
                    </div>

                    <div class="d-flex gap-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check2-circle me-2"></i>Save Changes
                        </button>
                        <a href="{{ route('staff.show', $staff) }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

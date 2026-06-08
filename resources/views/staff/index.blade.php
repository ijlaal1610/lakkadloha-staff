@extends('layouts.app')
@section('title', 'Staff')

@section('breadcrumb')
    <li class="breadcrumb-item active">Staff</li>
@endsection

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Staff Management</h1>
        <p class="page-subtitle">Manage your team members and their access</p>
    </div>
    @if(auth()->user()->isSuperAdmin())
    <a href="{{ route('staff.create') }}" class="btn btn-primary">
        <i class="bi bi-person-plus-fill me-2"></i>Add Staff
    </a>
    @endif
</div>

<!-- Stats -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon stat-icon-primary"><i class="bi bi-people-fill"></i></div>
            <div><div class="stat-value">{{ $stats['total'] }}</div><div class="stat-label">Total Users</div></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon stat-icon-success"><i class="bi bi-person-check-fill"></i></div>
            <div><div class="stat-value">{{ $stats['active'] }}</div><div class="stat-label">Active</div></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon stat-icon-info"><i class="bi bi-person-badge-fill"></i></div>
            <div><div class="stat-value">{{ $stats['managers'] }}</div><div class="stat-label">Managers</div></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon stat-icon-warning"><i class="bi bi-person-fill"></i></div>
            <div><div class="stat-value">{{ $stats['staff'] }}</div><div class="stat-label">Staff Members</div></div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body py-3">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control" placeholder="Search by name, email, ID…"
                    value="{{ request('search') }}" />
            </div>
            <div class="col-md-2">
                <select name="role" class="form-select">
                    <option value="">All Roles</option>
                    <option value="super_admin" {{ request('role') === 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                    <option value="manager" {{ request('role') === 'manager' ? 'selected' : '' }}>Manager</option>
                    <option value="staff" {{ request('role') === 'staff' ? 'selected' : '' }}>Staff</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search me-1"></i>Filter</button>
            </div>
        </form>
    </div>
</div>

<!-- Staff List -->
<div class="table-card">
    @if($staff->count() > 0)
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Employee</th>
                    <th>Role</th>
                    <th>Employee ID</th>
                    <th>Joined</th>
                    <th>Sales</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($staff as $member)
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-3">
                            <img src="{{ $member->avatar_url }}" alt="{{ $member->name }}" style="width:38px;height:38px;border-radius:50%;object-fit:cover;" />
                            <div>
                                <div style="font-size:14px;font-weight:600;color:var(--text);">{{ $member->name }}</div>
                                <div style="font-size:12px;color:var(--text-3);">{{ $member->email }}</div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="badge {{ $member->role === 'super_admin' ? 'bg-danger' : ($member->role === 'manager' ? 'bg-primary' : 'bg-secondary') }}">
                            {{ $member->getRoleLabel() }}
                        </span>
                    </td>
                    <td style="font-size:12px;font-family:monospace;color:var(--text-2);">{{ $member->employee_id ?? '—' }}</td>
                    <td style="font-size:13px;color:var(--text-2);">{{ $member->joining_date ? $member->joining_date->format('d M Y') : '—' }}</td>
                    <td style="font-size:13px;font-weight:600;">{{ number_format($member->sales_count) }}</td>
                    <td>
                        @if($member->is_active)
                            <span class="status-badge status-present">Active</span>
                        @else
                            <span class="status-badge status-absent">Inactive</span>
                        @endif
                    </td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="{{ route('staff.show', $member) }}" class="btn btn-xs btn-outline-primary" title="View">
                                <i class="bi bi-eye"></i>
                            </a>
                            @if(auth()->user()->isSuperAdmin())
                            <a href="{{ route('staff.edit', $member) }}" class="btn btn-xs btn-outline-secondary" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="{{ route('staff.toggle-status', $member) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-xs {{ $member->is_active ? 'btn-outline-warning' : 'btn-outline-success' }}"
                                    title="{{ $member->is_active ? 'Deactivate' : 'Activate' }}">
                                    <i class="bi {{ $member->is_active ? 'bi-pause-fill' : 'bi-play-fill' }}"></i>
                                </button>
                            </form>
                            @if($member->id !== auth()->id())
                            <form action="{{ route('staff.destroy', $member) }}" method="POST"
                                onsubmit="return confirm('Remove {{ addslashes($member->name) }} permanently?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-xs btn-outline-danger" title="Remove">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                            @endif
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="p-3">{{ $staff->links() }}</div>
    @else
    <div class="empty-state">
        <i class="bi bi-people"></i>
        <h5>No Staff Found</h5>
        <p>{{ request()->hasAny(['search','role','status']) ? 'No staff match your filters.' : 'Add your first staff member.' }}</p>
        @if(auth()->user()->isSuperAdmin())
        <a href="{{ route('staff.create') }}" class="btn btn-primary">
            <i class="bi bi-person-plus me-2"></i>Add First Staff
        </a>
        @endif
    </div>
    @endif
</div>
@endsection

@extends('layouts.app')
@section('title', 'Audit Logs')

@section('breadcrumb')
    <li class="breadcrumb-item active">Audit Logs</li>
@endsection

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Audit Logs</h1>
        <p class="page-subtitle">Complete activity trail — every action recorded</p>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body py-3">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <input type="text" name="search" class="form-control" placeholder="Search activity…" value="{{ request('search') }}" />
            </div>
            <div class="col-md-2">
                <select name="module" class="form-select">
                    <option value="">All Modules</option>
                    @foreach($modules as $mod)
                    <option value="{{ $mod }}" {{ request('module') === $mod ? 'selected' : '' }}>{{ ucfirst($mod) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="user_id" class="form-select">
                    <option value="">All Users</option>
                    @foreach($users as $u)
                    <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}" />
            </div>
            <div class="col-md-1">
                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search"></i></button>
            </div>
        </form>
    </div>
</div>

<div class="table-card">
    @if($logs->count() > 0)
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Time</th>
                    <th>User</th>
                    <th>Module</th>
                    <th>Action</th>
                    <th>Description</th>
                    <th>IP Address</th>
                </tr>
            </thead>
            <tbody>
                @foreach($logs as $log)
                <tr>
                    <td style="white-space:nowrap;font-size:12px;color:var(--text-3);">
                        {{ $log->created_at->format('d M Y') }}<br>
                        <span style="font-size:11px;">{{ $log->created_at->format('H:i:s') }}</span>
                    </td>
                    <td>
                        @if($log->user)
                        <div style="font-size:13px;font-weight:600;color:var(--text);">{{ $log->user->name }}</div>
                        <div style="font-size:11px;color:var(--text-3);">{{ $log->user->getRoleLabel() }}</div>
                        @else
                        <span style="font-size:12px;color:var(--text-3);">System</span>
                        @endif
                    </td>
                    <td>
                        @php
                            $modColors = ['sales' => 'primary', 'inventory' => 'info', 'attendance' => 'success', 'salary' => 'warning', 'staff' => 'danger', 'auth' => 'secondary'];
                            $color = $modColors[$log->module] ?? 'secondary';
                        @endphp
                        <span class="badge bg-{{ $color }} bg-opacity-75">{{ ucfirst($log->module) }}</span>
                    </td>
                    <td style="font-size:12px;font-weight:600;color:var(--text-2);">{{ str_replace('_', ' ', ucfirst($log->action)) }}</td>
                    <td style="font-size:13px;max-width:280px;">{{ $log->description }}</td>
                    <td style="font-size:12px;font-family:monospace;color:var(--text-3);">{{ $log->ip_address ?? '—' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="p-3">{{ $logs->links() }}</div>
    @else
    <div class="empty-state">
        <i class="bi bi-journal-text"></i>
        <h5>No Audit Logs</h5>
        <p>Activity logs will appear here as actions are performed.</p>
    </div>
    @endif
</div>
@endsection

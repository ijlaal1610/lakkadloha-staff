@extends('layouts.app')
@section('title', 'Attendance')

@section('breadcrumb')
    <li class="breadcrumb-item active">Attendance</li>
@endsection

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Attendance</h1>
        <p class="page-subtitle">
            @if(auth()->user()->isAdminOrManager())
                Manage staff attendance — {{ isset($date) ? $date->format('D, d M Y') : today()->format('D, d M Y') }}
            @else
                Your attendance for {{ isset($month) ? $month->format('F Y') : now()->format('F Y') }}
            @endif
        </p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        @if(auth()->user()->isStaff())
            @if(!isset($todayAttendance) || !$todayAttendance)
                <form action="{{ route('attendance.check-in') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-box-arrow-in-right me-2"></i>Check In
                    </button>
                </form>
            @elseif(isset($todayAttendance) && $todayAttendance && !$todayAttendance->time_out)
                <form action="{{ route('attendance.check-out') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-outline-primary">
                        <i class="bi bi-box-arrow-right me-2"></i>Check Out
                    </button>
                </form>
            @else
                <span class="btn" style="background:var(--success-bg);color:var(--success);border:none;cursor:default;">
                    <i class="bi bi-check-circle me-1"></i>{{ $todayAttendance->time_in }} – {{ $todayAttendance->time_out }}
                </span>
            @endif
        @endif
        @can('manage', App\Models\Attendance::class)
        <a href="{{ route('attendance.report') }}" class="btn btn-outline-primary">
            <i class="bi bi-bar-chart me-2"></i>Reports
        </a>
        @endcan
    </div>
</div>

@if(auth()->user()->isAdminOrManager())
<!-- Admin View -->
<!-- Stats -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon stat-icon-success"><i class="bi bi-person-check-fill"></i></div>
            <div><div class="stat-value">{{ $stats['present'] }}</div><div class="stat-label">Present</div></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon stat-icon-danger"><i class="bi bi-person-x-fill"></i></div>
            <div><div class="stat-value">{{ $stats['absent'] }}</div><div class="stat-label">Absent</div></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon stat-icon-warning"><i class="bi bi-clock-fill"></i></div>
            <div><div class="stat-value">{{ $stats['late'] }}</div><div class="stat-label">Late</div></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon stat-icon-info"><i class="bi bi-calendar2-minus-fill"></i></div>
            <div><div class="stat-value">{{ $stats['leave'] }}</div><div class="stat-label">On Leave</div></div>
        </div>
    </div>
</div>

<!-- Date Filter -->
<div class="card mb-4">
    <div class="card-body py-3">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Date</label>
                <input type="date" name="date" class="form-control" value="{{ isset($date) ? $date->format('Y-m-d') : today()->format('Y-m-d') }}" />
            </div>
            <div class="col-md-3">
                <label class="form-label">Staff</label>
                <select name="staff_id" class="form-select">
                    <option value="">All Staff</option>
                    @foreach($staffList as $s)
                    <option value="{{ $s->id }}" {{ request('staff_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search me-1"></i>Filter</button>
            </div>
        </form>
    </div>
</div>

<!-- Bulk Mark Form -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title">Staff Attendance — {{ isset($date) ? $date->format('D, d M Y') : today()->format('D, d M Y') }}</h5>
        <button class="btn btn-primary btn-sm" type="submit" form="bulkAttendanceForm">
            <i class="bi bi-check2-all me-2"></i>Save All
        </button>
    </div>
    <form id="bulkAttendanceForm" action="{{ route('attendance.bulk-mark') }}" method="POST" data-loading>
        @csrf
        <input type="hidden" name="date" value="{{ isset($date) ? $date->format('Y-m-d') : today()->format('Y-m-d') }}" />
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Staff</th>
                        <th>Status</th>
                        <th>Time In</th>
                        <th>Time Out</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($allStaff as $i => $staffMember)
                    @php
                        $att = $attendances->firstWhere('user_id', $staffMember->id);
                    @endphp
                    <tr>
                        <input type="hidden" name="attendances[{{ $i }}][user_id]" value="{{ $staffMember->id }}" />
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <img src="{{ $staffMember->avatar_url }}" alt="{{ $staffMember->name }}" class="avatar-sm" style="border-radius:50%;object-fit:cover;" />
                                <div>
                                    <div style="font-size:13px;font-weight:600;color:var(--text);">{{ $staffMember->name }}</div>
                                    <div style="font-size:11px;color:var(--text-3);">{{ $staffMember->getRoleLabel() }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <select name="attendances[{{ $i }}][status]" class="form-select form-select-sm" style="min-width:120px;">
                                @foreach(['present','absent','late','half_day','leave','holiday'] as $s)
                                <option value="{{ $s }}" {{ ($att && $att->status === $s) ? 'selected' : ($s === 'present' && !$att ? 'selected' : '') }}>
                                    {{ ucwords(str_replace('_', ' ', $s)) }}
                                </option>
                                @endforeach
                            </select>
                        </td>
                        <td>
                            <input type="time" name="attendances[{{ $i }}][time_in]" class="form-control form-control-sm" style="min-width:110px;"
                                value="{{ $att?->time_in ?? '' }}" />
                        </td>
                        <td>
                            <input type="time" name="attendances[{{ $i }}][time_out]" class="form-control form-control-sm" style="min-width:110px;"
                                value="{{ $att?->time_out ?? '' }}" />
                        </td>
                        <td>
                            <input type="text" class="form-control form-control-sm" style="min-width:140px;"
                                placeholder="Optional notes" value="{{ $att?->notes ?? '' }}" disabled />
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </form>
</div>

@else
<!-- Staff View: Monthly Calendar -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon stat-icon-success"><i class="bi bi-calendar-check"></i></div>
            <div><div class="stat-value">{{ $stats['present'] }}</div><div class="stat-label">Days Present</div></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon stat-icon-danger"><i class="bi bi-calendar-x"></i></div>
            <div><div class="stat-value">{{ $stats['absent'] }}</div><div class="stat-label">Days Absent</div></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon stat-icon-warning"><i class="bi bi-clock"></i></div>
            <div><div class="stat-value">{{ $stats['late'] }}</div><div class="stat-label">Late Days</div></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon stat-icon-info"><i class="bi bi-calendar-minus"></i></div>
            <div><div class="stat-value">{{ $stats['half_day'] }}</div><div class="stat-label">Half Days</div></div>
        </div>
    </div>
</div>

<!-- Month nav -->
<div class="card mb-4">
    <div class="card-body py-3">
        <form method="GET" class="d-flex align-items-center gap-3">
            <input type="month" name="month" class="form-control" style="max-width:200px;"
                value="{{ isset($month) ? $month->format('Y-m') : now()->format('Y-m') }}" />
            <button type="submit" class="btn btn-primary"><i class="bi bi-calendar3 me-1"></i>View</button>
        </form>
    </div>
</div>

<div class="table-card">
    @if($attendances->count() > 0)
    <div class="table-responsive">
        <table class="table">
            <thead><tr><th>Date</th><th>Status</th><th>Time In</th><th>Time Out</th><th>Hours</th><th>Notes</th></tr></thead>
            <tbody>
                @foreach($attendances as $att)
                <tr>
                    <td style="font-weight:600;">{{ $att->date->format('D, d M') }}</td>
                    <td><span class="status-badge status-{{ $att->status }}">{{ ucwords(str_replace('_',' ',$att->status)) }}</span></td>
                    <td>{{ $att->time_in ? \Carbon\Carbon::parse($att->time_in)->format('h:i A') : '—' }}</td>
                    <td>{{ $att->time_out ? \Carbon\Carbon::parse($att->time_out)->format('h:i A') : '—' }}</td>
                    <td style="font-size:13px;color:var(--text-2);">{{ $att->working_hours ?? '—' }}</td>
                    <td style="font-size:12px;color:var(--text-3);">{{ $att->notes ?? '—' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="empty-state">
        <i class="bi bi-calendar-x"></i>
        <h5>No Records Found</h5>
        <p>No attendance records for {{ isset($month) ? $month->format('F Y') : now()->format('F Y') }}.</p>
    </div>
    @endif
</div>
@endif
@endsection

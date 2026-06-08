@extends('layouts.app')
@section('title', 'Attendance Report')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('reports.index') }}" style="color:var(--text-2);text-decoration:none;">Reports</a></li>
    <li class="breadcrumb-item active">Attendance</li>
@endsection

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Attendance Report</h1>
        <p class="page-subtitle">{{ $from->format('d M Y') }} — {{ $to->format('d M Y') }}</p>
    </div>
    <a href="{{ route('reports.attendance.pdf', request()->all()) }}" class="btn btn-outline-danger" target="_blank">
        <i class="bi bi-file-pdf me-2"></i>Export PDF
    </a>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body py-3">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-2">
                <label class="form-label">From</label>
                <input type="date" name="from" class="form-control" value="{{ $from->format('Y-m-d') }}" />
            </div>
            <div class="col-md-2">
                <label class="form-label">To</label>
                <input type="date" name="to" class="form-control" value="{{ $to->format('Y-m-d') }}" />
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search me-1"></i>Apply</button>
            </div>
        </form>
    </div>
</div>

<!-- Summary table by staff -->
<div class="card mb-4">
    <div class="card-header"><h5 class="card-title"><i class="bi bi-people me-2"></i>Staff Summary</h5></div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Staff</th>
                    <th style="color:var(--success);">Present</th>
                    <th style="color:var(--danger);">Absent</th>
                    <th style="color:var(--warning);">Late</th>
                    <th style="color:var(--info);">Half Day</th>
                    <th style="color:var(--primary);">Leave</th>
                    <th>Total Days</th>
                    <th>Attendance %</th>
                </tr>
            </thead>
            <tbody>
                @foreach($summary as $row)
                @php $pct = $row['total_days'] > 0 ? round(($row['present'] / $row['total_days']) * 100) : 0; @endphp
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <img src="{{ $row['staff']->avatar_url }}" style="width:32px;height:32px;border-radius:50%;object-fit:cover;" />
                            <div>
                                <div style="font-size:13px;font-weight:600;">{{ $row['staff']->name }}</div>
                                <div style="font-size:11px;color:var(--text-3);">{{ $row['staff']->getRoleLabel() }}</div>
                            </div>
                        </div>
                    </td>
                    <td style="font-weight:700;color:var(--success);">{{ $row['present'] }}</td>
                    <td style="font-weight:700;color:var(--danger);">{{ $row['absent'] }}</td>
                    <td style="font-weight:700;color:var(--warning);">{{ $row['late'] }}</td>
                    <td>{{ $row['half_day'] }}</td>
                    <td>{{ $row['leave'] }}</td>
                    <td>{{ $row['total_days'] }}</td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="stock-bar" style="width:80px;">
                                <div class="stock-bar-fill {{ $pct >= 80 ? 'high' : ($pct >= 60 ? 'medium' : 'low') }}"
                                    style="width:{{ $pct }}%;"></div>
                            </div>
                            <span style="font-size:12px;font-weight:600;color:{{ $pct >= 80 ? 'var(--success)' : ($pct >= 60 ? 'var(--warning)' : 'var(--danger)') }};">
                                {{ $pct }}%
                            </span>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- Detailed records -->
<div class="table-card">
    <div class="p-4 border-bottom" style="border-color:var(--border-light)!important;">
        <h5 class="card-title mb-0">Daily Records ({{ $records->count() }} entries)</h5>
    </div>
    @if($records->count() > 0)
    <div class="table-responsive">
        <table class="table">
            <thead><tr><th>Date</th><th>Staff</th><th>Status</th><th>Time In</th><th>Time Out</th><th>Hours</th></tr></thead>
            <tbody>
                @foreach($records->sortByDesc('date') as $att)
                <tr>
                    <td>{{ $att->date->format('D, d M Y') }}</td>
                    <td style="font-weight:500;">{{ $att->user->name ?? '—' }}</td>
                    <td><span class="status-badge status-{{ $att->status }}">{{ ucwords(str_replace('_',' ',$att->status)) }}</span></td>
                    <td>{{ $att->time_in ? \Carbon\Carbon::parse($att->time_in)->format('h:i A') : '—' }}</td>
                    <td>{{ $att->time_out ? \Carbon\Carbon::parse($att->time_out)->format('h:i A') : '—' }}</td>
                    <td style="font-size:12px;color:var(--text-2);">{{ $att->working_hours ?? '—' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="empty-state"><i class="bi bi-calendar-x"></i><p>No attendance records for this period.</p></div>
    @endif
</div>
@endsection

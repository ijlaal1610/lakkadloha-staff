@extends('layouts.app')
@section('title', 'Attendance Report')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('attendance.index') }}" style="color:var(--text-2);text-decoration:none;">Attendance</a></li>
    <li class="breadcrumb-item active">Report</li>
@endsection

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Attendance Report</h1>
        <p class="page-subtitle">Detailed attendance analysis</p>
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
            <div class="col-md-3">
                <label class="form-label">Staff Member</label>
                <select name="staff_id" class="form-select">
                    <option value="">All Staff</option>
                    @foreach($staffList as $s)
                    <option value="{{ $s->id }}" {{ $staffId == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search me-1"></i>Apply</button>
            </div>
        </form>
    </div>
</div>

<!-- Summary by staff -->
<div class="card mb-4">
    <div class="card-header"><h5 class="card-title">Summary by Staff</h5></div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Staff</th>
                    <th style="color:var(--success);">Present</th>
                    <th style="color:var(--danger);">Absent</th>
                    <th style="color:var(--warning);">Late</th>
                    <th style="color:var(--info);">Half Day</th>
                    <th>Leave</th>
                    <th>Attendance %</th>
                </tr>
            </thead>
            <tbody>
                @foreach($summary as $row)
                @php
                    $totalDays = array_sum([$row['present'],$row['absent'],$row['late'],$row['half_day'],$row['leave']]);
                    $pct = $totalDays > 0 ? round(($row['present'] / $totalDays) * 100) : 0;
                @endphp
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <img src="{{ $row['staff']->avatar_url }}" style="width:30px;height:30px;border-radius:50%;object-fit:cover;" />
                            <span style="font-weight:600;font-size:13px;">{{ $row['staff']->name }}</span>
                        </div>
                    </td>
                    <td style="font-weight:700;color:var(--success);">{{ $row['present'] }}</td>
                    <td style="font-weight:700;color:var(--danger);">{{ $row['absent'] }}</td>
                    <td style="color:var(--warning);">{{ $row['late'] }}</td>
                    <td>{{ $row['half_day'] }}</td>
                    <td>{{ $row['leave'] }}</td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="stock-bar" style="width:80px;">
                                <div class="stock-bar-fill {{ $pct >= 80 ? 'high' : ($pct >= 60 ? 'medium' : 'low') }}" style="width:{{ $pct }}%;"></div>
                            </div>
                            <span style="font-size:12px;font-weight:700;color:{{ $pct >= 80 ? 'var(--success)' : ($pct >= 60 ? 'var(--warning)' : 'var(--danger)') }};">{{ $pct }}%</span>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@endsection

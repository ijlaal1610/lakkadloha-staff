@extends('layouts.app')
@section('title', 'Salary Report')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('reports.index') }}" style="color:var(--text-2);text-decoration:none;">Reports</a></li>
    <li class="breadcrumb-item active">Salary</li>
@endsection
@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Salary Report</h1>
        <p class="page-subtitle">{{ $from->format('d M Y') }} — {{ $to->format('d M Y') }}</p>
    </div>
    <a href="{{ route('reports.salary.pdf', request()->all()) }}" class="btn btn-outline-danger" target="_blank">
        <i class="bi bi-file-pdf me-2"></i>Export PDF
    </a>
</div>

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

<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="stat-card"><div class="stat-icon stat-icon-success"><i class="bi bi-cash-stack"></i></div><div><div class="stat-value" data-rupee>{{ $summary['total_salary'] }}</div><div class="stat-label">Total Salary</div></div></div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card"><div class="stat-icon stat-icon-warning"><i class="bi bi-wallet2"></i></div><div><div class="stat-value" data-rupee>{{ $summary['total_advances'] }}</div><div class="stat-label">Total Advances</div></div></div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card"><div class="stat-icon stat-icon-info"><i class="bi bi-gift"></i></div><div><div class="stat-value" data-rupee>{{ $summary['total_bonuses'] }}</div><div class="stat-label">Total Bonuses</div></div></div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card"><div class="stat-icon stat-icon-primary"><i class="bi bi-calculator"></i></div><div><div class="stat-value" data-rupee>{{ $summary['net_expense'] }}</div><div class="stat-label">Net Payout</div></div></div>
    </div>
</div>

<!-- Per employee breakdown -->
<div class="card mb-4">
    <div class="card-header"><h5 class="card-title">Per Employee Breakdown</h5></div>
    <div class="table-responsive">
        <table class="table">
            <thead><tr><th>Employee</th><th>Salary</th><th>Advances</th><th>Bonuses</th><th>Deductions</th><th>Net</th></tr></thead>
            <tbody>
                @foreach($byEmployee as $emp)
                <tr>
                    <td style="font-weight:600;">
                        <a href="{{ route('salary.employee-summary', $emp['employee']) }}" style="color:var(--primary);text-decoration:none;">
                            {{ $emp['employee']->name ?? '—' }}
                        </a>
                    </td>
                    <td style="color:var(--success);font-weight:600;">₹{{ number_format($emp['salary'], 2) }}</td>
                    <td style="color:var(--warning);">₹{{ number_format($emp['advance'], 2) }}</td>
                    <td style="color:var(--info);">₹{{ number_format($emp['bonus'], 2) }}</td>
                    <td style="color:var(--danger);">₹{{ number_format($emp['deduction'], 2) }}</td>
                    <td style="font-weight:700;">₹{{ number_format($emp['salary'] + $emp['bonus'] - $emp['deduction'], 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- All records -->
<div class="table-card">
    <div class="p-4 border-bottom" style="border-color:var(--border-light)!important;"><h5 class="card-title mb-0">All Records</h5></div>
    @if($records->count() > 0)
    <div class="table-responsive">
        <table class="table">
            <thead><tr><th>Date</th><th>Employee</th><th>Type</th><th>Amount</th><th>Method</th><th>Notes</th></tr></thead>
            <tbody>
                @foreach($records as $r)
                <tr>
                    <td>{{ $r->record_date->format('d M Y') }}</td>
                    <td style="font-weight:600;">{{ $r->employee->name ?? '—' }}</td>
                    <td><span class="badge {{ $r->type_badge_class }}">{{ $r->type_label }}</span></td>
                    <td style="font-weight:700;color:{{ in_array($r->type, ['salary','bonus']) ? 'var(--success)' : 'var(--danger)' }};">
                        {{ in_array($r->type, ['salary','bonus']) ? '+' : '-' }}₹{{ number_format($r->amount, 2) }}
                    </td>
                    <td style="font-size:12px;">{{ ucwords(str_replace('_',' ',$r->payment_method)) }}</td>
                    <td style="font-size:12px;color:var(--text-3);">{{ $r->notes ?? '—' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="empty-state"><i class="bi bi-cash-coin"></i><p>No records for this period.</p></div>
    @endif
</div>
@endsection

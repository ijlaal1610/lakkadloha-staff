@extends('layouts.app')
@section('title', $employee->name . ' — Salary Summary')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('salary.index') }}" style="color:var(--text-2);text-decoration:none;">Salary</a></li>
    <li class="breadcrumb-item active">{{ $employee->name }}</li>
@endsection

@section('content')
<div class="page-header">
    <div class="d-flex align-items-center gap-3">
        <img src="{{ $employee->avatar_url }}" alt="{{ $employee->name }}" style="width:56px;height:56px;border-radius:50%;object-fit:cover;border:3px solid var(--primary);" />
        <div>
            <h1 class="page-title">{{ $employee->name }}</h1>
            <p class="page-subtitle">{{ $employee->designation ?? $employee->getRoleLabel() }} &bull; {{ $employee->employee_id ?? '' }}</p>
        </div>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('salary.create') }}?employee_id={{ $employee->id }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-2"></i>Add Record
        </a>
        <a href="{{ route('salary.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Back
        </a>
    </div>
</div>

<!-- Stats -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon stat-icon-success"><i class="bi bi-cash-stack"></i></div>
            <div><div class="stat-value" data-rupee>{{ $stats['total_salary'] }}</div><div class="stat-label">Total Salary Paid</div></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon stat-icon-warning"><i class="bi bi-wallet2"></i></div>
            <div><div class="stat-value" data-rupee>{{ $stats['total_advances'] }}</div><div class="stat-label">Total Advances</div></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon stat-icon-info"><i class="bi bi-gift"></i></div>
            <div><div class="stat-value" data-rupee>{{ $stats['total_bonuses'] }}</div><div class="stat-label">Total Bonuses</div></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon stat-icon-danger"><i class="bi bi-dash-circle"></i></div>
            <div><div class="stat-value" data-rupee>{{ $stats['total_deductions'] }}</div><div class="stat-label">Total Deductions</div></div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Monthly breakdown chart -->
    <div class="col-lg-5">
        <div class="card">
            <div class="card-header"><h5 class="card-title"><i class="bi bi-bar-chart me-2 text-primary"></i>Monthly Breakdown</h5></div>
            <div class="card-body p-0">
                @foreach($monthlyBreakdown->groupBy('month')->take(6) as $month => $monthRecs)
                <div class="p-3 border-bottom" style="border-color:var(--border-light)!important;">
                    <div style="font-size:12px;font-weight:700;color:var(--text-2);margin-bottom:8px;">
                        {{ \Carbon\Carbon::createFromFormat('Y-m', $month)->format('M Y') }}
                    </div>
                    @foreach($monthRecs as $rec)
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="badge {{ $rec->type === 'salary' ? 'bg-success' : ($rec->type === 'advance' ? 'bg-warning' : ($rec->type === 'bonus' ? 'bg-info' : 'bg-danger')) }} bg-opacity-75" style="font-size:10px;">
                            {{ ucfirst($rec->type) }}
                        </span>
                        <span style="font-size:13px;font-weight:700;color:{{ in_array($rec->type, ['salary','bonus']) ? 'var(--success)' : 'var(--danger)' }};">
                            {{ in_array($rec->type, ['salary','bonus']) ? '+' : '-' }}₹{{ number_format($rec->total, 2) }}
                        </span>
                    </div>
                    @endforeach
                </div>
                @endforeach
                @if($monthlyBreakdown->groupBy('month')->count() === 0)
                <div class="empty-state py-5">
                    <i class="bi bi-cash-coin"></i>
                    <p>No records yet.</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- All records -->
    <div class="col-lg-7">
        <div class="table-card">
            <div class="p-4 border-bottom" style="border-color:var(--border-light)!important;">
                <h5 class="card-title mb-0">All Records ({{ $records->total() }})</h5>
            </div>
            @if($records->count() > 0)
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr><th>Date</th><th>Type</th><th>Amount</th><th>Method</th><th>Notes</th><th></th></tr>
                    </thead>
                    <tbody>
                        @foreach($records as $record)
                        <tr>
                            <td style="font-size:13px;">{{ $record->record_date->format('d M Y') }}</td>
                            <td><span class="badge {{ $record->type_badge_class }}">{{ $record->type_label }}</span></td>
                            <td style="font-weight:700;color:{{ in_array($record->type, ['salary','bonus']) ? 'var(--success)' : 'var(--danger)' }};">
                                {{ in_array($record->type, ['salary','bonus']) ? '+' : '-' }}₹{{ number_format($record->amount, 2) }}
                            </td>
                            <td style="font-size:12px;color:var(--text-2);">{{ ucwords(str_replace('_', ' ', $record->payment_method)) }}</td>
                            <td style="font-size:12px;color:var(--text-3);max-width:140px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                                {{ $record->notes ?? '—' }}
                            </td>
                            <td>
                                <a href="{{ route('salary.show', $record) }}" class="btn btn-xs btn-outline-primary">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-3">{{ $records->links() }}</div>
            @else
            <div class="empty-state">
                <i class="bi bi-cash-coin"></i>
                <h5>No Records</h5>
                <p>No salary records found for this employee.</p>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

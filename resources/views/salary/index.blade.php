@extends('layouts.app')
@section('title', 'Salary')

@section('breadcrumb')
    <li class="breadcrumb-item active">Salary</li>
@endsection

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Salary Records</h1>
        <p class="page-subtitle">All payment records — salary, advances, bonuses, deductions</p>
    </div>
    @can('manage', App\Models\SalaryRecord::class)
    <a href="{{ route('salary.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle me-2"></i>Add Record
    </a>
    @endcan
</div>

<!-- Stats -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon stat-icon-success"><i class="bi bi-cash-stack"></i></div>
            <div>
                <div class="stat-value" data-rupee>{{ $stats['total_salary_this_month'] }}</div>
                <div class="stat-label">Salary This Month</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon stat-icon-warning"><i class="bi bi-wallet2"></i></div>
            <div>
                <div class="stat-value" data-rupee>{{ $stats['total_advances_this_month'] }}</div>
                <div class="stat-label">Advances This Month</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon stat-icon-info"><i class="bi bi-gift"></i></div>
            <div>
                <div class="stat-value" data-rupee>{{ $stats['total_bonuses_this_month'] }}</div>
                <div class="stat-label">Bonuses This Month</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon stat-icon-primary"><i class="bi bi-people-fill"></i></div>
            <div>
                <div class="stat-value" data-rupee>{{ $stats['total_salary_this_month'] + $stats['total_bonuses_this_month'] }}</div>
                <div class="stat-label">Total Payout</div>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body py-3">
        <form method="GET" class="row g-2 align-items-end">
            @if(auth()->user()->isAdminOrManager())
            <div class="col-md-3">
                <select name="employee_id" class="form-select">
                    <option value="">All Employees</option>
                    @foreach($employees as $emp)
                    <option value="{{ $emp->id }}" {{ request('employee_id') == $emp->id ? 'selected' : '' }}>{{ $emp->name }}</option>
                    @endforeach
                </select>
            </div>
            @endif
            <div class="col-md-2">
                <select name="type" class="form-select">
                    <option value="">All Types</option>
                    <option value="salary" {{ request('type') === 'salary' ? 'selected' : '' }}>Salary</option>
                    <option value="advance" {{ request('type') === 'advance' ? 'selected' : '' }}>Advance</option>
                    <option value="bonus" {{ request('type') === 'bonus' ? 'selected' : '' }}>Bonus</option>
                    <option value="deduction" {{ request('type') === 'deduction' ? 'selected' : '' }}>Deduction</option>
                </select>
            </div>
            <div class="col-md-2">
                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}" />
            </div>
            <div class="col-md-2">
                <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}" />
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search me-1"></i>Filter</button>
            </div>
        </form>
    </div>
</div>

<!-- Records Table -->
<div class="table-card">
    @if($records->count() > 0)
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    @if(auth()->user()->isAdminOrManager())<th>Employee</th>@endif
                    <th>Type</th>
                    <th>Amount</th>
                    <th>Date</th>
                    <th>Payment Method</th>
                    <th>Reference</th>
                    <th>Notes</th>
                    @if(auth()->user()->isAdminOrManager())<th>Processed By</th>@endif
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($records as $record)
                <tr>
                    @if(auth()->user()->isAdminOrManager())
                    <td>
                        <a href="{{ route('salary.employee-summary', $record->employee) }}" style="color:var(--primary);text-decoration:none;font-weight:600;font-size:13px;">
                            {{ $record->employee->name ?? '—' }}
                        </a>
                        <div style="font-size:11px;color:var(--text-3);">{{ $record->employee->employee_id ?? '' }}</div>
                    </td>
                    @endif
                    <td>
                        <span class="badge {{ $record->type_badge_class }}">{{ $record->type_label }}</span>
                    </td>
                    <td style="font-weight:700;color:{{ in_array($record->type, ['salary','bonus']) ? 'var(--success)' : 'var(--danger)' }};font-size:14px;">
                        {{ in_array($record->type, ['salary','bonus']) ? '+' : '-' }}₹{{ number_format($record->amount, 2) }}
                    </td>
                    <td style="font-size:13px;">{{ $record->record_date->format('d M Y') }}</td>
                    <td style="font-size:12px;color:var(--text-2);">{{ ucwords(str_replace('_', ' ', $record->payment_method)) }}</td>
                    <td style="font-size:12px;color:var(--text-3);">{{ $record->reference_number ?? '—' }}</td>
                    <td style="font-size:12px;color:var(--text-3);max-width:160px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                        {{ $record->notes ?? '—' }}
                    </td>
                    @if(auth()->user()->isAdminOrManager())
                    <td style="font-size:12px;color:var(--text-2);">{{ $record->processor->name ?? '—' }}</td>
                    @endif
                    <td>
                        <div class="d-flex gap-1">
                            <a href="{{ route('salary.show', $record) }}" class="btn btn-xs btn-outline-primary" title="View">
                                <i class="bi bi-eye"></i>
                            </a>
                            @can('admin', App\Models\SalaryRecord::class)
                            <form action="{{ route('salary.destroy', $record) }}" method="POST"
                                onsubmit="return confirm('Delete this record permanently?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-xs btn-outline-danger" title="Delete">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                            @endcan
                        </div>
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
        <h5>No Salary Records</h5>
        <p>{{ request()->hasAny(['employee_id','type','date_from']) ? 'No records match your filters.' : 'Add your first salary record to get started.' }}</p>
        @can('manage', App\Models\SalaryRecord::class)
        <a href="{{ route('salary.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus me-1"></i>Add Record
        </a>
        @endcan
    </div>
    @endif
</div>
@endsection

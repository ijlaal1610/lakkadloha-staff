@extends('layouts.app')
@section('title', $staff->name)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('staff.index') }}" style="color:var(--text-2);text-decoration:none;">Staff</a></li>
    <li class="breadcrumb-item active">{{ $staff->name }}</li>
@endsection

@section('content')
<div class="page-header">
    <div class="d-flex align-items-center gap-3">
        <img src="{{ $staff->avatar_url }}" alt="{{ $staff->name }}" style="width:64px;height:64px;border-radius:50%;object-fit:cover;border:3px solid var(--primary);" />
        <div>
            <h1 class="page-title">{{ $staff->name }}</h1>
            <div class="d-flex align-items-center gap-2">
                <span class="badge {{ $staff->role === 'super_admin' ? 'bg-danger' : ($staff->role === 'manager' ? 'bg-primary' : 'bg-secondary') }}">{{ $staff->getRoleLabel() }}</span>
                <span class="status-badge {{ $staff->is_active ? 'status-present' : 'status-absent' }}">{{ $staff->is_active ? 'Active' : 'Inactive' }}</span>
                @if($staff->employee_id)<span style="font-size:12px;font-family:monospace;color:var(--text-3);">{{ $staff->employee_id }}</span>@endif
            </div>
        </div>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('salary.employee-summary', $staff) }}" class="btn btn-outline-success">
            <i class="bi bi-cash-stack me-2"></i>Salary History
        </a>
        @if(auth()->user()->isSuperAdmin())
        <a href="{{ route('staff.edit', $staff) }}" class="btn btn-outline-primary">
            <i class="bi bi-pencil me-2"></i>Edit
        </a>
        <form action="{{ route('staff.toggle-status', $staff) }}" method="POST">
            @csrf
            <button type="submit" class="btn {{ $staff->is_active ? 'btn-outline-warning' : 'btn-outline-success' }}">
                <i class="bi {{ $staff->is_active ? 'bi-pause-fill' : 'bi-play-fill' }} me-2"></i>
                {{ $staff->is_active ? 'Deactivate' : 'Activate' }}
            </button>
        </form>
        @endif
        <a href="{{ route('staff.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Back
        </a>
    </div>
</div>

<!-- Stats -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon stat-icon-success"><i class="bi bi-currency-rupee"></i></div>
            <div><div class="stat-value" data-rupee>{{ $stats['total_sales'] }}</div><div class="stat-label">Total Sales</div></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon stat-icon-primary"><i class="bi bi-graph-up"></i></div>
            <div><div class="stat-value" data-rupee>{{ $stats['sales_this_month'] }}</div><div class="stat-label">This Month</div></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon stat-icon-info"><i class="bi bi-cash-stack"></i></div>
            <div><div class="stat-value" data-rupee>{{ $stats['total_salary_paid'] }}</div><div class="stat-label">Total Salary Paid</div></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon stat-icon-warning"><i class="bi bi-calendar-check"></i></div>
            <div><div class="stat-value">{{ $stats['present_days_month'] }}</div><div class="stat-label">Days Present (Month)</div></div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header"><h5 class="card-title">Personal Info</h5></div>
            <div class="card-body p-0">
                <div class="info-row px-4"><span class="info-label">Email</span><span class="info-value" style="font-size:12px;">{{ $staff->email }}</span></div>
                <div class="info-row px-4"><span class="info-label">Phone</span><span class="info-value">{{ $staff->phone ?? '—' }}</span></div>
                <div class="info-row px-4"><span class="info-label">Designation</span><span class="info-value">{{ $staff->designation ?? '—' }}</span></div>
                <div class="info-row px-4"><span class="info-label">Joined</span><span class="info-value">{{ $staff->joining_date ? $staff->joining_date->format('d M Y') : '—' }}</span></div>
                <div class="info-row px-4"><span class="info-label">Address</span><span class="info-value" style="font-size:12px;">{{ $staff->address ?? '—' }}</span></div>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <!-- Recent Sales -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title">Recent Sales</h5>
                <a href="{{ route('sales.index', ['staff_id' => $staff->id]) }}" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            @if($recentSales->count() > 0)
            <div class="table-responsive">
                <table class="table">
                    <thead><tr><th>Sale #</th><th>Product</th><th>Total</th><th>Date</th></tr></thead>
                    <tbody>
                        @foreach($recentSales as $sale)
                        <tr>
                            <td><a href="{{ route('sales.show', $sale) }}" style="color:var(--primary);font-weight:600;text-decoration:none;font-size:12px;">{{ $sale->sale_number }}</a></td>
                            <td style="font-size:13px;">{{ $sale->product->name ?? '—' }}</td>
                            <td style="font-weight:700;color:var(--success);">₹{{ number_format($sale->total_amount, 2) }}</td>
                            <td style="font-size:12px;color:var(--text-3);">{{ $sale->sold_at->format('d M Y') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="empty-state py-5">
                <i class="bi bi-cart-x"></i>
                <p>No sales recorded yet.</p>
            </div>
            @endif
        </div>

        <!-- Recent Attendance -->
        <div class="card">
            <div class="card-header"><h5 class="card-title">Recent Attendance</h5></div>
            @if($recentAttendance->count() > 0)
            <div class="table-responsive">
                <table class="table">
                    <thead><tr><th>Date</th><th>Status</th><th>Time In</th><th>Time Out</th></tr></thead>
                    <tbody>
                        @foreach($recentAttendance->take(10) as $att)
                        <tr>
                            <td>{{ $att->date->format('D, d M Y') }}</td>
                            <td><span class="status-badge status-{{ $att->status }}">{{ ucwords(str_replace('_',' ',$att->status)) }}</span></td>
                            <td>{{ $att->time_in ? \Carbon\Carbon::parse($att->time_in)->format('h:i A') : '—' }}</td>
                            <td>{{ $att->time_out ? \Carbon\Carbon::parse($att->time_out)->format('h:i A') : '—' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="empty-state py-5">
                <i class="bi bi-calendar-x"></i>
                <p>No attendance records.</p>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

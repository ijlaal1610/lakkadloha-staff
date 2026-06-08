@extends('layouts.app')
@section('title', 'Sales')

@section('breadcrumb')
    <li class="breadcrumb-item active">Sales</li>
@endsection

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Sales</h1>
        <p class="page-subtitle">Track and manage all transactions</p>
    </div>
    <a href="{{ route('sales.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle me-2"></i>New Sale
    </a>
</div>

<!-- Stats -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon stat-icon-primary"><i class="bi bi-currency-rupee"></i></div>
            <div>
                <div class="stat-value" data-rupee>{{ $stats['today'] }}</div>
                <div class="stat-label">Today's Revenue</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon stat-icon-success"><i class="bi bi-bag-check-fill"></i></div>
            <div>
                <div class="stat-value">{{ $stats['today_count'] }}</div>
                <div class="stat-label">Today's Orders</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon stat-icon-info"><i class="bi bi-graph-up"></i></div>
            <div>
                <div class="stat-value" data-rupee>{{ $stats['this_month'] }}</div>
                <div class="stat-label">This Month</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon stat-icon-warning"><i class="bi bi-receipt"></i></div>
            <div>
                <div class="stat-value">{{ $stats['this_month_count'] }}</div>
                <div class="stat-label">Month Orders</div>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body py-3">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <input type="text" name="search" class="form-control" placeholder="Search sale # or product…" value="{{ request('search') }}" />
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    <option value="refunded" {{ request('status') === 'refunded' ? 'selected' : '' }}>Refunded</option>
                </select>
            </div>
            <div class="col-md-2">
                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}" placeholder="From date" />
            </div>
            <div class="col-md-2">
                <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}" />
            </div>
            @if(auth()->user()->isAdminOrManager() && $staffList->count() > 0)
            <div class="col-md-2">
                <select name="staff_id" class="form-select">
                    <option value="">All Staff</option>
                    @foreach($staffList as $s)
                    <option value="{{ $s->id }}" {{ request('staff_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>
            @endif
            <div class="col-md-1">
                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search"></i></button>
            </div>
        </form>
    </div>
</div>

<!-- Sales Table -->
<div class="table-card">
    @if($sales->count() > 0)
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Sale #</th>
                    <th>Product</th>
                    @if(auth()->user()->isAdminOrManager())<th>Staff</th>@endif
                    <th>Customer</th>
                    <th>Qty</th>
                    <th>Price</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sales as $sale)
                <tr>
                    <td>
                        <a href="{{ route('sales.show', $sale) }}" style="color:var(--primary);font-weight:700;text-decoration:none;font-size:12px;">
                            {{ $sale->sale_number }}
                        </a>
                    </td>
                    <td style="max-width:160px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;font-weight:500;">
                        {{ $sale->product->name ?? '—' }}
                    </td>
                    @if(auth()->user()->isAdminOrManager())
                    <td style="font-size:12px;color:var(--text-2);">{{ $sale->staff->name ?? '—' }}</td>
                    @endif
                    <td style="font-size:12px;color:var(--text-2);">{{ $sale->customer_name ?? '—' }}</td>
                    <td>{{ $sale->quantity }}</td>
                    <td>₹{{ number_format($sale->selling_price, 2) }}</td>
                    <td style="font-weight:700;color:var(--success);">₹{{ number_format($sale->total_amount, 2) }}</td>
                    <td><span class="status-badge status-{{ $sale->status }}">{{ ucfirst($sale->status) }}</span></td>
                    <td style="font-size:12px;color:var(--text-3);white-space:nowrap;">{{ $sale->sold_at->format('d M, h:i A') }}</td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="{{ route('sales.show', $sale) }}" class="btn btn-xs btn-outline-primary" title="View">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="{{ route('sales.receipt', $sale) }}" class="btn btn-xs btn-outline-secondary" title="Receipt">
                                <i class="bi bi-receipt"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="p-3">{{ $sales->links() }}</div>
    @else
    <div class="empty-state">
        <i class="bi bi-cart-x"></i>
        <h5>No Sales Found</h5>
        <p>{{ request()->hasAny(['search','status','date_from','staff_id']) ? 'No sales match your filters.' : 'Create your first sale to get started.' }}</p>
        <a href="{{ route('sales.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-2"></i>Create Sale
        </a>
    </div>
    @endif
</div>
@endsection

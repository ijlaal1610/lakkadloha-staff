@extends('layouts.app')
@section('title', 'Sales Report')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('reports.index') }}" style="color:var(--text-2);text-decoration:none;">Reports</a></li>
    <li class="breadcrumb-item active">Sales</li>
@endsection

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Sales Report</h1>
        <p class="page-subtitle">{{ $from->format('d M Y') }} — {{ $to->format('d M Y') }}</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('reports.sales.pdf', request()->all()) }}" class="btn btn-outline-danger" target="_blank">
            <i class="bi bi-file-pdf me-2"></i>PDF
        </a>
        <a href="{{ route('reports.sales.csv', request()->all()) }}" class="btn btn-outline-success">
            <i class="bi bi-filetype-csv me-2"></i>CSV
        </a>
    </div>
</div>

<!-- Date Range Filter -->
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
                <label class="form-label">Staff</label>
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
            <div class="col-md-2">
                <div class="btn-group w-100">
                    <a href="{{ route('reports.sales', ['from' => today()->format('Y-m-d'), 'to' => today()->format('Y-m-d')]) }}" class="btn btn-outline-secondary btn-sm">Today</a>
                    <a href="{{ route('reports.sales', ['from' => now()->startOfMonth()->format('Y-m-d'), 'to' => now()->endOfMonth()->format('Y-m-d')]) }}" class="btn btn-outline-secondary btn-sm">Month</a>
                    <a href="{{ route('reports.sales', ['from' => now()->startOfYear()->format('Y-m-d'), 'to' => now()->endOfYear()->format('Y-m-d')]) }}" class="btn btn-outline-secondary btn-sm">Year</a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Summary Stats -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon stat-icon-success"><i class="bi bi-currency-rupee"></i></div>
            <div><div class="stat-value" data-rupee>{{ $summary['total_revenue'] }}</div><div class="stat-label">Total Revenue</div></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon stat-icon-primary"><i class="bi bi-bag-check"></i></div>
            <div><div class="stat-value">{{ $summary['total_orders'] }}</div><div class="stat-label">Completed Orders</div></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon stat-icon-info"><i class="bi bi-calculator"></i></div>
            <div><div class="stat-value" data-rupee>{{ round($summary['avg_order_value'], 2) }}</div><div class="stat-label">Avg. Order Value</div></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon stat-icon-warning"><i class="bi bi-x-circle"></i></div>
            <div><div class="stat-value">{{ $summary['cancelled'] + $summary['refunded'] }}</div><div class="stat-label">Cancelled/Refunded</div></div>
        </div>
    </div>
</div>

<!-- Revenue Chart -->
@if($daily->count() > 0)
<div class="card mb-4">
    <div class="card-header"><h5 class="card-title"><i class="bi bi-graph-up me-2 text-primary"></i>Daily Revenue Trend</h5></div>
    <div class="card-body">
        <div class="chart-container">
            <canvas id="revenueChart" height="240"></canvas>
        </div>
    </div>
</div>
@endif

<div class="row g-4 mb-4">
    <!-- Staff Performance -->
    @if($staffPerformance->count() > 0)
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header"><h5 class="card-title"><i class="bi bi-people me-2 text-success"></i>Staff Performance</h5></div>
            <div class="table-responsive">
                <table class="table">
                    <thead><tr><th>Staff</th><th>Orders</th><th>Revenue</th></tr></thead>
                    <tbody>
                        @foreach($staffPerformance as $sp)
                        <tr>
                            <td style="font-weight:600;">{{ $sp['staff']->name ?? '—' }}</td>
                            <td>{{ $sp['count'] }}</td>
                            <td style="font-weight:700;color:var(--success);">₹{{ number_format($sp['revenue'], 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    <!-- Top Products -->
    @if($topProducts->count() > 0)
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header"><h5 class="card-title"><i class="bi bi-trophy me-2 text-warning"></i>Top Products</h5></div>
            <div class="table-responsive">
                <table class="table">
                    <thead><tr><th>Product</th><th>Units</th><th>Revenue</th></tr></thead>
                    <tbody>
                        @foreach($topProducts as $tp)
                        <tr>
                            <td style="font-weight:600;">{{ $tp['product']->name ?? 'Deleted' }}</td>
                            <td>{{ $tp['quantity'] }}</td>
                            <td style="font-weight:700;color:var(--success);">₹{{ number_format($tp['revenue'], 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif
</div>

<!-- Full Sales Table -->
<div class="table-card">
    <div class="p-4 border-bottom" style="border-color:var(--border-light)!important;">
        <h5 class="card-title mb-0">All Sales ({{ $sales->count() }})</h5>
    </div>
    @if($sales->count() > 0)
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr><th>Sale #</th><th>Date</th><th>Product</th><th>Staff</th><th>Qty</th><th>Price</th><th>Total</th><th>Status</th></tr>
            </thead>
            <tbody>
                @foreach($sales as $sale)
                <tr>
                    <td><a href="{{ route('sales.show', $sale) }}" style="color:var(--primary);font-weight:600;text-decoration:none;font-size:12px;">{{ $sale->sale_number }}</a></td>
                    <td style="font-size:12px;">{{ $sale->sold_at->format('d M Y H:i') }}</td>
                    <td style="font-weight:500;max-width:160px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $sale->product->name ?? '—' }}</td>
                    <td style="font-size:12px;color:var(--text-2);">{{ $sale->staff->name ?? '—' }}</td>
                    <td>{{ $sale->quantity }}</td>
                    <td>₹{{ number_format($sale->selling_price, 2) }}</td>
                    <td style="font-weight:700;color:var(--success);">₹{{ number_format($sale->total_amount, 2) }}</td>
                    <td><span class="status-badge status-{{ $sale->status }}">{{ ucfirst($sale->status) }}</span></td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr style="background:var(--bg-secondary);">
                    <td colspan="6" style="font-weight:700;color:var(--text);text-align:right;padding:12px 16px;">Total Revenue:</td>
                    <td style="font-weight:800;font-size:15px;color:var(--success);padding:12px 16px;">₹{{ number_format($summary['total_revenue'], 2) }}</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>
    @else
    <div class="empty-state">
        <i class="bi bi-cart-x"></i>
        <h5>No Sales</h5>
        <p>No completed sales found for the selected period.</p>
    </div>
    @endif
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const daily = @json($daily);
    const labels = Object.keys(daily).map(d => {
        const dt = new Date(d);
        return dt.toLocaleDateString('en-IN', { day: 'numeric', month: 'short' });
    });
    const data = Object.values(daily).map(d => d.revenue);

    const ctx = document.getElementById('revenueChart');
    if (!ctx || !labels.length) return;

    new Chart(ctx, {
        type: 'line',
        data: {
            labels,
            datasets: [{
                label: 'Revenue (₹)',
                data,
                borderColor: '#4f46e5',
                backgroundColor: 'rgba(79,70,229,0.08)',
                borderWidth: 2.5,
                pointBackgroundColor: '#4f46e5',
                pointRadius: 4,
                tension: 0.4,
                fill: true,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: { legend: { display: false } },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { callback: v => '₹' + new Intl.NumberFormat('en-IN', { notation: 'compact' }).format(v) }
                }
            }
        }
    });
});
</script>
@endpush
@endsection

@extends('layouts.app')
@section('title', 'Dashboard')

@section('breadcrumb')
    <li class="breadcrumb-item active">Dashboard</li>
@endsection

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">
            @php
                $hour = now()->hour;
                $greeting = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
            @endphp
            {{ $greeting }}, {{ explode(' ', auth()->user()->name)[0] }} 👋
        </h1>
        <p class="page-subtitle">{{ now()->format('l, d F Y') }} • Here's what's happening</p>
    </div>
    @if(auth()->user()->isStaff())
    <div class="d-flex gap-2 flex-wrap">
        @if(!isset($myAttendanceToday) || !$myAttendanceToday)
            <form action="{{ route('attendance.check-in') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-success">
                    <i class="bi bi-box-arrow-in-right me-2"></i>Check In
                </button>
            </form>
        @elseif($myAttendanceToday && !$myAttendanceToday->time_out)
            <form action="{{ route('attendance.check-out') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-outline-primary">
                    <i class="bi bi-box-arrow-right me-2"></i>Check Out
                </button>
            </form>
        @else
            <span class="btn btn-sm" style="background:var(--success-bg);color:var(--success);border:none;cursor:default;">
                <i class="bi bi-check-circle me-1"></i>Checked Out
            </span>
        @endif
        <a href="{{ route('sales.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-2"></i>New Sale
        </a>
    </div>
    @endif
</div>

<!-- Stat Cards -->
<div class="row g-3 mb-4">
    @if(auth()->user()->isStaff())
        <div class="col-6 col-lg-3">
            <div class="stat-card">
                <div class="stat-icon stat-icon-primary"><i class="bi bi-cart-fill"></i></div>
                <div>
                    <div class="stat-value" data-rupee>{{ $myTodaySales ?? 0 }}</div>
                    <div class="stat-label">My Sales Today</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card">
                <div class="stat-icon stat-icon-success"><i class="bi bi-graph-up"></i></div>
                <div>
                    <div class="stat-value" data-rupee>{{ $myMonthlySales ?? 0 }}</div>
                    <div class="stat-label">My This Month</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card">
                <div class="stat-icon stat-icon-info"><i class="bi bi-cash-stack"></i></div>
                <div>
                    <div class="stat-value" data-rupee>{{ $mySalaryThisMonth ?? 0 }}</div>
                    <div class="stat-label">Salary This Month</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card">
                <div class="stat-icon stat-icon-warning"><i class="bi bi-calendar-check-fill"></i></div>
                <div>
                    <div class="stat-value">
                        @if(isset($myAttendanceToday) && $myAttendanceToday)
                            <span class="status-badge status-{{ $myAttendanceToday->status }}">{{ ucfirst($myAttendanceToday->status) }}</span>
                        @else
                            <span class="status-badge status-absent">Not Marked</span>
                        @endif
                    </div>
                    <div class="stat-label">Today's Attendance</div>
                </div>
            </div>
        </div>
    @else
        <div class="col-6 col-lg-3">
            <div class="stat-card">
                <div class="stat-icon stat-icon-primary"><i class="bi bi-currency-rupee"></i></div>
                <div>
                    <div class="stat-value" data-rupee>{{ $todaySales }}</div>
                    <div class="stat-label">Today's Revenue</div>
                    <div class="stat-trend stat-trend-up"><i class="bi bi-bag-check me-1"></i>{{ $todaySalesCount }} orders</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card">
                <div class="stat-icon stat-icon-success"><i class="bi bi-graph-up-arrow"></i></div>
                <div>
                    <div class="stat-value" data-rupee>{{ $monthlySales }}</div>
                    <div class="stat-label">This Month</div>
                    <div class="stat-trend stat-trend-up"><i class="bi bi-bag me-1"></i>{{ $monthlySalesCount }} orders</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card">
                <div class="stat-icon stat-icon-info"><i class="bi bi-people-fill"></i></div>
                <div>
                    <div class="stat-value">{{ $presentToday }}/{{ $totalStaff }}</div>
                    <div class="stat-label">Staff Present Today</div>
                    @if($absentToday > 0)
                    <div class="stat-trend stat-trend-down"><i class="bi bi-person-x me-1"></i>{{ $absentToday }} absent</div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card">
                <div class="stat-icon stat-icon-warning"><i class="bi bi-box-seam-fill"></i></div>
                <div>
                    <div class="stat-value">{{ $lowStockProducts->count() }}</div>
                    <div class="stat-label">Low Stock Items</div>
                    @if($lowStockProducts->count() > 0)
                    <div class="stat-trend stat-trend-down"><i class="bi bi-exclamation-triangle me-1"></i>Needs attention</div>
                    @else
                    <div class="stat-trend stat-trend-up"><i class="bi bi-check-circle me-1"></i>All good</div>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>

<div class="row g-4 mb-4">
    <!-- Sales Chart -->
    <div class="col-lg-8">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title"><i class="bi bi-bar-chart-fill me-2 text-primary"></i>Revenue — Last 7 Days</h5>
                <a href="{{ route('reports.sales') }}" class="btn btn-sm btn-outline-primary">Full Report</a>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="salesChart" height="260"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title"><i class="bi bi-lightning-fill me-2 text-warning"></i>Quick Overview</h5>
            </div>
            <div class="card-body p-0">
                <div class="p-4 border-bottom" style="border-color:var(--border-light)!important;">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span style="font-size:13px;color:var(--text-2);">Weekly Revenue</span>
                        <span style="font-size:14px;font-weight:700;color:var(--text);">₹{{ number_format($weeklySales, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span style="font-size:13px;color:var(--text-2);">Monthly Revenue</span>
                        <span style="font-size:14px;font-weight:700;color:var(--success);">₹{{ number_format($monthlySales, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span style="font-size:13px;color:var(--text-2);">Yearly Revenue</span>
                        <span style="font-size:14px;font-weight:700;color:var(--primary);">₹{{ number_format($yearlySales, 2) }}</span>
                    </div>
                </div>

                @if($lowStockProducts->count() > 0)
                <div class="p-3">
                    <div style="font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--warning);margin-bottom:10px;">
                        <i class="bi bi-exclamation-triangle me-1"></i>Low Stock Alerts
                    </div>
                    @foreach($lowStockProducts->take(4) as $product)
                    <div class="d-flex align-items-center justify-content-between py-2" style="border-bottom:1px solid var(--border-light);">
                        <a href="{{ route('inventory.show', $product) }}" style="font-size:13px;color:var(--text);text-decoration:none;font-weight:500;">
                            {{ Str::limit($product->name, 22) }}
                        </a>
                        <span class="status-badge status-{{ $product->stock_status }}">{{ $product->current_stock }}</span>
                    </div>
                    @endforeach
                    @if($lowStockProducts->count() > 4)
                    <a href="{{ route('inventory.index', ['stock_status' => 'low']) }}" style="font-size:12px;color:var(--primary);text-decoration:none;display:block;margin-top:8px;">
                        +{{ $lowStockProducts->count() - 4 }} more →
                    </a>
                    @endif
                </div>
                @else
                <div class="text-center p-4" style="color:var(--text-3);">
                    <i class="bi bi-check-circle-fill text-success" style="font-size:28px;"></i>
                    <p class="mt-2 mb-0" style="font-size:13px;">All products well stocked</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Top Products & Recent Sales -->
<div class="row g-4">
    @if(auth()->user()->isAdminOrManager() && $topProducts->count() > 0)
    <div class="col-lg-5">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title"><i class="bi bi-trophy-fill me-2" style="color:var(--warning);"></i>Top Products This Month</h5>
            </div>
            <div class="card-body p-0">
                @foreach($topProducts as $i => $tp)
                <div class="d-flex align-items-center gap-3 p-3" style="border-bottom:1px solid var(--border-light);">
                    <div style="width:28px;height:28px;border-radius:8px;background:var(--primary-bg);display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;color:var(--primary);flex-shrink:0;">
                        {{ $i + 1 }}
                    </div>
                    <div class="flex-grow-1" style="min-width:0;">
                        <div style="font-size:13px;font-weight:600;color:var(--text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                            {{ $tp->product->name ?? 'Deleted Product' }}
                        </div>
                        <div style="font-size:12px;color:var(--text-3);">{{ $tp->qty }} units sold</div>
                    </div>
                    <div style="font-size:13px;font-weight:700;color:var(--success);flex-shrink:0;">
                        ₹{{ number_format($tp->total, 0) }}
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <div class="{{ auth()->user()->isAdminOrManager() && $topProducts->count() > 0 ? 'col-lg-7' : 'col-12' }}">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title"><i class="bi bi-clock-history me-2 text-primary"></i>Recent Sales</h5>
                <a href="{{ route('sales.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            @if($recentSales->count() > 0)
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Sale #</th>
                            <th>Product</th>
                            @if(auth()->user()->isAdminOrManager())<th>Staff</th>@endif
                            <th>Qty</th>
                            <th>Total</th>
                            <th>Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentSales as $sale)
                        <tr>
                            <td>
                                <a href="{{ route('sales.show', $sale) }}" style="color:var(--primary);font-weight:600;text-decoration:none;font-size:12px;">
                                    {{ $sale->sale_number }}
                                </a>
                            </td>
                            <td style="max-width:160px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                {{ $sale->product->name ?? '—' }}
                            </td>
                            @if(auth()->user()->isAdminOrManager())
                            <td style="font-size:12px;color:var(--text-2);">{{ $sale->staff->name ?? '—' }}</td>
                            @endif
                            <td>{{ $sale->quantity }}</td>
                            <td style="font-weight:600;color:var(--success);">₹{{ number_format($sale->total_amount, 2) }}</td>
                            <td style="font-size:12px;color:var(--text-3);">{{ $sale->sold_at->diffForHumans() }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="empty-state">
                <i class="bi bi-cart-x"></i>
                <h5>No Sales Yet</h5>
                <p>Start by creating your first sale.</p>
                <a href="{{ route('sales.create') }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus me-1"></i>New Sale
                </a>
            </div>
            @endif
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const labels = @json($chartLabels);
    const data   = @json($chartData);
    const isDark = document.documentElement.getAttribute('data-theme') === 'dark';

    const ctx = document.getElementById('salesChart');
    if (!ctx) return;

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels,
            datasets: [{
                label: 'Revenue (₹)',
                data,
                backgroundColor: 'rgba(79, 70, 229, 0.15)',
                borderColor: '#4f46e5',
                borderWidth: 2,
                borderRadius: 6,
                borderSkipped: false,
                hoverBackgroundColor: 'rgba(79, 70, 229, 0.3)',
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: ctx => '₹' + new Intl.NumberFormat('en-IN').format(ctx.parsed.y)
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: v => '₹' + new Intl.NumberFormat('en-IN', { notation: 'compact' }).format(v)
                    }
                }
            }
        }
    });
});
</script>
@endpush

@extends('layouts.app')
@section('title', 'Inventory Report')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('reports.index') }}" style="color:var(--text-2);text-decoration:none;">Reports</a></li>
    <li class="breadcrumb-item active">Inventory</li>
@endsection
@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Inventory Report</h1>
        <p class="page-subtitle">Current stock levels and movement summary</p>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="stat-card"><div class="stat-icon stat-icon-primary"><i class="bi bi-box-seam-fill"></i></div><div><div class="stat-value">{{ $stats['total_products'] }}</div><div class="stat-label">Total Products</div></div></div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card"><div class="stat-icon stat-icon-warning"><i class="bi bi-exclamation-triangle-fill"></i></div><div><div class="stat-value">{{ $stats['low_stock'] }}</div><div class="stat-label">Low Stock</div></div></div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card"><div class="stat-icon stat-icon-danger"><i class="bi bi-x-circle-fill"></i></div><div><div class="stat-value">{{ $stats['out_of_stock'] }}</div><div class="stat-label">Out of Stock</div></div></div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card"><div class="stat-icon stat-icon-success"><i class="bi bi-layers-fill"></i></div><div><div class="stat-value">{{ number_format($stats['total_units']) }}</div><div class="stat-label">Total Units</div></div></div>
    </div>
</div>

<div class="table-card">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Current Stock</th>
                    <th>Threshold</th>
                    <th>Status</th>
                    <th>Total Sales</th>
                    <th>Units Sold</th>
                    <th>Created By</th>
                    <th>Created</th>
                </tr>
            </thead>
            <tbody>
                @foreach($products as $product)
                <tr>
                    <td>
                        <a href="{{ route('inventory.show', $product) }}" style="color:var(--primary);font-weight:600;text-decoration:none;">
                            {{ $product->name }}
                        </a>
                    </td>
                    <td style="font-weight:700;font-size:15px;color:{{ $product->isOutOfStock() ? 'var(--danger)' : ($product->isLowStock() ? 'var(--warning)' : 'var(--success)') }};">
                        {{ number_format($product->current_stock) }}
                    </td>
                    <td style="color:var(--text-3);">{{ $product->low_stock_threshold }}</td>
                    <td><span class="status-badge status-{{ $product->stock_status }}">{{ $product->stock_status_label }}</span></td>
                    <td>{{ $product->sales_count }} orders</td>
                    <td>{{ number_format($product->sales_sum_quantity ?? 0) }} units</td>
                    <td style="font-size:12px;color:var(--text-2);">{{ $product->creator->name ?? '—' }}</td>
                    <td style="font-size:12px;color:var(--text-3);">{{ $product->created_at->format('d M Y') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection

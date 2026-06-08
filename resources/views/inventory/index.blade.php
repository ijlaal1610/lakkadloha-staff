@extends('layouts.app')
@section('title', 'Inventory')

@section('breadcrumb')
    <li class="breadcrumb-item active">Inventory</li>
@endsection

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Inventory</h1>
        <p class="page-subtitle">Manage products and stock levels</p>
    </div>
    @can('manage', App\Models\Product::class)
    <a href="{{ route('inventory.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle me-2"></i>Add Product
    </a>
    @endcan
</div>

<!-- Stats -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon stat-icon-primary"><i class="bi bi-box-seam-fill"></i></div>
            <div>
                <div class="stat-value">{{ $stats['total'] }}</div>
                <div class="stat-label">Total Products</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon stat-icon-warning"><i class="bi bi-exclamation-triangle-fill"></i></div>
            <div>
                <div class="stat-value">{{ $stats['low_stock'] }}</div>
                <div class="stat-label">Low Stock</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon stat-icon-danger"><i class="bi bi-x-circle-fill"></i></div>
            <div>
                <div class="stat-value">{{ $stats['out_of_stock'] }}</div>
                <div class="stat-label">Out of Stock</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon stat-icon-success"><i class="bi bi-check-circle-fill"></i></div>
            <div>
                <div class="stat-value">{{ $stats['total'] - $stats['out_of_stock'] }}</div>
                <div class="stat-label">In Stock</div>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body py-3">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control" placeholder="Search products…"
                    value="{{ request('search') }}" />
            </div>
            <div class="col-md-2">
                <select name="stock_status" class="form-select">
                    <option value="">All Stock</option>
                    <option value="low" {{ request('stock_status') === 'low' ? 'selected' : '' }}>Low Stock</option>
                    <option value="out" {{ request('stock_status') === 'out' ? 'selected' : '' }}>Out of Stock</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-search me-1"></i>Filter
                </button>
            </div>
            @if(request()->hasAny(['search','stock_status','status']))
            <div class="col-md-2">
                <a href="{{ route('inventory.index') }}" class="btn btn-outline-secondary w-100">Clear</a>
            </div>
            @endif
        </form>
    </div>
</div>

<!-- Product Grid -->
@if($products->count() > 0)
<div class="row g-3 mb-4">
    @foreach($products as $product)
    <div class="col-sm-6 col-lg-4 col-xl-3">
        <div class="card h-100" style="transition: transform .2s, box-shadow .2s;">
            <!-- Product Photo -->
            <div style="height:140px;background:var(--bg-secondary);border-radius:12px 12px 0 0;overflow:hidden;display:flex;align-items:center;justify-content:center;">
                @if($product->photo)
                    <img src="{{ $product->photo_url }}" alt="{{ $product->name }}" style="width:100%;height:100%;object-fit:cover;" />
                @else
                    <i class="bi bi-box-seam" style="font-size:40px;color:var(--text-3);"></i>
                @endif
            </div>

            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <h6 style="font-size:14px;font-weight:700;color:var(--text);margin:0;line-height:1.3;">
                        {{ $product->name }}
                    </h6>
                    <span class="status-badge status-{{ $product->stock_status }}">
                        {{ $product->stock_status_label }}
                    </span>
                </div>

                <!-- Stock Bar -->
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span style="font-size:12px;color:var(--text-3);">Stock</span>
                        <span style="font-size:13px;font-weight:700;color:var(--text);">{{ number_format($product->current_stock) }}</span>
                    </div>
                    @php
                        $threshold = $product->low_stock_threshold;
                        $stock = $product->current_stock;
                        $maxDisplay = max($stock, $threshold * 3, 1);
                        $pct = min(100, ($stock / $maxDisplay) * 100);
                        $fillClass = $stock <= 0 ? 'low' : ($product->isLowStock() ? 'medium' : 'high');
                    @endphp
                    <div class="stock-bar">
                        <div class="stock-bar-fill {{ $fillClass }}" style="width:{{ $pct }}%;"></div>
                    </div>
                    <div style="font-size:11px;color:var(--text-3);margin-top:4px;">Threshold: {{ $threshold }}</div>
                </div>

                @if($product->notes)
                <p style="font-size:12px;color:var(--text-3);margin:0 0 12px;">{{ Str::limit($product->notes, 60) }}</p>
                @endif

                <div class="d-flex gap-2">
                    <a href="{{ route('inventory.show', $product) }}" class="btn btn-sm btn-outline-primary flex-grow-1">
                        <i class="bi bi-eye me-1"></i>View
                    </a>
                    @can('manage', App\Models\Product::class)
                    <button class="btn btn-sm btn-success" onclick="openAddStock({{ $product->id }}, '{{ addslashes($product->name) }}')" title="Add Stock">
                        <i class="bi bi-plus-lg"></i>
                    </button>
                    <a href="{{ route('inventory.edit', $product) }}" class="btn btn-sm btn-outline-secondary" title="Edit">
                        <i class="bi bi-pencil"></i>
                    </a>
                    @endcan
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

{{ $products->links() }}
@else
<div class="card">
    <div class="empty-state">
        <i class="bi bi-box-seam"></i>
        <h5>No Products Found</h5>
        <p>{{ request()->hasAny(['search','stock_status']) ? 'No products match your filters.' : 'Start by adding your first product.' }}</p>
        @can('manage', App\Models\Product::class)
        <a href="{{ route('inventory.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-2"></i>Add First Product
        </a>
        @endcan
    </div>
</div>
@endif

<!-- Add Stock Modal -->
@can('manage', App\Models\Product::class)
<div class="modal fade" id="addStockModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-plus-circle me-2 text-success"></i>Add Stock</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addStockForm" method="POST">
                @csrf
                <div class="modal-body">
                    <p id="addStockProductName" style="font-size:14px;font-weight:600;color:var(--text);margin-bottom:16px;"></p>
                    <div class="mb-3">
                        <label class="form-label">Quantity to Add</label>
                        <input type="number" name="quantity" class="form-control" min="1" required placeholder="e.g. 50" />
                    </div>
                    <div class="mb-0">
                        <label class="form-label">Notes (optional)</label>
                        <input type="text" name="notes" class="form-control" placeholder="Restock reason…" />
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success"><i class="bi bi-plus me-1"></i>Add Stock</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openAddStock(productId, productName) {
    document.getElementById('addStockProductName').textContent = productName;
    document.getElementById('addStockForm').action = '/inventory/' + productId + '/add-stock';
    new bootstrap.Modal(document.getElementById('addStockModal')).show();
}
</script>
@endcan

@endsection

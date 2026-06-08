@extends('layouts.app')
@section('title', $product->name)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('inventory.index') }}" style="color:var(--text-2);text-decoration:none;">Inventory</a></li>
    <li class="breadcrumb-item active">{{ Str::limit($product->name, 30) }}</li>
@endsection

@section('content')
<div class="page-header">
    <div class="d-flex align-items-center gap-3">
        <div style="width:56px;height:56px;border-radius:12px;overflow:hidden;background:var(--bg-secondary);flex-shrink:0;display:flex;align-items:center;justify-content:center;">
            @if($product->photo)
                <img src="{{ $product->photo_url }}" alt="{{ $product->name }}" style="width:100%;height:100%;object-fit:cover;" />
            @else
                <i class="bi bi-box-seam" style="font-size:24px;color:var(--text-3);"></i>
            @endif
        </div>
        <div>
            <h1 class="page-title">{{ $product->name }}</h1>
            <div class="d-flex align-items-center gap-2">
                <span class="status-badge status-{{ $product->stock_status }}">{{ $product->stock_status_label }}</span>
                @if(!$product->is_active)
                    <span class="badge bg-secondary">Inactive</span>
                @endif
                <span style="font-size:12px;color:var(--text-3);">Added by {{ $product->creator->name ?? '—' }}</span>
            </div>
        </div>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        @can('manage', App\Models\Product::class)
        <button class="btn btn-success" onclick="openAddStockModal()">
            <i class="bi bi-plus-lg me-2"></i>Add Stock
        </button>
        <button class="btn btn-outline-warning" onclick="openAdjustStockModal()">
            <i class="bi bi-sliders me-2"></i>Adjust
        </button>
        <a href="{{ route('inventory.edit', $product) }}" class="btn btn-outline-primary">
            <i class="bi bi-pencil me-2"></i>Edit
        </a>
        @endcan
        <a href="{{ route('inventory.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Back
        </a>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-4">
        <!-- Stock Card -->
        <div class="card mb-4">
            <div class="card-body text-center p-4">
                <div style="font-size:56px;font-weight:800;color:{{ $product->isOutOfStock() ? 'var(--danger)' : ($product->isLowStock() ? 'var(--warning)' : 'var(--success)') }};line-height:1;">
                    {{ number_format($product->current_stock) }}
                </div>
                <div style="font-size:13px;color:var(--text-3);margin-top:4px;">Units in stock</div>
                <div class="stock-bar mt-3">
                    @php
                        $max = max($product->current_stock, $product->low_stock_threshold * 3, 1);
                        $pct = min(100, ($product->current_stock / $max) * 100);
                        $cls = $product->current_stock <= 0 ? 'low' : ($product->isLowStock() ? 'medium' : 'high');
                    @endphp
                    <div class="stock-bar-fill {{ $cls }}" style="width:{{ $pct }}%;"></div>
                </div>
                <div class="d-flex justify-content-between mt-1">
                    <span style="font-size:11px;color:var(--text-3);">0</span>
                    <span style="font-size:11px;color:var(--text-3);">Alert at {{ $product->low_stock_threshold }}</span>
                </div>
            </div>
        </div>

        <!-- Product Info -->
        <div class="card">
            <div class="card-header"><h5 class="card-title">Details</h5></div>
            <div class="card-body p-0">
                <div class="info-row px-4"><span class="info-label">Created</span><span class="info-value">{{ $product->created_at->format('d M Y') }}</span></div>
                <div class="info-row px-4"><span class="info-label">Created By</span><span class="info-value">{{ $product->creator->name ?? '—' }}</span></div>
                <div class="info-row px-4"><span class="info-label">Total Sales</span><span class="info-value">{{ number_format($product->sales_count) }} orders</span></div>
                <div class="info-row px-4"><span class="info-label">Low Stock At</span><span class="info-value">{{ $product->low_stock_threshold }} units</span></div>
                <div class="info-row px-4"><span class="info-label">Status</span><span class="info-value">{{ $product->is_active ? 'Active' : 'Inactive' }}</span></div>
                @if($product->notes)
                <div class="info-row px-4"><span class="info-label">Notes</span><span class="info-value" style="font-size:12px;">{{ $product->notes }}</span></div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <!-- Stock Movements -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title"><i class="bi bi-arrow-left-right me-2"></i>Stock Movement History</h5>
            </div>
            @if($stockMovements->count() > 0)
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Qty</th>
                            <th>Before</th>
                            <th>After</th>
                            <th>By</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($stockMovements as $movement)
                        <tr>
                            <td style="font-size:12px;">{{ $movement->created_at->format('d M Y H:i') }}</td>
                            <td>
                                <span class="status-badge {{ in_array($movement->type, ['add','refund']) ? 'status-present' : (in_array($movement->type, ['deduct','sale']) ? 'status-cancelled' : 'status-late') }}">
                                    {{ ucfirst($movement->type) }}
                                </span>
                            </td>
                            <td style="font-weight:700;color:{{ in_array($movement->type, ['add','refund']) ? 'var(--success)' : 'var(--danger)' }};">
                                {{ in_array($movement->type, ['add','refund']) ? '+' : '-' }}{{ $movement->quantity }}
                            </td>
                            <td style="color:var(--text-3);">{{ $movement->stock_before }}</td>
                            <td style="font-weight:600;">{{ $movement->stock_after }}</td>
                            <td style="font-size:12px;color:var(--text-2);">{{ $movement->user->name ?? '—' }}</td>
                            <td style="font-size:12px;color:var(--text-3);max-width:140px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                {{ $movement->notes ?? '—' }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            {{ $stockMovements->links() }}
            @else
            <div class="empty-state">
                <i class="bi bi-arrow-left-right"></i>
                <h5>No Movements Yet</h5>
                <p>Stock movements will appear here when stock is added or sold.</p>
            </div>
            @endif
        </div>

        <!-- Sales History -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title"><i class="bi bi-cart me-2"></i>Recent Sales</h5>
            </div>
            @if($salesHistory->count() > 0)
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr><th>Sale #</th><th>Date</th><th>Staff</th><th>Qty</th><th>Price</th><th>Total</th><th>Status</th></tr>
                    </thead>
                    <tbody>
                        @foreach($salesHistory as $sale)
                        <tr>
                            <td><a href="{{ route('sales.show', $sale) }}" style="color:var(--primary);font-weight:600;text-decoration:none;font-size:12px;">{{ $sale->sale_number }}</a></td>
                            <td style="font-size:12px;">{{ $sale->sold_at->format('d M Y') }}</td>
                            <td style="font-size:12px;color:var(--text-2);">{{ $sale->staff->name ?? '—' }}</td>
                            <td>{{ $sale->quantity }}</td>
                            <td>₹{{ number_format($sale->selling_price, 2) }}</td>
                            <td style="font-weight:600;">₹{{ number_format($sale->total_amount, 2) }}</td>
                            <td><span class="status-badge status-{{ $sale->status }}">{{ ucfirst($sale->status) }}</span></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            {{ $salesHistory->links() }}
            @else
            <div class="empty-state py-5">
                <i class="bi bi-cart-x"></i>
                <p class="mb-0">No sales recorded for this product yet.</p>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Add Stock Modal -->
@can('manage', App\Models\Product::class)
<div class="modal fade" id="addStockModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-plus-circle me-2 text-success"></i>Add Stock</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('inventory.add-stock', $product) }}" method="POST" data-loading>
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Quantity to Add</label>
                        <input type="number" name="quantity" class="form-control" min="1" required placeholder="e.g. 50" autofocus />
                    </div>
                    <div>
                        <label class="form-label">Notes (optional)</label>
                        <input type="text" name="notes" class="form-control" placeholder="Reason for restock…" />
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

<!-- Adjust Stock Modal -->
<div class="modal fade" id="adjustStockModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-sliders me-2 text-warning"></i>Adjust Stock</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('inventory.adjust-stock', $product) }}" method="POST" data-loading>
                @csrf
                <div class="modal-body">
                    <p style="font-size:13px;color:var(--text-2);">Current stock: <strong>{{ $product->current_stock }}</strong></p>
                    <div class="mb-3">
                        <label class="form-label">New Stock Count</label>
                        <input type="number" name="new_stock" class="form-control" min="0" required placeholder="Enter correct stock count" />
                    </div>
                    <div>
                        <label class="form-label">Reason <span class="text-danger">*</span></label>
                        <input type="text" name="notes" class="form-control" required placeholder="Physical count, damage, etc." />
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning"><i class="bi bi-check2 me-1"></i>Adjust</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openAddStockModal() { new bootstrap.Modal(document.getElementById('addStockModal')).show(); }
function openAdjustStockModal() { new bootstrap.Modal(document.getElementById('adjustStockModal')).show(); }
</script>
@endcan
@endsection

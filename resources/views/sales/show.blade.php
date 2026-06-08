@extends('layouts.app')
@section('title', 'Sale ' . $sale->sale_number)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('sales.index') }}" style="color:var(--text-2);text-decoration:none;">Sales</a></li>
    <li class="breadcrumb-item active">{{ $sale->sale_number }}</li>
@endsection

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">{{ $sale->sale_number }}</h1>
        <div class="d-flex align-items-center gap-2 mt-1">
            <span class="status-badge status-{{ $sale->status }}">{{ ucfirst($sale->status) }}</span>
            <span style="font-size:12px;color:var(--text-3);">{{ $sale->sold_at->format('d M Y, h:i A') }}</span>
        </div>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('sales.receipt', $sale) }}" class="btn btn-outline-primary" target="_blank">
            <i class="bi bi-printer me-2"></i>Receipt
        </a>
        @can('update', $sale)
        @if($sale->status === 'completed')
        <a href="{{ route('sales.edit', $sale) }}" class="btn btn-outline-secondary">
            <i class="bi bi-pencil me-2"></i>Edit
        </a>
        @endif
        @endcan
        @can('manage', $sale)
        @if($sale->status === 'completed')
        <button class="btn btn-outline-warning" onclick="document.getElementById('refundModal').querySelector('.modal').style.display='none'" data-bs-toggle="modal" data-bs-target="#refundModal">
            <i class="bi bi-arrow-return-left me-2"></i>Refund
        </button>
        <button class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#cancelModal">
            <i class="bi bi-x-circle me-2"></i>Cancel
        </button>
        @endif
        @endcan
        <a href="{{ route('sales.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Back
        </a>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <!-- Sale Details -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title">Sale Information</h5>
            </div>
            <div class="card-body p-0">
                <div class="info-row px-4"><span class="info-label">Sale Number</span><span class="info-value fw-700">{{ $sale->sale_number }}</span></div>
                <div class="info-row px-4">
                    <span class="info-label">Product</span>
                    <span class="info-value">
                        <a href="{{ route('inventory.show', $sale->product) }}" style="color:var(--primary);text-decoration:none;">
                            {{ $sale->product->name ?? 'Deleted Product' }}
                        </a>
                    </span>
                </div>
                <div class="info-row px-4"><span class="info-label">Quantity</span><span class="info-value">{{ $sale->quantity }} units</span></div>
                <div class="info-row px-4"><span class="info-label">Selling Price</span><span class="info-value">₹{{ number_format($sale->selling_price, 2) }} / unit</span></div>
                <div class="info-row px-4">
                    <span class="info-label">Total Amount</span>
                    <span class="info-value" style="font-size:18px;font-weight:800;color:var(--success);">₹{{ number_format($sale->total_amount, 2) }}</span>
                </div>
                <div class="info-row px-4"><span class="info-label">Status</span><span class="info-value"><span class="status-badge status-{{ $sale->status }}">{{ ucfirst($sale->status) }}</span></span></div>
                <div class="info-row px-4"><span class="info-label">Sold At</span><span class="info-value">{{ $sale->sold_at->format('D, d M Y h:i A') }}</span></div>
                @if($sale->notes)
                <div class="info-row px-4"><span class="info-label">Notes</span><span class="info-value" style="font-size:13px;">{{ $sale->notes }}</span></div>
                @endif
            </div>
        </div>

        @if($sale->refunds->count() > 0)
        <div class="card">
            <div class="card-header"><h5 class="card-title">Refund History</h5></div>
            <div class="table-responsive">
                <table class="table">
                    <thead><tr><th>Date</th><th>Qty</th><th>Refund Amount</th><th>Processed By</th><th>Reason</th></tr></thead>
                    <tbody>
                        @foreach($sale->refunds as $refund)
                        <tr>
                            <td style="font-size:12px;">{{ $refund->refunded_at->format('d M Y') }}</td>
                            <td>{{ $refund->quantity }}</td>
                            <td style="font-weight:700;color:var(--danger);">₹{{ number_format($refund->refund_amount, 2) }}</td>
                            <td style="font-size:12px;">{{ $refund->processor->name ?? '—' }}</td>
                            <td style="font-size:12px;color:var(--text-2);">{{ $refund->reason }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    </div>

    <div class="col-lg-4">
        <!-- Staff & Customer -->
        <div class="card mb-4">
            <div class="card-header"><h5 class="card-title">People</h5></div>
            <div class="card-body">
                <div style="margin-bottom:16px;">
                    <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--text-3);margin-bottom:8px;">Sold By</div>
                    <div class="d-flex align-items-center gap-2">
                        <img src="{{ $sale->staff->avatar_url ?? '' }}" alt="{{ $sale->staff->name ?? '' }}" style="width:36px;height:36px;border-radius:50%;object-fit:cover;" />
                        <div>
                            <div style="font-size:14px;font-weight:600;color:var(--text);">{{ $sale->staff->name ?? '—' }}</div>
                            <div style="font-size:12px;color:var(--text-3);">{{ $sale->staff->getRoleLabel() ?? '' }}</div>
                        </div>
                    </div>
                </div>
                @if($sale->customer_name)
                <hr style="border-color:var(--border-light);">
                <div>
                    <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--text-3);margin-bottom:8px;">Customer</div>
                    <div style="font-size:14px;font-weight:600;color:var(--text);">{{ $sale->customer_name }}</div>
                    @if($sale->customer_phone)
                    <div style="font-size:12px;color:var(--text-3);margin-top:2px;"><i class="bi bi-phone me-1"></i>{{ $sale->customer_phone }}</div>
                    @endif
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Refund Modal -->
@can('manage', $sale)
@if($sale->status === 'completed')
<div class="modal fade" id="refundModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-arrow-return-left me-2 text-warning"></i>Process Refund</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('sales.refund', $sale) }}" method="POST" data-loading>
                @csrf
                <div class="modal-body">
                    <p style="font-size:13px;color:var(--text-2);">Original quantity: <strong>{{ $sale->quantity }}</strong></p>
                    <div class="mb-3">
                        <label class="form-label">Refund Quantity</label>
                        <input type="number" name="quantity" class="form-control" min="1" max="{{ $sale->quantity }}" required value="{{ $sale->quantity }}" />
                    </div>
                    <div>
                        <label class="form-label">Reason <span class="text-danger">*</span></label>
                        <textarea name="reason" class="form-control" rows="2" required placeholder="Reason for refund…"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning"><i class="bi bi-check2 me-1"></i>Process Refund</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Cancel Modal -->
<div class="modal fade" id="cancelModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-x-circle me-2 text-danger"></i>Cancel Sale</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('sales.cancel', $sale) }}" method="POST" data-loading>
                @csrf
                <div class="modal-body">
                    <div class="alert py-2 mb-3" style="background:var(--danger-bg);border-color:var(--danger);color:var(--danger);border-radius:8px;font-size:13px;">
                        <i class="bi bi-exclamation-triangle me-2"></i>Stock will be restored after cancellation.
                    </div>
                    <div>
                        <label class="form-label">Reason (optional)</label>
                        <textarea name="reason" class="form-control" rows="2" placeholder="Reason for cancellation…"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Keep Sale</button>
                    <button type="submit" class="btn btn-danger"><i class="bi bi-x-circle me-1"></i>Cancel Sale</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endcan
@endsection

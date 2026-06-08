@extends('layouts.app')
@section('title', 'Edit Sale')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('sales.index') }}" style="color:var(--text-2);text-decoration:none;">Sales</a></li>
    <li class="breadcrumb-item"><a href="{{ route('sales.show', $sale) }}" style="color:var(--text-2);text-decoration:none;">{{ $sale->sale_number }}</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Edit Sale {{ $sale->sale_number }}</h1>
        <p class="page-subtitle">You can edit the price and customer details only</p>
    </div>
    <a href="{{ route('sales.show', $sale) }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-2"></i>Back
    </a>
</div>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header"><h5 class="card-title">Sale Details</h5></div>
            <div class="card-body">
                <form action="{{ route('sales.update', $sale) }}" method="POST" data-loading>
                    @csrf @method('PUT')

                    <div class="mb-4">
                        <label class="form-label">Product</label>
                        <input type="text" class="form-control" value="{{ $sale->product->name ?? '—' }}" disabled />
                        <div class="form-text">Product and quantity cannot be changed. Cancel and create a new sale if needed.</div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-label">Quantity</label>
                            <input type="number" class="form-control" value="{{ $sale->quantity }}" disabled />
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Selling Price (₹) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">₹</span>
                                <input type="number" name="selling_price" id="selling_price"
                                    class="form-control @error('selling_price') is-invalid @enderror"
                                    value="{{ old('selling_price', $sale->selling_price) }}"
                                    min="0.01" step="0.01" required oninput="updateTotal()" />
                            </div>
                            @error('selling_price')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">New Total</label>
                            <div id="total_preview" style="padding:9px 16px;background:var(--primary-bg);border:2px solid var(--primary);border-radius:var(--radius-sm);font-size:18px;font-weight:800;color:var(--primary);">
                                ₹{{ number_format($sale->total_amount, 2) }}
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Customer Name</label>
                            <input type="text" name="customer_name" class="form-control"
                                value="{{ old('customer_name', $sale->customer_name) }}" />
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Customer Phone</label>
                            <input type="text" name="customer_phone" class="form-control"
                                value="{{ old('customer_phone', $sale->customer_phone) }}" />
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="2">{{ old('notes', $sale->notes) }}</textarea>
                    </div>

                    <div class="d-flex gap-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check2-circle me-2"></i>Save Changes
                        </button>
                        <a href="{{ route('sales.show', $sale) }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
const qty = {{ $sale->quantity }};
function updateTotal() {
    const price = parseFloat(document.getElementById('selling_price').value || 0);
    const total = qty * price;
    document.getElementById('total_preview').textContent = '₹' + total.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}
</script>
@endsection

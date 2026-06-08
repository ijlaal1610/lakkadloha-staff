@extends('layouts.app')
@section('title', 'New Sale')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('sales.index') }}" style="color:var(--text-2);text-decoration:none;">Sales</a></li>
    <li class="breadcrumb-item active">New Sale</li>
@endsection

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">New Sale</h1>
        <p class="page-subtitle">Record a new product sale</p>
    </div>
    <a href="{{ route('sales.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-2"></i>Back
    </a>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title"><i class="bi bi-cart-plus me-2 text-primary"></i>Sale Details</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('sales.store') }}" method="POST" data-loading id="saleForm">
                    @csrf

                    <!-- Product -->
                    <div class="mb-4">
                        <label class="form-label">Product <span class="text-danger">*</span></label>
                        <select name="product_id" id="product_id" class="form-select @error('product_id') is-invalid @enderror" required onchange="onProductChange(this)">
                            <option value="">— Select a product —</option>
                            @foreach($products as $product)
                            <option value="{{ $product->id }}"
                                data-stock="{{ $product->current_stock }}"
                                data-name="{{ $product->name }}"
                                {{ old('product_id') == $product->id ? 'selected' : '' }}>
                                {{ $product->name }} ({{ $product->current_stock }} in stock)
                            </option>
                            @endforeach
                        </select>
                        <div id="stock_info" class="form-text"></div>
                        @error('product_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="row g-3 mb-4">
                        <!-- Quantity -->
                        <div class="col-md-4">
                            <label class="form-label">Quantity <span class="text-danger">*</span></label>
                            <input type="number" name="quantity" id="quantity"
                                class="form-control @error('quantity') is-invalid @enderror"
                                value="{{ old('quantity', 1) }}" min="1" required
                                oninput="updateTotal()" />
                            @error('quantity')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <!-- Selling Price -->
                        <div class="col-md-4">
                            <label class="form-label">Selling Price (₹) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">₹</span>
                                <input type="number" name="selling_price" id="selling_price"
                                    class="form-control @error('selling_price') is-invalid @enderror"
                                    value="{{ old('selling_price') }}" min="0.01" step="0.01" required
                                    placeholder="0.00" oninput="updateTotal()" />
                            </div>
                            <div class="form-text">You can set any price per unit</div>
                            @error('selling_price')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <!-- Total Preview -->
                        <div class="col-md-4">
                            <label class="form-label">Total Amount</label>
                            <div style="padding:9px 16px;background:var(--primary-bg);border:2px solid var(--primary);border-radius:var(--radius-sm);">
                                <div id="total_preview" style="font-size:22px;font-weight:800;color:var(--primary);">₹0.00</div>
                            </div>
                        </div>
                    </div>

                    <!-- Customer Info -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Customer Name (optional)</label>
                            <input type="text" name="customer_name" class="form-control"
                                value="{{ old('customer_name') }}" placeholder="Walk-in customer" />
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Customer Phone (optional)</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-phone"></i></span>
                                <input type="text" name="customer_phone" class="form-control"
                                    value="{{ old('customer_phone') }}" placeholder="+91 9876543210" />
                            </div>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div class="mb-4">
                        <label class="form-label">Notes (optional)</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Any notes about this sale…">{{ old('notes') }}</textarea>
                    </div>

                    <div class="d-flex gap-3 flex-wrap">
                        <button type="submit" name="action" value="save" class="btn btn-primary">
                            <i class="bi bi-check2-circle me-2"></i>Save Sale
                        </button>
                        <button type="submit" name="print" value="1" class="btn btn-outline-primary">
                            <i class="bi bi-printer me-2"></i>Save & Print Receipt
                        </button>
                        <a href="{{ route('sales.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header"><h5 class="card-title">Sale Summary</h5></div>
            <div class="card-body">
                <div id="summaryProduct" style="font-size:13px;color:var(--text-3);margin-bottom:12px;">Select a product to see details</div>
                <div class="info-row">
                    <span class="info-label">Product</span>
                    <span id="sumProduct" class="info-value">—</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Available Stock</span>
                    <span id="sumStock" class="info-value">—</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Quantity</span>
                    <span id="sumQty" class="info-value">—</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Unit Price</span>
                    <span id="sumPrice" class="info-value">—</span>
                </div>
                <div class="info-row" style="border:none;">
                    <span class="info-label" style="font-weight:700;color:var(--text);">Total</span>
                    <span id="sumTotal" class="info-value" style="font-size:18px;font-weight:800;color:var(--success);">₹0.00</span>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h5 class="card-title">Guidelines</h5></div>
            <div class="card-body" style="font-size:13px;color:var(--text-2);line-height:1.7;">
                <p><i class="bi bi-check-circle-fill text-success me-2"></i>Stock reduces automatically after sale is saved.</p>
                <p><i class="bi bi-info-circle-fill text-info me-2"></i>Selling price is flexible — set it to any amount.</p>
                <p class="mb-0"><i class="bi bi-exclamation-triangle-fill text-warning me-2"></i>You cannot sell more than available stock.</p>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', initSaleForm);

function onProductChange(sel) {
    const opt = sel.options[sel.selectedIndex];
    const stock = opt.getAttribute('data-stock');
    const name  = opt.getAttribute('data-name') || '—';
    const stockInfo = document.getElementById('stock_info');
    const qtyInput  = document.getElementById('quantity');

    document.getElementById('sumProduct').textContent = name;

    if (stock !== null && opt.value) {
        const s = parseInt(stock);
        stockInfo.textContent = 'Available: ' + s.toLocaleString('en-IN') + ' units';
        stockInfo.className   = s <= 0 ? 'form-text text-danger' : 'form-text text-success';
        document.getElementById('sumStock').textContent = s + ' units';
        if (qtyInput) qtyInput.max = s;
    } else {
        stockInfo.textContent = '';
        document.getElementById('sumStock').textContent = '—';
    }
    updateTotal();
}

function updateTotal() {
    const qty   = parseFloat(document.getElementById('quantity')?.value || 0);
    const price = parseFloat(document.getElementById('selling_price')?.value || 0);
    const total = isNaN(qty) || isNaN(price) ? 0 : qty * price;
    const fmt   = v => '₹' + v.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

    document.getElementById('total_preview').textContent = fmt(total);
    document.getElementById('sumQty').textContent   = isNaN(qty) ? '—' : qty;
    document.getElementById('sumPrice').textContent = isNaN(price) ? '—' : fmt(price);
    document.getElementById('sumTotal').textContent = fmt(total);
}
</script>
@endsection

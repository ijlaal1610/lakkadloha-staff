@extends('layouts.app')
@section('title', 'Add Product')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('inventory.index') }}" style="color:var(--text-2);text-decoration:none;">Inventory</a></li>
    <li class="breadcrumb-item active">Add Product</li>
@endsection

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Add Product</h1>
        <p class="page-subtitle">Create a new inventory product</p>
    </div>
    <a href="{{ route('inventory.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-2"></i>Back
    </a>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Product Information</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('inventory.store') }}" method="POST" enctype="multipart/form-data" data-loading>
                    @csrf
                    <div class="mb-4">
                        <label class="form-label">Product Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                            value="{{ old('name') }}" placeholder="e.g. Teak Wood Plank 6ft" required autofocus />
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Initial Stock <span class="text-danger">*</span></label>
                            <input type="number" name="current_stock" class="form-control @error('current_stock') is-invalid @enderror"
                                value="{{ old('current_stock', 0) }}" min="0" required />
                            <div class="form-text">Units currently available</div>
                            @error('current_stock')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Low Stock Alert Threshold <span class="text-danger">*</span></label>
                            <input type="number" name="low_stock_threshold" class="form-control @error('low_stock_threshold') is-invalid @enderror"
                                value="{{ old('low_stock_threshold', 10) }}" min="1" required />
                            <div class="form-text">Alert when stock drops to this level</div>
                            @error('low_stock_threshold')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Notes (Optional)</label>
                        <textarea name="notes" class="form-control" rows="3" placeholder="Any additional notes about this product…">{{ old('notes') }}</textarea>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Product Photo (Optional)</label>
                        <input type="file" name="photo" class="form-control" accept="image/*" onchange="previewPhoto(this)" />
                        <div class="form-text">Max 2MB. JPG, PNG, WebP</div>
                        <div id="photoPreview" style="display:none;margin-top:12px;">
                            <img id="previewImg" src="" alt="Preview" style="height:100px;border-radius:8px;border:1px solid var(--border);" />
                        </div>
                    </div>

                    <div class="d-flex gap-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check2-circle me-2"></i>Create Product
                        </button>
                        <a href="{{ route('inventory.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Tips</h5>
            </div>
            <div class="card-body">
                <div style="font-size:13px;color:var(--text-2);line-height:1.7;">
                    <p><i class="bi bi-lightbulb-fill text-warning me-2"></i>Set a realistic low-stock threshold so you get alerts before running out.</p>
                    <p><i class="bi bi-info-circle-fill text-info me-2"></i>Initial stock entered here will be recorded as the first stock movement.</p>
                    <p class="mb-0"><i class="bi bi-image-fill text-primary me-2"></i>Product photos help staff identify items quickly during sales.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function previewPhoto(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            document.getElementById('previewImg').src = e.target.result;
            document.getElementById('photoPreview').style.display = 'block';
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endsection

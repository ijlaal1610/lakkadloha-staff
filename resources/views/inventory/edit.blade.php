@extends('layouts.app')
@section('title', 'Edit ' . $product->name)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('inventory.index') }}" style="color:var(--text-2);text-decoration:none;">Inventory</a></li>
    <li class="breadcrumb-item"><a href="{{ route('inventory.show', $product) }}" style="color:var(--text-2);text-decoration:none;">{{ Str::limit($product->name, 20) }}</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Edit Product</h1>
        <p class="page-subtitle">Update product information and settings</p>
    </div>
    <a href="{{ route('inventory.show', $product) }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-2"></i>Back
    </a>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header"><h5 class="card-title">Product Details</h5></div>
            <div class="card-body">
                <form action="{{ route('inventory.update', $product) }}" method="POST" enctype="multipart/form-data" data-loading>
                    @csrf @method('PUT')

                    <div class="mb-4">
                        <label class="form-label">Product Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                            value="{{ old('name', $product->name) }}" required />
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Low Stock Threshold <span class="text-danger">*</span></label>
                            <input type="number" name="low_stock_threshold" class="form-control @error('low_stock_threshold') is-invalid @enderror"
                                value="{{ old('low_stock_threshold', $product->low_stock_threshold) }}" min="1" required />
                            <div class="form-text">Current stock: <strong>{{ $product->current_stock }}</strong> — use Add/Adjust Stock to change stock level</div>
                            @error('low_stock_threshold')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status</label>
                            <select name="is_active" class="form-select">
                                <option value="1" {{ ($product->is_active ? 'selected' : '') }}>Active</option>
                                <option value="0" {{ (!$product->is_active ? 'selected' : '') }}>Inactive</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="3">{{ old('notes', $product->notes) }}</textarea>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Product Photo</label>
                        @if($product->photo)
                        <div class="mb-2">
                            <img src="{{ $product->photo_url }}" alt="{{ $product->name }}" style="height:80px;border-radius:8px;border:1px solid var(--border);" />
                            <div class="form-text">Upload a new photo to replace the current one</div>
                        </div>
                        @endif
                        <input type="file" name="photo" class="form-control" accept="image/*" onchange="previewPhoto(this)" />
                        <div id="photoPreview" style="display:none;margin-top:10px;">
                            <img id="previewImg" src="" style="height:80px;border-radius:8px;border:1px solid var(--border);" />
                        </div>
                    </div>

                    <div class="d-flex gap-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check2-circle me-2"></i>Save Changes
                        </button>
                        <a href="{{ route('inventory.show', $product) }}" class="btn btn-outline-secondary">Cancel</a>
                        @can('admin', App\Models\Product::class)
                        <form action="{{ route('inventory.destroy', $product) }}" method="POST" class="ms-auto"
                            onsubmit="return confirm('Delete {{ addslashes($product->name) }}? This cannot be undone.')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger">
                                <i class="bi bi-trash me-2"></i>Delete Product
                            </button>
                        </form>
                        @endcan
                    </div>
                </form>
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

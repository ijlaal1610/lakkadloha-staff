@extends('layouts.app')
@section('title', 'Add Salary Record')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('salary.index') }}" style="color:var(--text-2);text-decoration:none;">Salary</a></li>
    <li class="breadcrumb-item active">Add Record</li>
@endsection

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Add Salary Record</h1>
        <p class="page-subtitle">Record salary payment, advance, bonus, or deduction</p>
    </div>
    <a href="{{ route('salary.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-2"></i>Back
    </a>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title"><i class="bi bi-cash-stack me-2 text-success"></i>Payment Details</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('salary.store') }}" method="POST" data-loading>
                    @csrf

                    <div class="mb-4">
                        <label class="form-label">Employee <span class="text-danger">*</span></label>
                        <select name="employee_id" class="form-select @error('employee_id') is-invalid @enderror" required>
                            <option value="">— Select Employee —</option>
                            @foreach($employees as $emp)
                            <option value="{{ $emp->id }}" {{ old('employee_id') == $emp->id ? 'selected' : '' }}>
                                {{ $emp->name }} ({{ $emp->employee_id ?? $emp->getRoleLabel() }})
                            </option>
                            @endforeach
                        </select>
                        @error('employee_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Record Type <span class="text-danger">*</span></label>
                            <select name="type" class="form-select @error('type') is-invalid @enderror" required>
                                <option value="">— Select Type —</option>
                                <option value="salary" {{ old('type') === 'salary' ? 'selected' : '' }}>💰 Salary Payment</option>
                                <option value="advance" {{ old('type') === 'advance' ? 'selected' : '' }}>💳 Advance</option>
                                <option value="bonus" {{ old('type') === 'bonus' ? 'selected' : '' }}>🎁 Bonus</option>
                                <option value="deduction" {{ old('type') === 'deduction' ? 'selected' : '' }}>➖ Deduction</option>
                            </select>
                            @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Amount (₹) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">₹</span>
                                <input type="number" name="amount" class="form-control @error('amount') is-invalid @enderror"
                                    value="{{ old('amount') }}" min="1" step="0.01" required placeholder="0.00" />
                            </div>
                            @error('amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Record Date <span class="text-danger">*</span></label>
                            <input type="date" name="record_date" class="form-control @error('record_date') is-invalid @enderror"
                                value="{{ old('record_date', today()->format('Y-m-d')) }}" required />
                            @error('record_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Payment Method <span class="text-danger">*</span></label>
                            <select name="payment_method" class="form-select @error('payment_method') is-invalid @enderror" required>
                                <option value="">— Select Method —</option>
                                <option value="cash" {{ old('payment_method') === 'cash' ? 'selected' : '' }}>💵 Cash</option>
                                <option value="bank_transfer" {{ old('payment_method') === 'bank_transfer' ? 'selected' : '' }}>🏦 Bank Transfer</option>
                                <option value="upi" {{ old('payment_method') === 'upi' ? 'selected' : '' }}>📱 UPI</option>
                                <option value="cheque" {{ old('payment_method') === 'cheque' ? 'selected' : '' }}>📝 Cheque</option>
                            </select>
                            @error('payment_method')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Reference Number (optional)</label>
                        <input type="text" name="reference_number" class="form-control"
                            value="{{ old('reference_number') }}" placeholder="UTR, Cheque number, etc." />
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Notes (optional)</label>
                        <textarea name="notes" class="form-control" rows="3"
                            placeholder="Additional notes about this payment…">{{ old('notes') }}</textarea>
                    </div>

                    <div class="d-flex gap-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check2-circle me-2"></i>Save Record
                        </button>
                        <a href="{{ route('salary.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header"><h5 class="card-title">Record Types</h5></div>
            <div class="card-body p-0">
                <div class="p-3 border-bottom" style="border-color:var(--border-light)!important;">
                    <div style="font-size:13px;font-weight:700;color:var(--success);margin-bottom:4px;">💰 Salary</div>
                    <div style="font-size:12px;color:var(--text-2);">Regular monthly/weekly salary payment to employee.</div>
                </div>
                <div class="p-3 border-bottom" style="border-color:var(--border-light)!important;">
                    <div style="font-size:13px;font-weight:700;color:var(--warning);margin-bottom:4px;">💳 Advance</div>
                    <div style="font-size:12px;color:var(--text-2);">Salary advance given before the regular pay date.</div>
                </div>
                <div class="p-3 border-bottom" style="border-color:var(--border-light)!important;">
                    <div style="font-size:13px;font-weight:700;color:var(--info);margin-bottom:4px;">🎁 Bonus</div>
                    <div style="font-size:12px;color:var(--text-2);">Performance or festival bonus, incentives.</div>
                </div>
                <div class="p-3">
                    <div style="font-size:13px;font-weight:700;color:var(--danger);margin-bottom:4px;">➖ Deduction</div>
                    <div style="font-size:12px;color:var(--text-2);">Late penalty, damage recovery, etc.</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

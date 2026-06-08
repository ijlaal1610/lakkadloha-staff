@extends('layouts.app')
@section('title', 'Salary Record')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('salary.index') }}" style="color:var(--text-2);text-decoration:none;">Salary</a></li>
    <li class="breadcrumb-item active">Record #{{ $salary->id }}</li>
@endsection

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Salary Record #{{ $salary->id }}</h1>
        <span class="badge {{ $salary->type_badge_class }} mt-1">{{ $salary->type_label }}</span>
    </div>
    <a href="{{ route('salary.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-2"></i>Back
    </a>
</div>

<div class="row g-4">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header"><h5 class="card-title">Payment Details</h5></div>
            <div class="card-body p-0">
                <div class="info-row px-4"><span class="info-label">Employee</span><span class="info-value fw-700">{{ $salary->employee->name ?? '—' }}</span></div>
                <div class="info-row px-4"><span class="info-label">Employee ID</span><span class="info-value" style="font-family:monospace;">{{ $salary->employee->employee_id ?? '—' }}</span></div>
                <div class="info-row px-4"><span class="info-label">Type</span><span class="info-value"><span class="badge {{ $salary->type_badge_class }}">{{ $salary->type_label }}</span></span></div>
                <div class="info-row px-4">
                    <span class="info-label">Amount</span>
                    <span class="info-value" style="font-size:24px;font-weight:800;color:{{ $salary->isCredit() ? 'var(--success)' : 'var(--danger)' }};">
                        {{ $salary->isCredit() ? '+' : '-' }}₹{{ number_format($salary->amount, 2) }}
                    </span>
                </div>
                <div class="info-row px-4"><span class="info-label">Date</span><span class="info-value">{{ $salary->record_date->format('D, d M Y') }}</span></div>
                <div class="info-row px-4"><span class="info-label">Payment Method</span><span class="info-value">{{ ucwords(str_replace('_', ' ', $salary->payment_method)) }}</span></div>
                @if($salary->reference_number)
                <div class="info-row px-4"><span class="info-label">Reference</span><span class="info-value" style="font-family:monospace;">{{ $salary->reference_number }}</span></div>
                @endif
                @if($salary->notes)
                <div class="info-row px-4"><span class="info-label">Notes</span><span class="info-value" style="font-size:13px;">{{ $salary->notes }}</span></div>
                @endif
                <div class="info-row px-4"><span class="info-label">Processed By</span><span class="info-value">{{ $salary->processor->name ?? '—' }}</span></div>
                <div class="info-row px-4"><span class="info-label">Created</span><span class="info-value">{{ $salary->created_at->format('d M Y H:i') }}</span></div>
            </div>
        </div>
    </div>
</div>
@endsection

@extends('layouts.app')
@section('title', 'Salary Records')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Salary</li>
@endsection

@section('content')
<div class="page-header mb-4">
    <h1 class="page-title">Salary & Financials</h1>
</div>

<div class="row g-4 mb-4">
    @foreach(['Salary' => $totalSalary, 'Advance' => $totalAdvance, 'Bonus' => $totalBonus, 'Deduction' => $totalDeduction] as $label => $val)
    <div class="col-md-3">
        <div class="card p-3">
            <div class="text-muted small">{{ $label }}</div>
            <div class="h4 mb-0">₹{{ number_format($val, 2) }}</div>
        </div>
    </div>
    @endforeach
</div>

<div class="card">
    <div class="card-header"><h5 class="card-title">Transaction History</h5></div>
    <div class="card-body p-0">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th class="ps-4">Date</th>
                    <th>Type</th>
                    <th>Amount</th>
                    <th>Notes</th>
                </tr>
            </thead>
            <tbody>
                @foreach($records as $rec)
                <tr>
                    <td class="ps-4">{{ $rec->created_at->format('d M, Y') }}</td>
                    <td><span class="badge bg-light text-dark">{{ ucfirst($rec->type) }}</span></td>
                    <td class="fw-bold">₹{{ number_format($rec->amount, 2) }}</td>
                    <td>{{ $rec->notes }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
@extends('layouts.app')
@section('title', 'Reports')

@section('breadcrumb')
    <li class="breadcrumb-item active">Reports</li>
@endsection

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Reports & Analytics</h1>
        <p class="page-subtitle">Export and analyse business data</p>
    </div>
</div>

<div class="row g-4">
    <div class="col-md-6 col-lg-3">
        <a href="{{ route('reports.sales') }}" class="card text-decoration-none" style="transition:transform .2s,box-shadow .2s;display:block;">
            <div class="card-body text-center p-4">
                <div style="width:64px;height:64px;border-radius:16px;background:var(--primary-bg);display:flex;align-items:center;justify-content:center;margin:0 auto 16px;font-size:28px;color:var(--primary);">
                    <i class="bi bi-cart-fill"></i>
                </div>
                <div style="font-size:16px;font-weight:700;color:var(--text);margin-bottom:6px;">Sales Report</div>
                <div style="font-size:13px;color:var(--text-3);">Revenue, orders, staff performance</div>
            </div>
        </a>
    </div>
    <div class="col-md-6 col-lg-3">
        <a href="{{ route('reports.attendance') }}" class="card text-decoration-none" style="transition:transform .2s,box-shadow .2s;display:block;">
            <div class="card-body text-center p-4">
                <div style="width:64px;height:64px;border-radius:16px;background:var(--success-bg);display:flex;align-items:center;justify-content:center;margin:0 auto 16px;font-size:28px;color:var(--success);">
                    <i class="bi bi-calendar-check-fill"></i>
                </div>
                <div style="font-size:16px;font-weight:700;color:var(--text);margin-bottom:6px;">Attendance Report</div>
                <div style="font-size:13px;color:var(--text-3);">Present, absent, late summaries</div>
            </div>
        </a>
    </div>
    <div class="col-md-6 col-lg-3">
        <a href="{{ route('reports.salary') }}" class="card text-decoration-none" style="transition:transform .2s,box-shadow .2s;display:block;">
            <div class="card-body text-center p-4">
                <div style="width:64px;height:64px;border-radius:16px;background:var(--warning-bg);display:flex;align-items:center;justify-content:center;margin:0 auto 16px;font-size:28px;color:var(--warning);">
                    <i class="bi bi-cash-stack"></i>
                </div>
                <div style="font-size:16px;font-weight:700;color:var(--text);margin-bottom:6px;">Salary Report</div>
                <div style="font-size:13px;color:var(--text-3);">Payments, advances, bonuses</div>
            </div>
        </a>
    </div>
    <div class="col-md-6 col-lg-3">
        <a href="{{ route('reports.inventory') }}" class="card text-decoration-none" style="transition:transform .2s,box-shadow .2s;display:block;">
            <div class="card-body text-center p-4">
                <div style="width:64px;height:64px;border-radius:16px;background:var(--info-bg);display:flex;align-items:center;justify-content:center;margin:0 auto 16px;font-size:28px;color:var(--info);">
                    <i class="bi bi-box-seam-fill"></i>
                </div>
                <div style="font-size:16px;font-weight:700;color:var(--text);margin-bottom:6px;">Inventory Report</div>
                <div style="font-size:13px;color:var(--text-3);">Stock levels, movements, alerts</div>
            </div>
        </a>
    </div>
</div>
@endsection

@extends('layouts.app')
@section('title', 'Attendance')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Attendance</li>
@endsection

@section('content')
<div class="page-header d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="page-title">Attendance</h1>
        <p class="page-subtitle">Manage your daily clock-ins and outs</p>
    </div>
    <div>
        <form action="{{ route('attendance.checkin') }}" method="POST" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-primary"><i class="bi bi-box-arrow-in-right me-2"></i>Check In</button>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-4">Date</th>
                        <th>Time In</th>
                        <th>Time Out</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($attendances as $att)
                    <tr>
                        <td class="ps-4">{{ $att->date->format('d M, Y') }}</td>
                        <td>{{ $att->time_in?->format('h:i A') ?? '--' }}</td>
                        <td>{{ $att->time_out?->format('h:i A') ?? '--' }}</td>
                        <td>
                            <span class="badge {{ $att->time_out ? 'bg-success' : 'bg-warning' }}">
                                {{ $att->time_out ? 'Completed' : 'Active' }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="text-center py-4">No records found</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8" />
<title>Salary Report</title>
<style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #111; }
    .header { background: #f59e0b; color: white; padding: 16px 20px; margin-bottom: 16px; }
    .header h1 { font-size: 18px; font-weight: 700; }
    .header p { font-size: 11px; opacity: 0.9; margin-top: 4px; }
    table { width: 100%; border-collapse: collapse; margin: 0 20px; width: calc(100% - 40px); }
    thead th { background: #f1f2f8; color: #8b93ad; font-size: 10px; font-weight: 700; text-transform: uppercase; padding: 8px 10px; text-align: left; }
    tbody td { padding: 8px 10px; border-bottom: 1px solid #f0f1f8; }
    tfoot td { padding: 10px; font-weight: 700; border-top: 2px solid #e5e7f0; background: #fafafa; }
    .badge { display: inline-block; padding: 2px 6px; border-radius: 4px; font-size: 9px; font-weight: 700; }
    .footer { margin-top: 16px; padding: 10px 20px; border-top: 1px solid #e5e7f0; font-size: 10px; color: #8b93ad; text-align: center; }
</style>
</head>
<body>
<div class="header">
    <h1>Salary Report — Lakkad Loha</h1>
    <p>Period: {{ $from->format('d M Y') }} to {{ $to->format('d M Y') }} &nbsp;|&nbsp; Generated: {{ now()->format('d M Y H:i') }}</p>
</div>
<table>
    <thead>
        <tr>
            <th>Date</th>
            <th>Employee</th>
            <th>Type</th>
            <th>Amount</th>
            <th>Method</th>
            <th>Notes</th>
        </tr>
    </thead>
    <tbody>
        @foreach($records as $r)
        <tr>
            <td>{{ $r->record_date->format('d M Y') }}</td>
            <td style="font-weight:600;">{{ $r->employee->name ?? '—' }}</td>
            <td>
                <span class="badge" style="background:{{ ['salary'=>'#d1fae5','advance'=>'#fef3c7','bonus'=>'#dbeafe','deduction'=>'#fee2e2'][$r->type] }};
                    color:{{ ['salary'=>'#065f46','advance'=>'#78350f','bonus'=>'#1e3a5f','deduction'=>'#991b1b'][$r->type] }};">
                    {{ strtoupper($r->type) }}
                </span>
            </td>
            <td style="font-weight:700;color:{{ in_array($r->type, ['salary','bonus']) ? '#059669' : '#dc2626' }};">
                {{ in_array($r->type, ['salary','bonus']) ? '+' : '-' }}₹{{ number_format($r->amount, 2) }}
            </td>
            <td>{{ ucwords(str_replace('_',' ',$r->payment_method)) }}</td>
            <td style="color:#8b93ad;">{{ $r->notes ?? '—' }}</td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="3" style="text-align:right;">Total Payout (Salary + Bonus):</td>
            <td style="color:#059669;">₹{{ number_format($records->whereIn('type',['salary','bonus'])->sum('amount'), 2) }}</td>
            <td colspan="2"></td>
        </tr>
    </tfoot>
</table>
<div class="footer">Lakkad Loha Staff Management &bull; Confidential</div>
</body>
</html>

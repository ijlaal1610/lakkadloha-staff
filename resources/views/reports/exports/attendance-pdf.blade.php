<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8" />
<title>Attendance Report</title>
<style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #111; }
    .header { background: #10b981; color: white; padding: 16px 20px; margin-bottom: 16px; }
    .header h1 { font-size: 18px; font-weight: 700; }
    .header p { font-size: 11px; opacity: 0.85; margin-top: 4px; }
    table { width: 100%; border-collapse: collapse; margin: 0 20px; width: calc(100% - 40px); }
    thead th { background: #f1f2f8; color: #8b93ad; font-size: 10px; font-weight: 700; text-transform: uppercase; padding: 8px 10px; text-align: left; }
    tbody td { padding: 8px 10px; border-bottom: 1px solid #f0f1f8; }
    .pct-high { color: #059669; font-weight: 700; }
    .pct-mid { color: #d97706; font-weight: 700; }
    .pct-low { color: #dc2626; font-weight: 700; }
    .footer { margin-top: 16px; padding: 10px 20px; border-top: 1px solid #e5e7f0; font-size: 10px; color: #8b93ad; text-align: center; }
</style>
</head>
<body>
<div class="header">
    <h1>Attendance Report — Lakkad Loha</h1>
    <p>Period: {{ $from->format('d M Y') }} to {{ $to->format('d M Y') }} &nbsp;|&nbsp; Generated: {{ now()->format('d M Y H:i') }}</p>
</div>
<table>
    <thead>
        <tr>
            <th>Employee</th>
            <th>Present</th>
            <th>Absent</th>
            <th>Late</th>
            <th>Leave</th>
            <th>Attendance %</th>
        </tr>
    </thead>
    <tbody>
        @foreach($summary as $row)
        @php $total = $row['present'] + $row['absent'] + $row['late'] + $row['leave']; $pct = $total > 0 ? round($row['present'] / $total * 100) : 0; @endphp
        <tr>
            <td style="font-weight:600;">{{ $row['staff']->name }}</td>
            <td style="color:#059669;font-weight:700;">{{ $row['present'] }}</td>
            <td style="color:#dc2626;font-weight:700;">{{ $row['absent'] }}</td>
            <td style="color:#d97706;">{{ $row['late'] }}</td>
            <td>{{ $row['leave'] }}</td>
            <td class="{{ $pct >= 80 ? 'pct-high' : ($pct >= 60 ? 'pct-mid' : 'pct-low') }}">{{ $pct }}%</td>
        </tr>
        @endforeach
    </tbody>
</table>
<div class="footer">Lakkad Loha Staff Management &bull; Confidential</div>
</body>
</html>

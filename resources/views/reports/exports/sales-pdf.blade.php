<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8" />
<title>Sales Report</title>
<style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #111; background: white; }
    .header { background: #4f46e5; color: white; padding: 20px 24px; margin-bottom: 20px; }
    .header h1 { font-size: 20px; font-weight: 700; }
    .header p { font-size: 12px; opacity: 0.8; margin-top: 4px; }
    .summary { display: flex; gap: 16px; padding: 0 24px; margin-bottom: 20px; }
    .summary-card { flex: 1; background: #f8f9ff; border: 1px solid #e5e7f0; border-radius: 8px; padding: 12px; text-align: center; }
    .summary-val { font-size: 18px; font-weight: 800; color: #4f46e5; }
    .summary-lbl { font-size: 10px; color: #8b93ad; margin-top: 2px; }
    table { width: 100%; border-collapse: collapse; margin: 0 24px; width: calc(100% - 48px); }
    thead th { background: #f1f2f8; color: #8b93ad; font-size: 10px; font-weight: 700; text-transform: uppercase; padding: 8px 12px; text-align: left; }
    tbody td { padding: 8px 12px; border-bottom: 1px solid #f0f1f8; }
    tbody tr:nth-child(even) { background: #fafafa; }
    tfoot td { padding: 10px 12px; font-weight: 700; border-top: 2px solid #e5e7f0; }
    .footer { margin-top: 20px; padding: 12px 24px; border-top: 1px solid #e5e7f0; font-size: 10px; color: #8b93ad; text-align: center; }
    .badge { display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 10px; font-weight: 700; }
    .badge-completed { background: #d1fae5; color: #065f46; }
    .badge-cancelled { background: #fee2e2; color: #991b1b; }
    .badge-refunded { background: #fef3c7; color: #92400e; }
</style>
</head>
<body>
<div class="header">
    <h1>Sales Report — Lakkad Loha</h1>
    <p>Period: {{ $from->format('d M Y') }} to {{ $to->format('d M Y') }} &nbsp;|&nbsp; Generated: {{ now()->format('d M Y H:i') }}</p>
</div>

<div class="summary">
    <div class="summary-card">
        <div class="summary-val">₹{{ number_format($total, 2) }}</div>
        <div class="summary-lbl">Total Revenue</div>
    </div>
    <div class="summary-card">
        <div class="summary-val">{{ $sales->count() }}</div>
        <div class="summary-lbl">Total Orders</div>
    </div>
    <div class="summary-card">
        <div class="summary-val">₹{{ $sales->count() > 0 ? number_format($total / $sales->count(), 2) : '0.00' }}</div>
        <div class="summary-lbl">Avg. Order</div>
    </div>
</div>

<table>
    <thead>
        <tr>
            <th>Sale #</th>
            <th>Date & Time</th>
            <th>Product</th>
            <th>Staff</th>
            <th>Qty</th>
            <th>Unit Price</th>
            <th>Total</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach($sales as $sale)
        <tr>
            <td style="font-weight:700;font-family:monospace;">{{ $sale->sale_number }}</td>
            <td>{{ $sale->sold_at->format('d M Y H:i') }}</td>
            <td>{{ $sale->product->name ?? '—' }}</td>
            <td>{{ $sale->staff->name ?? '—' }}</td>
            <td style="text-align:center;">{{ $sale->quantity }}</td>
            <td>₹{{ number_format($sale->selling_price, 2) }}</td>
            <td style="font-weight:700;">₹{{ number_format($sale->total_amount, 2) }}</td>
            <td><span class="badge badge-{{ $sale->status }}">{{ strtoupper($sale->status) }}</span></td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="6" style="text-align:right;">GRAND TOTAL:</td>
            <td style="color:#4f46e5;">₹{{ number_format($total, 2) }}</td>
            <td></td>
        </tr>
    </tfoot>
</table>

<div class="footer">
    Lakkad Loha Staff Management System &bull; staff.lakkadloha.com &bull; Confidential
</div>
</body>
</html>

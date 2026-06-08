<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Receipt {{ $sale->sale_number }}</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css" rel="stylesheet" />
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { background: #f0f0f0; font-family: 'Courier New', monospace; min-height: 100vh; display: flex; align-items: flex-start; justify-content: center; padding: 24px; }
        .receipt { background: white; width: 360px; padding: 28px 24px; border-radius: 4px; box-shadow: 0 4px 24px rgba(0,0,0,0.12); }
        .brand { text-align: center; margin-bottom: 20px; }
        .brand h2 { font-size: 22px; font-weight: 900; letter-spacing: 2px; color: #111; margin-bottom: 4px; }
        .brand p { font-size: 11px; color: #666; }
        .divider { border: none; border-top: 2px dashed #ccc; margin: 16px 0; }
        .sale-num { text-align: center; font-size: 13px; color: #555; margin-bottom: 4px; }
        .sale-date { text-align: center; font-size: 11px; color: #888; margin-bottom: 16px; }
        table { width: 100%; font-size: 13px; border-collapse: collapse; }
        table th { font-size: 10px; text-transform: uppercase; color: #999; font-weight: 700; padding: 4px 0; border-bottom: 1px solid #eee; }
        table td { padding: 8px 0; border-bottom: 1px solid #f5f5f5; color: #333; vertical-align: top; }
        .total-row { font-size: 16px; font-weight: 900; color: #111; }
        .footer { text-align: center; margin-top: 20px; font-size: 11px; color: #aaa; }
        .status-pill { display: inline-block; padding: 2px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; background: #e8f5e9; color: #2e7d32; }
        .no-print { margin-top: 20px; display: flex; gap: 8px; justify-content: center; }
        @media print {
            body { background: white; padding: 0; }
            .receipt { box-shadow: none; border-radius: 0; width: 100%; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>
<div>
    <div class="receipt">
        <div class="brand">
            <h2>LAKKAD LOHA</h2>
            <p>Staff Management System</p>
            <p>staff.lakkadloha.com</p>
        </div>

        <hr class="divider" />

        <div class="sale-num">{{ $sale->sale_number }}</div>
        <div class="sale-date">{{ $sale->sold_at->format('D, d M Y • h:i A') }}</div>

        <hr class="divider" />

        <table>
            <thead>
                <tr>
                    <th style="width:50%;">Item</th>
                    <th style="text-align:right;">Qty</th>
                    <th style="text-align:right;">Price</th>
                    <th style="text-align:right;">Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ $sale->product->name ?? 'Item' }}</td>
                    <td style="text-align:right;">{{ $sale->quantity }}</td>
                    <td style="text-align:right;">₹{{ number_format($sale->selling_price, 2) }}</td>
                    <td style="text-align:right;font-weight:700;">₹{{ number_format($sale->total_amount, 2) }}</td>
                </tr>
            </tbody>
        </table>

        <hr class="divider" />

        <table>
            <tr>
                <td style="color:#888;font-size:12px;">Subtotal</td>
                <td style="text-align:right;">₹{{ number_format($sale->total_amount, 2) }}</td>
            </tr>
            <tr class="total-row">
                <td>TOTAL</td>
                <td style="text-align:right;">₹{{ number_format($sale->total_amount, 2) }}</td>
            </tr>
        </table>

        <hr class="divider" />

        <table style="font-size:12px;">
            <tr>
                <td style="color:#888;">Served by</td>
                <td style="text-align:right;font-weight:600;">{{ $sale->staff->name ?? '—' }}</td>
            </tr>
            @if($sale->customer_name)
            <tr>
                <td style="color:#888;">Customer</td>
                <td style="text-align:right;font-weight:600;">{{ $sale->customer_name }}</td>
            </tr>
            @endif
            <tr>
                <td style="color:#888;">Status</td>
                <td style="text-align:right;"><span class="status-pill">{{ strtoupper($sale->status) }}</span></td>
            </tr>
        </table>

        @if($sale->notes)
        <hr class="divider" />
        <p style="font-size:11px;color:#888;text-align:center;">{{ $sale->notes }}</p>
        @endif

        <div class="footer">
            <p>Thank you for your business!</p>
            <p style="margin-top:4px;font-size:10px;">**** Lakkad Loha ****</p>
        </div>
    </div>

    <div class="no-print">
        <button class="btn btn-primary btn-sm" onclick="window.print()">
            <i class="bi bi-printer me-1"></i> Print Receipt
        </button>
        <a href="{{ route('sales.show', $sale) }}" class="btn btn-outline-secondary btn-sm">
            Back to Sale
        </a>
    </div>
</div>
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css" rel="stylesheet" />
</body>
</html>

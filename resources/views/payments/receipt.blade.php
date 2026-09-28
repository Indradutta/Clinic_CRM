<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Receipt - {{ $payment->receipt_number }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #0f172a;
            background: #fff;
            padding: 30px;
            max-width: 650px;
            margin: 0 auto;
            font-size: 13px;
            line-height: 1.5;
        }
        .receipt-card {
            border: 2px dashed #cbd5e1;
            border-radius: 12px;
            padding: 24px;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 12px;
            margin-bottom: 16px;
        }
        .clinic-name { font-size: 18px; font-weight: 800; color: #1e3a8a; }
        .clinic-sub { font-size: 11px; color: #64748b; }
        .receipt-title { text-align: right; }
        .receipt-title h2 { font-size: 20px; font-weight: 900; color: #166534; }
        .receipt-title p { font-size: 11px; font-family: monospace; color: #475569; }

        .info-table {
            width: 100%;
            margin-bottom: 16px;
        }
        .info-table td { padding: 6px 4px; vertical-align: top; }
        .info-label { color: #64748b; font-size: 11px; text-transform: uppercase; font-weight: 700; width: 140px; }
        .info-value { font-weight: 600; color: #0f172a; }

        .amount-box {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 8px;
            padding: 12px 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .amount-box .label { font-size: 11px; font-weight: 700; text-transform: uppercase; color: #166534; }
        .amount-box .value { font-size: 24px; font-weight: 900; color: #166534; }

        .footer {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-top: 30px;
            padding-top: 10px;
        }
        .sig-line {
            border-top: 1px solid #0f172a;
            width: 160px;
            text-align: center;
            padding-top: 5px;
            font-size: 11px;
            font-weight: 600;
        }
        .btn-bar { margin-bottom: 20px; display: flex; justify-content: flex-end; gap: 10px; }
        .btn { background: #2563eb; color: #fff; padding: 8px 16px; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer; border: none; }
        @media print { .btn-bar { display: none !important; } }
    </style>
</head>
<body>

    <div class="btn-bar">
        <button onclick="window.print()" class="btn">🖨 Print Receipt</button>
        <a href="{{ route('invoices.show', $payment->invoice) }}" class="btn" style="background:#64748b;">Back to Invoice</a>
    </div>

    <div class="receipt-card">
        <!-- Header -->
        <div class="header">
            <div>
                <div class="clinic-name">{{ $settings['clinic_name'] ?? 'MediFlow Central Polyclinic' }}</div>
                <div class="clinic-sub">{{ $settings['clinic_address'] ?? '42 Healthcare Avenue, Medical Enclave' }}</div>
                <div class="clinic-sub">Tel: {{ $settings['clinic_phone'] ?? '+91 11 2345 6789' }}</div>
            </div>
            <div class="receipt-title">
                <h2>PAYMENT RECEIPT</h2>
                <p>{{ $payment->receipt_number }}</p>
                <p style="font-size:10px; color:#94a3b8;">{{ $payment->payment_date->format('d/m/Y h:i A') }}</p>
            </div>
        </div>

        <!-- Details -->
        <table class="info-table">
            <tr>
                <td class="info-label">Received From:</td>
                <td class="info-value">{{ $payment->patient->name }} ({{ $payment->patient->patient_id }})</td>
            </tr>
            <tr>
                <td class="info-label">Against Invoice:</td>
                <td class="info-value font-mono">{{ $payment->invoice->invoice_number }}</td>
            </tr>
            <tr>
                <td class="info-label">Payment Method:</td>
                <td class="info-value">{{ $payment->payment_method }}</td>
            </tr>
            @if($payment->transaction_reference)
                <tr>
                    <td class="info-label">Transaction Ref:</td>
                    <td class="info-value font-mono">{{ $payment->transaction_reference }}</td>
                </tr>
            @endif
            @if($payment->notes)
                <tr>
                    <td class="info-label">Payment Remarks:</td>
                    <td class="info-value">{{ $payment->notes }}</td>
                </tr>
            @endif
        </table>

        <!-- Amount Box -->
        <div class="amount-box">
            <div>
                <div class="label">Amount Received</div>
                <div style="font-size:11px; color:#166534;">Payment acknowledged with thanks</div>
            </div>
            <div class="value">₹{{ number_format($payment->amount, 2) }}</div>
        </div>

        <!-- Invoice Balance Snapshot -->
        <div style="font-size:11px; color:#475569; display:flex; justify-content:space-between; padding:0 4px;">
            <span>Invoice Total: <strong>₹{{ number_format($payment->invoice->grand_total, 2) }}</strong></span>
            <span>Total Paid: <strong>₹{{ number_format($payment->invoice->paid_amount, 2) }}</strong></span>
            <span>Remaining Balance: <strong>₹{{ number_format($payment->invoice->balance_due, 2) }}</strong></span>
        </div>

        <!-- Footer -->
        <div class="footer">
            <div style="font-size:10px; color:#94a3b8;">
                Received By: {{ $payment->receiver->name ?? 'Accounts Desk' }}
            </div>
            <div class="sig-line">
                Cashier / Authorized Signatory
            </div>
        </div>
    </div>

</body>
</html>

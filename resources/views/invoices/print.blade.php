<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice - {{ $invoice->invoice_number }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #0f172a;
            background: #fff;
            padding: 30px;
            max-width: 800px;
            margin: 0 auto;
            font-size: 13px;
            line-height: 1.5;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .clinic-name { font-size: 20px; font-weight: 800; color: #1e3a8a; }
        .clinic-details { font-size: 11px; color: #475569; margin-top: 3px; }
        .invoice-title {
            text-align: right;
        }
        .invoice-title h2 { font-size: 24px; font-weight: 900; color: #0f172a; text-transform: uppercase; }
        .invoice-title p { font-size: 11px; color: #64748b; font-family: monospace; }
        
        .meta-grid {
            display: flex;
            justify-content: space-between;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 12px 16px;
            margin-bottom: 20px;
        }
        .meta-col h4 { font-size: 10px; font-weight: 700; text-transform: uppercase; color: #64748b; margin-bottom: 4px; }
        .meta-col p { font-size: 12px; font-weight: 600; color: #0f172a; }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th {
            background: #f1f5f9;
            color: #334155;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            padding: 8px 12px;
            border-bottom: 2px solid #cbd5e1;
            text-align: left;
        }
        td {
            padding: 8px 12px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 12px;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }

        .summary-container {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 20px;
        }
        .terms-box {
            max-width: 420px;
            font-size: 11px;
            color: #475569;
        }
        .summary-table {
            width: 280px;
        }
        .summary-table td { padding: 4px 8px; border: none; }
        .summary-table tr.total-row td {
            font-size: 14px;
            font-weight: 800;
            border-top: 2px solid #0f172a;
            border-bottom: 2px solid #0f172a;
            padding-top: 8px;
            padding-bottom: 8px;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
        }
        .status-paid { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        .status-partially_paid { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
        .status-unpaid { background: #dbeafe; color: #1e40af; border: 1px solid #bfdbfe; }
        .status-overdue { background: #ffe4e6; color: #9f1239; border: 1px solid #fecdd3; }

        .footer {
            border-top: 1px solid #e2e8f0;
            padding-top: 20px;
            margin-top: 30px;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
        }
        .sig-box {
            text-align: center;
            width: 180px;
        }
        .sig-line {
            border-top: 1px solid #0f172a;
            margin-top: 50px;
            padding-top: 5px;
            font-size: 11px;
            font-weight: 600;
        }
        .btn-bar {
            margin-bottom: 20px;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }
        .btn {
            background: #2563eb;
            color: #fff;
            padding: 8px 16px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            border: none;
        }
        @media print {
            .btn-bar { display: none !important; }
            body { padding: 0; }
        }
    </style>
</head>
<body>

    <div class="btn-bar">
        <button onclick="window.print()" class="btn">🖨 Print Invoice</button>
        <a href="{{ route('invoices.show', $invoice) }}" class="btn" style="background:#64748b;">Back to Invoice</a>
    </div>

    <!-- Header -->
    <div class="header">
        <div>
            <div class="clinic-name">{{ $settings['clinic_name'] ?? 'MediFlow Central Polyclinic' }}</div>
            <div class="clinic-details">{{ $settings['clinic_address'] ?? '42 Healthcare Avenue, Medical Enclave, New Delhi - 110001' }}</div>
            <div class="clinic-details">Phone: {{ $settings['clinic_phone'] ?? '+91 11 2345 6789' }} • Email: {{ $settings['clinic_email'] ?? 'billing@mediflowclinic.com' }}</div>
        </div>
        <div class="invoice-title">
            <h2>INVOICE</h2>
            <p>{{ $invoice->invoice_number }}</p>
            <div style="margin-top:6px;">
                <span class="status-badge status-{{ $invoice->status }}">{{ $invoice->status_label }}</span>
            </div>
        </div>
    </div>

    <!-- Meta Grid -->
    <div class="meta-grid">
        <div class="meta-col">
            <h4>Billed To</h4>
            <p>{{ $invoice->patient->name }}</p>
            <span style="font-size:11px; color:#64748b;">ID: {{ $invoice->patient->patient_id }} • Tel: {{ $invoice->patient->phone }}</span>
        </div>
        <div class="meta-col">
            <h4>Invoice Date</h4>
            <p>{{ $invoice->invoice_date->format('d M Y') }}</p>
        </div>
        <div class="meta-col">
            <h4>Due Date</h4>
            <p>{{ $invoice->due_date ? $invoice->due_date->format('d M Y') : 'Due upon receipt' }}</p>
        </div>
        <div class="meta-col">
            <h4>Attending Doctor</h4>
            <p>{{ $invoice->doctor->name ?? 'General Practice' }}</p>
        </div>
    </div>

    <!-- Line Items Table -->
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Item Description</th>
                <th>Category</th>
                <th class="text-center">Qty</th>
                <th class="text-right">Unit Price</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->items as $idx => $item)
                <tr>
                    <td style="color:#94a3b8; font-weight:600;">{{ $idx + 1 }}</td>
                    <td style="font-weight:600;">{{ $item->description }}</td>
                    <td style="color:#64748b; text-transform:capitalize;">{{ str_replace('_', ' ', $item->item_type) }}</td>
                    <td class="text-center">{{ $item->quantity }}</td>
                    <td class="text-right">₹{{ number_format($item->unit_price, 2) }}</td>
                    <td class="text-right" style="font-weight:700;">₹{{ number_format($item->total, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Summary Section -->
    <div class="summary-container">
        <div class="terms-box">
            <h4 style="font-size:11px; font-weight:700; text-transform:uppercase; color:#0f172a; margin-bottom:4px;">Terms & Conditions:</h4>
            <p>{{ $invoice->terms ?? 'Thank you for choosing our clinic. Please retain this invoice for your medical insurance and tax records.' }}</p>
            @if($invoice->notes)
                <p style="margin-top:6px; font-style:italic;"><strong>Note:</strong> {{ $invoice->notes }}</p>
            @endif
        </div>

        <table class="summary-table">
            <tr>
                <td class="text-right" style="color:#64748b;">Subtotal:</td>
                <td class="text-right" style="font-weight:600;">₹{{ number_format($invoice->subtotal, 2) }}</td>
            </tr>
            @if($invoice->discount_amount > 0)
                <tr>
                    <td class="text-right" style="color:#166534;">Discount:</td>
                    <td class="text-right" style="font-weight:600; color:#166534;">-₹{{ number_format($invoice->discount_amount, 2) }}</td>
                </tr>
            @endif
            @if($invoice->tax_amount > 0)
                <tr>
                    <td class="text-right" style="color:#64748b;">Tax ({{ $invoice->tax_percentage }}%):</td>
                    <td class="text-right" style="font-weight:600;">+₹{{ number_format($invoice->tax_amount, 2) }}</td>
                </tr>
            @endif
            <tr class="total-row">
                <td class="text-right">Grand Total:</td>
                <td class="text-right">₹{{ number_format($invoice->grand_total, 2) }}</td>
            </tr>
            <tr>
                <td class="text-right" style="color:#166534; font-weight:600; padding-top:6px;">Paid Amount:</td>
                <td class="text-right" style="font-weight:700; color:#166534; padding-top:6px;">₹{{ number_format($invoice->paid_amount, 2) }}</td>
            </tr>
            <tr>
                <td class="text-right" style="color:#e11d48; font-weight:700;">Balance Due:</td>
                <td class="text-right" style="font-weight:800; color:#e11d48; font-size:14px;">₹{{ number_format($invoice->balance_due, 2) }}</td>
            </tr>
        </table>
    </div>

    <!-- Settlement Receipts Ledger -->
    @if($invoice->payments->isNotEmpty())
        <div style="margin-top:20px; border-top:1px solid #e2e8f0; padding-top:10px;">
            <h4 style="font-size:10px; font-weight:700; text-transform:uppercase; color:#64748b; margin-bottom:6px;">Payment Settlement Ledger:</h4>
            <table style="font-size:11px; margin-bottom:10px;">
                <thead>
                    <tr>
                        <th style="padding:4px 8px;">Receipt #</th>
                        <th style="padding:4px 8px;">Date</th>
                        <th style="padding:4px 8px;">Mode</th>
                        <th style="padding:4px 8px;">Reference #</th>
                        <th style="padding:4px 8px;" class="text-right">Amount Paid</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($invoice->payments as $p)
                        <tr>
                            <td style="padding:4px 8px; font-family:monospace; font-weight:700;">{{ $p->receipt_number }}</td>
                            <td style="padding:4px 8px;">{{ $p->payment_date->format('d/m/Y h:i A') }}</td>
                            <td style="padding:4px 8px;">{{ $p->payment_method }}</td>
                            <td style="padding:4px 8px; color:#64748b;">{{ $p->transaction_reference ?? '—' }}</td>
                            <td style="padding:4px 8px; font-weight:700; color:#166534;" class="text-right">₹{{ number_format($p->amount, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <!-- Footer -->
    <div class="footer">
        <div style="font-size:10px; color:#94a3b8; max-width:450px;">
            This is a computer-generated billing statement. Please contact administration for billing inquiries.
        </div>
        <div class="sig-box">
            <div class="sig-line">
                Authorized Signatory / Accounts
            </div>
        </div>
    </div>

</body>
</html>

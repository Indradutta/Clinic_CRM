<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Prescription - {{ $prescription->prescription_number }} - {{ $prescription->patient->name }}</title>
    
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #1e293b;
            background: #ffffff;
            font-size: 13px;
            line-height: 1.5;
            padding: 30px;
        }
        .header {
            display: flex;
            justify-content: space-between;
            border-bottom: 2px solid #2563eb;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .clinic-name {
            font-size: 20px;
            font-weight: 700;
            color: #0f172a;
        }
        .clinic-details {
            font-size: 11px;
            color: #64748b;
            margin-top: 4px;
        }
        .doctor-info {
            text-align: right;
        }
        .doctor-name {
            font-size: 15px;
            font-weight: 700;
            color: #0f172a;
        }
        .doctor-sub {
            font-size: 11px;
            color: #64748b;
        }
        .patient-bar {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 12px 16px;
            margin-bottom: 20px;
            font-size: 12px;
        }
        .patient-bar div span:first-child {
            display: block;
            font-size: 10px;
            text-transform: uppercase;
            color: #94a3b8;
            font-weight: 700;
        }
        .patient-bar div span:last-child {
            font-weight: 600;
            color: #0f172a;
        }
        .rx-section {
            margin-bottom: 25px;
        }
        .rx-symbol {
            font-size: 28px;
            font-weight: 700;
            color: #2563eb;
            font-family: serif;
            margin-bottom: 8px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
            margin-bottom: 20px;
        }
        th {
            background: #f1f5f9;
            text-align: left;
            padding: 8px 10px;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 10px;
            color: #475569;
            border-bottom: 1px solid #cbd5e1;
        }
        td {
            padding: 10px;
            border-bottom: 1px solid #e2e8f0;
        }
        .notes-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }
        .notes-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 12px;
        }
        .notes-box h4 {
            font-size: 11px;
            text-transform: uppercase;
            color: #64748b;
            margin-bottom: 6px;
        }
        .footer {
            margin-top: 40px;
            border-top: 1px solid #e2e8f0;
            padding-top: 20px;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
        }
        .signature-box {
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
        .print-btn-bar {
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
            .print-btn-bar { display: none !important; }
            body { padding: 0; }
        }
    </style>
</head>
<body>

    <div class="print-btn-bar">
        <button onclick="window.print()" class="btn">🖨 Print Prescription</button>
        <a href="{{ route('prescriptions.show', $prescription) }}" class="btn" style="background:#64748b;">Back to Prescription</a>
    </div>

    <!-- Clinic & Doctor Header (Sec 8, p. 10) -->
    <div class="header">
        <div>
            <div class="clinic-name">{{ $settings['clinic_name'] ?? 'MediFlow Central Polyclinic' }}</div>
            <div class="clinic-details">{{ $settings['clinic_address'] ?? '42 Healthcare Avenue, Medical Enclave, New Delhi - 110001' }}</div>
            <div class="clinic-details">Phone: {{ $settings['clinic_phone'] ?? '+91 11 2345 6789' }} • Email: {{ $settings['clinic_email'] ?? 'contact@mediflowclinic.com' }}</div>
        </div>
        <div class="doctor-info">
            <div class="doctor-name">{{ $prescription->doctor->name ?? $settings['doctor_name'] }}</div>
            <div class="doctor-sub">{{ $settings['doctor_qualification'] ?? 'MBBS, MD - General Medicine' }}</div>
            <div class="doctor-sub" style="font-weight:600; color:#2563eb;">Reg No: {{ $settings['doctor_registration_number'] ?? 'MCI-482910' }}</div>
        </div>
    </div>

    <!-- Patient Details Bar -->
    <div class="patient-bar">
        <div>
            <span>Patient Name</span>
            <span>{{ $prescription->patient->name }}</span>
        </div>
        <div>
            <span>Patient ID & Blood</span>
            <span>{{ $prescription->patient->patient_id }} ({{ $prescription->patient->blood_group ?? '—' }})</span>
        </div>
        <div>
            <span>Age / Gender</span>
            <span>{{ $prescription->patient->age ?? 'N/A' }} yrs / {{ $prescription->patient->gender }}</span>
        </div>
        <div>
            <span>Date & Rx No</span>
            <span>{{ $prescription->prescription_date->format('d/m/Y') }} ({{ $prescription->prescription_number }})</span>
        </div>
    </div>

    @if($prescription->diagnosis)
        <div style="margin-bottom: 15px; font-size: 12px;">
            <span style="font-weight: 700; color: #475569; text-transform: uppercase; font-size: 10px;">Diagnosis:</span>
            <span style="font-weight: 600; margin-left: 6px;">{{ $prescription->diagnosis }}</span>
        </div>
    @endif

    <!-- Rx Body -->
    <div class="rx-section">
        <div class="rx-symbol">℞</div>

        <table>
            <thead>
                <tr>
                    <th style="width: 5%;">#</th>
                    <th style="width: 35%;">Medicine Name</th>
                    <th style="width: 15%;">Dosage</th>
                    <th style="width: 15%;">Frequency</th>
                    <th style="width: 15%;">Duration</th>
                    <th style="width: 15%;">Timing / Notes</th>
                </tr>
            </thead>
            <tbody>
                @foreach($prescription->items as $idx => $item)
                    <tr>
                        <td>{{ $idx + 1 }}</td>
                        <td><strong>{{ $item->medicine_name }}</strong></td>
                        <td>{{ $item->dosage }}</td>
                        <td>{{ $item->frequency }}</td>
                        <td>{{ $item->duration }}</td>
                        <td>
                            {{ $item->timing }}
                            @if($item->instructions)
                                <br><small style="color:#64748b; font-style:italic;">{{ $item->instructions }}</small>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Tests & Advice Grid -->
    @if($prescription->tests || $prescription->advice)
        <div class="notes-grid">
            @if($prescription->tests)
                <div class="notes-box">
                    <h4>Recommended Investigations / Lab Tests</h4>
                    @if(is_array($prescription->tests))
                        <p>{{ implode(', ', $prescription->tests) }}</p>
                    @else
                        <p>{{ $prescription->tests }}</p>
                    @endif
                </div>
            @endif

            @if($prescription->advice)
                <div class="notes-box">
                    <h4>General Advice & Lifestyle Precautions</h4>
                    <p>{{ $prescription->advice }}</p>
                </div>
            @endif
        </div>
    @endif

    <!-- Follow Up Date -->
    @if($prescription->follow_up_date)
        <div style="font-weight: 600; font-size: 12px; margin-bottom: 20px; color: #1e40af;">
            🗓 Next Review / Follow-up Date: {{ $prescription->follow_up_date->format('d/m/Y') }}
        </div>
    @endif

    <!-- Footer & Signature Block -->
    <div class="footer">
        <div style="font-size: 10px; color: #94a3b8; max-width: 450px;">
            {{ $settings['prescription_footer'] ?? 'Please take medicines strictly according to prescribed dosage. Report immediately in case of any adverse symptoms.' }}
        </div>
        <div class="signature-box">
            <div class="sig-line">
                {{ $prescription->doctor->name ?? $settings['doctor_name'] }}<br>
                <span style="font-size: 10px; font-weight: 400; color: #64748b;">Authorized Signatory</span>
            </div>
        </div>
    </div>

</body>
</html>

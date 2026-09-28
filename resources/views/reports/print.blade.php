<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Practice Report - {{ $clinicName }}</title>
    @vite(['resources/css/app.css'])
    <style>
        @media print {
            .no-print { display: none !important; }
            body { font-size: 11pt; color: #000; background: #fff; }
            table { page-break-inside: auto; }
            tr { page-break-inside: avoid; page-break-after: auto; }
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-900 p-6 md:p-10 font-sans" onload="window.print()">

    <!-- Print / Close Toolbar for Screen -->
    <div class="no-print max-w-5xl mx-auto mb-6 flex items-center justify-between bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
        <div class="flex items-center space-x-2">
            <span class="text-xs font-semibold text-slate-600">Print Preview Ready</span>
            <span class="text-xs px-2 py-0.5 rounded bg-blue-50 text-blue-700 font-bold uppercase">{{ $reportType }} Report</span>
        </div>
        <div class="flex items-center space-x-2">
            <button onclick="window.print()" class="px-4 py-1.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-xs">
                Print Document
            </button>
            <a href="{{ route('reports.index', request()->query()) }}" class="px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold">
                Back to Reports
            </a>
        </div>
    </div>

    <!-- Official Report Document Container -->
    <div class="max-w-5xl mx-auto bg-white p-8 sm:p-10 rounded-2xl border border-slate-200 shadow-xs print:border-none print:shadow-none print:p-0">
        
        <!-- Practice Letterhead Header -->
        <div class="flex justify-between items-start border-b-2 border-slate-900 pb-6 mb-6">
            <div>
                <h1 class="text-2xl font-black text-slate-900 tracking-tight uppercase">{{ $clinicName }}</h1>
                <p class="text-xs font-semibold text-slate-600 mt-1">Official Medical Practice & Analytics Report</p>
                <p class="text-xs text-slate-500">Supervising Physician: {{ $doctorName }}</p>
            </div>
            <div class="text-right">
                <span class="text-xs font-bold uppercase tracking-wider px-2.5 py-1 bg-slate-900 text-white rounded">
                    {{ strtoupper($reportType) }} AUDIT
                </span>
                <p class="text-xs font-bold text-slate-900 mt-2">Report Period: {{ $dateLabel }}</p>
                <p class="text-[10px] text-slate-500 mt-0.5">Generated: {{ date('d M Y, h:i A') }}</p>
            </div>
        </div>

        <!-- Summary KPI Row -->
        <div class="grid grid-cols-3 sm:grid-cols-4 gap-3 mb-6 p-4 bg-slate-50 rounded-xl border border-slate-200 text-xs">
            @if($reportType === 'financial')
                <div>
                    <span class="text-slate-500 uppercase tracking-wider block text-[10px] font-bold">Total Revenue</span>
                    <span class="text-base font-extrabold text-emerald-700">{{ $currencySymbol }}{{ number_format($metrics['total_revenue'], 2) }}</span>
                </div>
                <div>
                    <span class="text-slate-500 uppercase tracking-wider block text-[10px] font-bold">Total Invoiced</span>
                    <span class="text-base font-extrabold text-slate-900">{{ $currencySymbol }}{{ number_format($metrics['total_invoiced'], 2) }}</span>
                </div>
                <div>
                    <span class="text-slate-500 uppercase tracking-wider block text-[10px] font-bold">Outstanding Balance</span>
                    <span class="text-base font-extrabold text-rose-700">{{ $currencySymbol }}{{ number_format($metrics['outstanding_amount'], 2) }}</span>
                </div>
                <div>
                    <span class="text-slate-500 uppercase tracking-wider block text-[10px] font-bold">Settled Invoices</span>
                    <span class="text-base font-extrabold text-blue-700">{{ $metrics['paid_invoices_count'] }}</span>
                </div>
            @elseif($reportType === 'appointments')
                <div>
                    <span class="text-slate-500 uppercase tracking-wider block text-[10px] font-bold">Total Appointments</span>
                    <span class="text-base font-extrabold text-slate-900">{{ $metrics['total_appointments'] }}</span>
                </div>
                <div>
                    <span class="text-slate-500 uppercase tracking-wider block text-[10px] font-bold">Completed Visits</span>
                    <span class="text-base font-extrabold text-emerald-700">{{ $metrics['completed_count'] }}</span>
                </div>
                <div>
                    <span class="text-slate-500 uppercase tracking-wider block text-[10px] font-bold">Completion Rate</span>
                    <span class="text-base font-extrabold text-blue-700">{{ $metrics['completion_rate'] }}%</span>
                </div>
                <div>
                    <span class="text-slate-500 uppercase tracking-wider block text-[10px] font-bold">Cancelled / No-Show</span>
                    <span class="text-base font-extrabold text-rose-700">{{ $metrics['cancelled_count'] + $metrics['no_show_count'] }}</span>
                </div>
            @elseif($reportType === 'patients')
                <div>
                    <span class="text-slate-500 uppercase tracking-wider block text-[10px] font-bold">New Patients</span>
                    <span class="text-base font-extrabold text-blue-700">{{ $metrics['new_patients'] }}</span>
                </div>
                <div>
                    <span class="text-slate-500 uppercase tracking-wider block text-[10px] font-bold">Returning Patients</span>
                    <span class="text-base font-extrabold text-indigo-700">{{ $metrics['returning_patients'] }}</span>
                </div>
                <div>
                    <span class="text-slate-500 uppercase tracking-wider block text-[10px] font-bold">Total Registered</span>
                    <span class="text-base font-extrabold text-slate-900">{{ $metrics['total_registered'] }}</span>
                </div>
            @elseif($reportType === 'prescriptions')
                <div>
                    <span class="text-slate-500 uppercase tracking-wider block text-[10px] font-bold">Total Scripts</span>
                    <span class="text-base font-extrabold text-teal-700">{{ $metrics['total_prescriptions'] }}</span>
                </div>
                <div>
                    <span class="text-slate-500 uppercase tracking-wider block text-[10px] font-bold">Patients Treated</span>
                    <span class="text-base font-extrabold text-slate-900">{{ $metrics['distinct_patients'] }}</span>
                </div>
            @endif
        </div>

        <!-- Ledger Table -->
        <table class="w-full text-left text-xs border border-slate-200">
            <thead class="bg-slate-100 uppercase text-[10px] font-bold text-slate-700 border-b border-slate-200">
                @if($reportType === 'financial')
                    <tr>
                        <th class="p-2 border-r border-slate-200">Invoice #</th>
                        <th class="p-2 border-r border-slate-200">Date</th>
                        <th class="p-2 border-r border-slate-200">Patient</th>
                        <th class="p-2 border-r border-slate-200">Doctor</th>
                        <th class="p-2 text-right border-r border-slate-200">Grand Total</th>
                        <th class="p-2 text-right border-r border-slate-200">Paid</th>
                        <th class="p-2 text-right border-r border-slate-200">Balance</th>
                        <th class="p-2 text-center">Status</th>
                    </tr>
                @elseif($reportType === 'appointments')
                    <tr>
                        <th class="p-2 border-r border-slate-200">APT #</th>
                        <th class="p-2 border-r border-slate-200">Date & Time</th>
                        <th class="p-2 border-r border-slate-200">Patient</th>
                        <th class="p-2 border-r border-slate-200">Type</th>
                        <th class="p-2 border-r border-slate-200">Doctor</th>
                        <th class="p-2 text-center">Status</th>
                    </tr>
                @elseif($reportType === 'patients')
                    <tr>
                        <th class="p-2 border-r border-slate-200">Patient ID</th>
                        <th class="p-2 border-r border-slate-200">Name</th>
                        <th class="p-2 border-r border-slate-200">Phone</th>
                        <th class="p-2 border-r border-slate-200">Gender / Age</th>
                        <th class="p-2 border-r border-slate-200">Registered Date</th>
                        <th class="p-2 text-right">Visits</th>
                    </tr>
                @elseif($reportType === 'prescriptions')
                    <tr>
                        <th class="p-2 border-r border-slate-200">Rx #</th>
                        <th class="p-2 border-r border-slate-200">Date</th>
                        <th class="p-2 border-r border-slate-200">Patient</th>
                        <th class="p-2 border-r border-slate-200">Diagnosis</th>
                        <th class="p-2 border-r border-slate-200">Doctor</th>
                        <th class="p-2 text-right">Items</th>
                    </tr>
                @endif
            </thead>
            <tbody class="divide-y divide-slate-200">
                @if($reportType === 'financial')
                    @foreach($records as $inv)
                        <tr>
                            <td class="p-2 font-mono font-bold border-r border-slate-200">{{ $inv->invoice_number }}</td>
                            <td class="p-2 border-r border-slate-200">{{ $inv->invoice_date?->format('Y-m-d') }}</td>
                            <td class="p-2 border-r border-slate-200 font-semibold">{{ $inv->patient->name ?? 'N/A' }}</td>
                            <td class="p-2 border-r border-slate-200">{{ $inv->doctor->name ?? 'N/A' }}</td>
                            <td class="p-2 text-right font-bold border-r border-slate-200">{{ $currencySymbol }}{{ number_format($inv->grand_total, 2) }}</td>
                            <td class="p-2 text-right text-emerald-700 font-bold border-r border-slate-200">{{ $currencySymbol }}{{ number_format($inv->paid_amount, 2) }}</td>
                            <td class="p-2 text-right font-bold border-r border-slate-200">{{ $currencySymbol }}{{ number_format($inv->balance_due, 2) }}</td>
                            <td class="p-2 text-center uppercase font-bold text-[10px]">{{ $inv->status }}</td>
                        </tr>
                    @endforeach
                @elseif($reportType === 'appointments')
                    @foreach($records as $apt)
                        <tr>
                            <td class="p-2 font-mono font-bold border-r border-slate-200">{{ $apt->appointment_id }}</td>
                            <td class="p-2 border-r border-slate-200">{{ $apt->appointment_date?->format('Y-m-d') }} {{ $apt->appointment_time }}</td>
                            <td class="p-2 border-r border-slate-200 font-semibold">{{ $apt->patient->name ?? 'N/A' }}</td>
                            <td class="p-2 border-r border-slate-200">{{ $apt->appointment_type }}</td>
                            <td class="p-2 border-r border-slate-200">{{ $apt->doctor->name ?? 'N/A' }}</td>
                            <td class="p-2 text-center capitalize">{{ $apt->status_label }}</td>
                        </tr>
                    @endforeach
                @elseif($reportType === 'patients')
                    @foreach($records as $pat)
                        <tr>
                            <td class="p-2 font-mono font-bold border-r border-slate-200">{{ $pat->patient_id }}</td>
                            <td class="p-2 border-r border-slate-200 font-semibold">{{ $pat->name }}</td>
                            <td class="p-2 border-r border-slate-200">{{ $pat->phone ?: '—' }}</td>
                            <td class="p-2 border-r border-slate-200">{{ $pat->gender ?: 'N/A' }} • {{ $pat->age ?: '—' }}</td>
                            <td class="p-2 border-r border-slate-200">{{ $pat->created_at?->format('Y-m-d') }}</td>
                            <td class="p-2 text-right font-bold">{{ $pat->appointments_count }}</td>
                        </tr>
                    @endforeach
                @elseif($reportType === 'prescriptions')
                    @foreach($records as $rx)
                        <tr>
                            <td class="p-2 font-mono font-bold border-r border-slate-200">{{ $rx->prescription_number }}</td>
                            <td class="p-2 border-r border-slate-200">{{ $rx->prescription_date?->format('Y-m-d') }}</td>
                            <td class="p-2 border-r border-slate-200 font-semibold">{{ $rx->patient->name ?? 'N/A' }}</td>
                            <td class="p-2 border-r border-slate-200">{{ $rx->diagnosis }}</td>
                            <td class="p-2 border-r border-slate-200">{{ $rx->doctor->name ?? 'N/A' }}</td>
                            <td class="p-2 text-right font-bold">{{ $rx->items->count() }}</td>
                        </tr>
                    @endforeach
                @endif
            </tbody>
        </table>

        <!-- Sign-off Footer -->
        <div class="mt-12 pt-6 border-t border-slate-300 flex justify-between items-end text-xs text-slate-500">
            <div>
                <p class="font-bold text-slate-700">Official Clinical Audit Document</p>
                <p>Generated by MediFlow CRM. Confidential healthcare information.</p>
            </div>
            <div class="text-center">
                <div class="w-48 border-b border-slate-400 mb-1"></div>
                <p class="font-semibold text-slate-700">Authorized Signature</p>
            </div>
        </div>

    </div>

</body>
</html>

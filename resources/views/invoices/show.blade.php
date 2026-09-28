@extends('layouts.app')

@section('title', 'Invoice ' . $invoice->invoice_number)

@section('content')
<div class="space-y-8">

    <!-- Top Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex flex-wrap items-center gap-2.5 mb-2">
                <span class="text-xs font-mono font-bold text-brand-700 bg-brand-50 px-3 py-1 rounded-lg border border-brand-200/80">
                    {{ $invoice->invoice_number }}
                </span>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold border whitespace-nowrap shadow-2xs {{ $invoice->status_badge_class }}">
                    <span class="w-1.5 h-1.5 rounded-full shrink-0 {{ $invoice->status === 'paid' ? 'bg-emerald-500' : ($invoice->status === 'partially_paid' ? 'bg-amber-500' : ($invoice->status === 'overdue' ? 'bg-rose-500' : ($invoice->status === 'cancelled' ? 'bg-slate-400' : 'bg-blue-500'))) }}"></span>
                    <span>{{ $invoice->status_label }}</span>
                </span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-slate-900">Billing Statement</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">
                Issued {{ $invoice->invoice_date->format('F d, Y') }} • Due: {{ $invoice->due_date ? $invoice->due_date->format('F d, Y') : 'Immediate' }}
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            <a href="{{ route('invoices.print', $invoice) }}" target="_blank" 
               class="btn-pill-secondary inline-flex items-center gap-1.5 text-xs">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                </svg>
                <span>Print Invoice</span>
            </a>
            @can('invoices.edit')
            <a href="{{ route('invoices.edit', $invoice) }}" 
               class="btn-pill-secondary text-xs">
                Edit
            </a>
            @endcan
            <a href="{{ route('invoices.index') }}" 
               class="btn-pill-secondary text-xs">
                &larr; Invoices
            </a>
        </div>
    </div>

    <!-- Main Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 sm:gap-8">

        <!-- Left 2 Cols: Invoice Data & Itemized Charges -->
        <div class="lg:col-span-2 space-y-6 sm:space-y-8">

            <!-- Patient & Bill Context Card -->
            <div class="glass-card-elevated rounded-2xl p-6 sm:p-8 space-y-5">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h2 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Patient & Provider Information</h2>
                    @can('patients.view')
                        <a href="{{ route('patients.show', $invoice->patient) }}" class="text-xs font-bold text-brand-600 hover:text-brand-700 hover:underline inline-flex items-center gap-1">
                            <span>Patient Profile</span> ↗
                        </a>
                    @endcan
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 text-xs">
                    <div class="space-y-1">
                        <span class="text-slate-400 block font-medium uppercase text-[10px] tracking-wider">Billed Patient</span>
                        <p class="font-extrabold text-slate-900 text-base">{{ $invoice->patient->name }}</p>
                        <p class="text-slate-500 font-mono text-[11px]">{{ $invoice->patient->patient_id }} • {{ $invoice->patient->gender }}, {{ $invoice->patient->age ?? 'N/A' }} yrs</p>
                        <p class="text-slate-600 mt-1">{{ $invoice->patient->phone }} • {{ $invoice->patient->email ?? 'No email' }}</p>
                    </div>

                    <div class="space-y-1">
                        <span class="text-slate-400 block font-medium uppercase text-[10px] tracking-wider">Attending Clinician / Center</span>
                        <p class="font-extrabold text-slate-900 text-base">{{ $invoice->doctor->name ?? $settings['clinic_name'] ?? 'MediFlow Polyclinic' }}</p>
                        <p class="text-slate-500 text-xs">{{ $settings['clinic_address'] ?? 'Central Medical Enclave' }}</p>
                        @if($invoice->appointment)
                            <div class="pt-1">
                                <span class="text-[11px] font-mono font-semibold text-brand-700 bg-brand-50 px-2.5 py-0.5 rounded-md">
                                    Appt Ref: {{ $invoice->appointment->appointment_id }}
                                </span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Itemized Line Charges -->
            <div class="glass-card-elevated rounded-2xl p-6 sm:p-8 space-y-6">
                <div class="border-b border-slate-100 pb-3">
                    <h2 class="text-xs font-bold text-slate-900 uppercase tracking-wider">
                        Itemized Billing Charges
                    </h2>
                </div>

                <div class="overflow-x-auto rounded-2xl border border-slate-100">
                    <table class="w-full text-left text-xs text-slate-700">
                        <thead class="bg-slate-50/80 text-[11px] uppercase font-bold text-slate-400 border-b border-slate-100">
                            <tr>
                                <th class="px-5 py-3.5">#</th>
                                <th class="px-4 py-3.5">Description</th>
                                <th class="px-3 py-3.5">Category</th>
                                <th class="px-3 py-3.5 text-center">Qty</th>
                                <th class="px-4 py-3.5 text-right">Unit Price</th>
                                <th class="px-5 py-3.5 text-right">Line Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($invoice->items as $idx => $item)
                                <tr class="hover:bg-slate-50/50 transition">
                                    <td class="px-5 py-3.5 font-semibold text-slate-400">{{ $idx + 1 }}</td>
                                    <td class="px-4 py-3.5 font-bold text-slate-900">{{ $item->description }}</td>
                                    <td class="px-3 py-3.5 capitalize">
                                        <span class="px-2.5 py-0.5 rounded-md bg-slate-100 text-slate-600 text-[10px] font-semibold">
                                            {{ str_replace('_', ' ', $item->item_type) }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-3.5 text-center font-semibold text-slate-700">{{ $item->quantity }}</td>
                                    <td class="px-4 py-3.5 text-right font-medium">₹{{ number_format($item->unit_price, 2) }}</td>
                                    <td class="px-5 py-3.5 text-right font-extrabold text-slate-900">₹{{ number_format($item->total, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Financial Math Summary -->
                <div class="flex justify-end pt-2">
                    <div class="w-full sm:w-80 space-y-2.5 text-xs divide-y divide-slate-100/80">
                        <div class="flex justify-between py-1.5">
                            <span class="text-slate-500 font-medium">Items Subtotal:</span>
                            <span class="font-bold text-slate-800">₹{{ number_format($invoice->subtotal, 2) }}</span>
                        </div>
                        @if($invoice->discount_amount > 0)
                            <div class="flex justify-between py-1.5 text-emerald-700">
                                <span class="font-medium">Discount Applied:</span>
                                <span class="font-bold">-₹{{ number_format($invoice->discount_amount, 2) }}</span>
                            </div>
                        @endif
                        @if($invoice->tax_amount > 0)
                            <div class="flex justify-between py-1.5">
                                <span class="text-slate-500 font-medium">Tax ({{ $invoice->tax_percentage }}%):</span>
                                <span class="font-bold text-slate-800">+₹{{ number_format($invoice->tax_amount, 2) }}</span>
                            </div>
                        @endif
                        <div class="flex justify-between py-2 text-sm border-t-2 border-slate-900">
                            <span class="font-extrabold text-slate-900">Grand Total:</span>
                            <span class="font-black text-slate-900">₹{{ number_format($invoice->grand_total, 2) }}</span>
                        </div>
                        <div class="flex justify-between py-1.5 text-emerald-700">
                            <span class="font-medium">Paid to Date:</span>
                            <span class="font-bold">₹{{ number_format($invoice->paid_amount, 2) }}</span>
                        </div>
                        <div class="flex justify-between py-2 text-sm font-extrabold {{ $invoice->balance_due > 0 ? 'text-rose-600' : 'text-slate-400' }}">
                            <span>Balance Due:</span>
                            <span>₹{{ number_format($invoice->balance_due, 2) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Notes & Terms -->
                @if($invoice->notes || $invoice->terms)
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs pt-4 border-t border-slate-100">
                        @if($invoice->notes)
                            <div class="p-4 bg-slate-50/80 rounded-2xl border border-slate-100">
                                <span class="font-bold text-[10px] uppercase text-slate-400 block mb-1">Notes:</span>
                                <p class="text-slate-700 leading-relaxed">{{ $invoice->notes }}</p>
                            </div>
                        @endif
                        @if($invoice->terms)
                            <div class="p-4 bg-slate-50/80 rounded-2xl border border-slate-100">
                                <span class="font-bold text-[10px] uppercase text-slate-400 block mb-1">Terms:</span>
                                <p class="text-slate-700 leading-relaxed">{{ $invoice->terms }}</p>
                            </div>
                        @endif
                    </div>
                @endif
            </div>

        </div>

        <!-- Right Column: Payments Ledger & Record Payment -->
        <div class="space-y-6 sm:space-y-8">

            <!-- Balance Due Widget -->
            <div class="glass-card-elevated rounded-2xl p-6 sm:p-7 space-y-3">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block">Settlement Status</span>
                @if($invoice->balance_due > 0)
                    <div class="p-5 bg-rose-50/90 border border-rose-200/80 rounded-2xl">
                        <span class="text-[10px] font-bold uppercase text-rose-700 tracking-wider block">Outstanding Balance</span>
                        <div class="text-3xl font-black text-rose-700 mt-1">₹{{ number_format($invoice->balance_due, 2) }}</div>
                        <p class="text-xs text-rose-600 mt-1.5 font-medium">Payment is required to settle this account statement.</p>
                    </div>
                @else
                    <div class="p-5 bg-emerald-50/90 border border-emerald-200/80 rounded-2xl">
                        <span class="text-[10px] font-bold uppercase text-emerald-700 tracking-wider block">Fully Settled</span>
                        <div class="text-3xl font-black text-emerald-700 mt-1">₹0.00 Due</div>
                        <p class="text-xs text-emerald-600 mt-1.5 font-medium">All dues on this invoice have been paid in full.</p>
                    </div>
                @endif
            </div>

            <!-- Record Payment Form (if balance > 0) -->
            @can('invoices.payment_management')
            @if($invoice->balance_due > 0 && $invoice->status !== \App\Models\Invoice::STATUS_CANCELLED)
                <div class="glass-card-elevated rounded-2xl p-6 sm:p-7 space-y-5">
                    <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider border-b border-slate-100 pb-3 flex items-center gap-2">
                        <span>💳</span>
                        <span>Record Payment Transaction</span>
                    </h3>

                    <form method="POST" action="{{ route('payments.store') }}" class="space-y-3.5">
                        @csrf
                        <input type="hidden" name="invoice_id" value="{{ $invoice->id }}">

                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">Amount to Pay (₹) *</label>
                            <input type="number" step="0.01" min="0.01" max="{{ $invoice->balance_due }}" name="amount" required
                                   value="{{ $invoice->balance_due }}"
                                   class="apple-input w-full font-bold text-slate-900 text-xs py-2">
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">Payment Mode *</label>
                            <select name="payment_method" required class="apple-input w-full text-xs py-2">
                                <option value="Cash">Cash</option>
                                <option value="UPI">UPI / QR Code</option>
                                <option value="Card">Card (Debit / Credit)</option>
                                <option value="Bank Transfer">Bank Transfer / NEFT</option>
                                <option value="Insurance">Insurance / TPA</option>
                                <option value="Cheque">Cheque</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">Transaction Ref #</label>
                            <input type="text" name="transaction_reference" placeholder="e.g. UPI Ref / Auth code / Cheque #"
                                   class="apple-input w-full text-xs py-2">
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">Payment Date *</label>
                            <input type="datetime-local" name="payment_date" required value="{{ now()->format('Y-m-d\TH:i') }}"
                                   class="apple-input w-full text-xs py-2">
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">Notes</label>
                            <input type="text" name="notes" placeholder="Optional notes.."
                                   class="apple-input w-full text-xs py-2">
                        </div>

                        <button type="submit" 
                                class="btn-pill-primary w-full py-3 text-center justify-center font-bold text-xs mt-3">
                            Log Payment Settlement
                        </button>
                    </form>
                </div>
            @endif
            @endcan

            <!-- Payments Ledger / Receipts History -->
            <div class="glass-card-elevated rounded-2xl p-6 sm:p-7 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Payment Ledger ({{ $invoice->payments->count() }})</h3>
                </div>

                @if($invoice->payments->isEmpty())
                    <div class="text-center py-8 text-slate-400 text-xs">
                        <p>No payments recorded yet.</p>
                    </div>
                @else
                    <div class="space-y-3">
                        @foreach($invoice->payments as $pay)
                            <div class="glass-card p-4 rounded-2xl space-y-2 text-xs hover:border-slate-300 transition">
                                <div class="flex justify-between items-center">
                                    <div class="flex items-center gap-2">
                                        <span class="font-mono font-bold text-slate-900">{{ $pay->receipt_number }}</span>
                                        <span class="px-2.5 py-0.5 rounded-md text-[10px] font-bold border {{ $pay->method_badge_class }}">
                                            {{ $pay->payment_method }}
                                        </span>
                                    </div>
                                    <span class="font-extrabold text-emerald-700 text-sm">₹{{ number_format($pay->amount, 2) }}</span>
                                </div>
                                <div class="flex justify-between items-center text-[11px] text-slate-500">
                                    <span>{{ $pay->payment_date->format('M d, Y • h:i A') }}</span>
                                    <div class="flex items-center gap-3">
                                        @can('invoices.payment_management')
                                        <a href="{{ route('payments.receipt', $pay) }}" target="_blank" 
                                           class="text-brand-600 hover:text-brand-700 font-bold hover:underline">
                                            Receipt ↗
                                        </a>
                                        <form method="POST" action="{{ route('payments.destroy', $pay) }}" onsubmit="return confirm('Void this payment transaction? This will restore the balance on the invoice.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-rose-500 hover:text-rose-700 font-bold" title="Void Payment">
                                                ✕
                                            </button>
                                        </form>
                                        @endcan
                                    </div>
                                </div>
                                @if($pay->transaction_reference)
                                    <span class="text-[10px] text-slate-400 font-mono block">Ref: {{ $pay->transaction_reference }}</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

        </div>

    </div>

</div>
@endsection

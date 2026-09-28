@extends('layouts.app')

@section('title', 'Generate Invoice')

@section('content')
<div class="space-y-8" x-data="{
    discount_type: 'fixed',
    discount_value: 0,
    tax_percentage: 0,
    items: [
        { description: 'General Specialist Consultation', item_type: 'consultation', quantity: 1, unit_price: 500, total: 500 }
    ],
    addItem() {
        this.items.push({ description: '', item_type: 'procedure', quantity: 1, unit_price: 0, total: 0 });
    },
    removeItem(index) {
        if (this.items.length > 1) {
            this.items.splice(index, 1);
        }
    },
    updateRowTotal(index) {
        let q = parseFloat(this.items[index].quantity) || 0;
        let p = parseFloat(this.items[index].unit_price) || 0;
        this.items[index].total = (q * p).toFixed(2);
    },
    subtotal() {
        return this.items.reduce((acc, it) => acc + (parseFloat(it.total) || 0), 0);
    },
    discountAmount() {
        let sub = this.subtotal();
        let val = parseFloat(this.discount_value) || 0;
        if (this.discount_type === 'percentage') {
            return (sub * (val / 100));
        }
        return Math.min(val, sub);
    },
    taxAmount() {
        let taxable = Math.max(0, this.subtotal() - this.discountAmount());
        let rate = parseFloat(this.tax_percentage) || 0;
        return (taxable * (rate / 100));
    },
    grandTotal() {
        let val = (this.subtotal() - this.discountAmount() + this.taxAmount());
        return Math.max(0, val);
    }
}">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-lg bg-brand-50/80 border border-brand-100 text-brand-700 text-xs font-semibold mb-2">
                <span>Billing Workflow</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-slate-900">Create Patient Invoice</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">Itemized professional charges, treatments, diagnostic tests, and automated billing calculation.</p>
        </div>
        <a href="{{ route('invoices.index') }}" class="btn-pill-secondary self-start sm:self-auto inline-flex items-center gap-2">
            &larr; <span>Back to Invoices</span>
        </a>
    </div>

    <form method="POST" action="{{ route('invoices.store') }}" class="space-y-8" novalidate>
        @csrf
        @if($appointment)
            <input type="hidden" name="appointment_id" value="{{ $appointment->id }}">
        @endif
        @if($prescription)
            <input type="hidden" name="prescription_id" value="{{ $prescription->id }}">
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 sm:gap-8">

            <!-- Left 2 Cols: Invoice Details & Items -->
            <div class="lg:col-span-2 space-y-6 sm:space-y-8">

                <!-- 1. Metadata -->
                <div class="glass-card-elevated rounded-2xl p-6 sm:p-8 space-y-6">
                    <div class="flex items-center gap-3 border-b border-slate-100 pb-4">
                        <div class="w-9 h-9 rounded-xl bg-brand-50 text-brand-600 flex items-center justify-center font-bold">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider">1. Patient & Invoice Information</h2>
                            <p class="text-xs text-slate-400">Specify patient, attending clinician, and billing schedule</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-5">
                        <!-- Patient Selection -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Select Patient *</label>
                            @if($selectedPatient)
                                <input type="hidden" name="patient_id" value="{{ $selectedPatient->id }}">
                                <div class="px-4 py-3 bg-brand-50/60 border border-brand-200/80 rounded-2xl text-xs text-slate-900 flex justify-between items-center">
                                    <div>
                                        <span class="font-bold text-brand-900">{{ $selectedPatient->name }}</span>
                                        <span class="text-brand-600 font-mono text-[11px] block">{{ $selectedPatient->patient_id }}</span>
                                    </div>
                                    <span class="text-slate-500 font-mono text-xs">{{ $selectedPatient->phone }}</span>
                                </div>
                            @else
                                <select name="patient_id" required class="apple-input w-full">
                                    <option value="">-- Choose Patient --</option>
                                    @foreach($patients as $p)
                                        <option value="{{ $p->id }}" {{ old('patient_id') == $p->id ? 'selected' : '' }}>
                                            {{ $p->name }} ({{ $p->patient_id }}) - {{ $p->phone }}
                                        </option>
                                    @endforeach
                                </select>
                        @error('patient_id') <p class="text-[11px] text-rose-500 mt-1 font-medium">{{ $message }}</p> @enderror
                    @endif
                </div>

                <!-- Attending Doctor (Single Doctor Rule) -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Attending Doctor</label>
                            @if(isset($doctors) && $doctors->count() === 1)
                                @php $singleDoc = $doctors->first(); @endphp
                                <input type="hidden" name="doctor_id" value="{{ $singleDoc->id }}">
                                <div class="px-4 py-3 bg-slate-50/90 border border-slate-200/80 rounded-2xl text-xs text-slate-800 flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 rounded-full bg-brand-100 text-brand-700 flex items-center justify-center font-bold text-[10px]">
                                            Dr
                                        </div>
                                        <span class="font-bold">{{ $singleDoc->name }}</span>
                                    </div>
                                    <span class="text-[10px] font-semibold text-brand-600 bg-brand-50 px-2 py-0.5 rounded-md uppercase tracking-wider">Primary</span>
                                </div>
                            @else
                                <select name="doctor_id" class="apple-input w-full">
                                    <option value="">-- General Practice / Clinic --</option>
                                    @foreach($doctors as $doc)
                                        <option value="{{ $doc->id }}" {{ old('doctor_id', $appointment->doctor_id ?? auth()->id()) == $doc->id ? 'selected' : '' }}>
                                            {{ $doc->name }} ({{ ucfirst(str_replace('_', ' ', $doc->role)) }})
                                        </option>
                                    @endforeach
                                </select>
                            @endif
                        </div>

                        <!-- Invoice Date -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Invoice Date *</label>
                            <input type="date" name="invoice_date" required value="{{ old('invoice_date', now()->toDateString()) }}"
                                   class="apple-input w-full">
                    @error('invoice_date') <p class="text-[11px] text-rose-500 mt-1 font-medium">{{ $message }}</p> @enderror
                </div>

                <!-- Due Date -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Payment Due Date</label>
                            <input type="date" name="due_date" value="{{ old('due_date', now()->addDays(7)->toDateString()) }}"
                                   class="apple-input w-full">
                    @error('due_date') <p class="text-[11px] text-rose-500 mt-1 font-medium">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <!-- 2. Line Items Repeater -->
                <div class="glass-card-elevated rounded-2xl p-6 sm:p-8 space-y-6">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"></path>
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider">2. Itemized Clinical & Service Charges</h2>
                                <p class="text-xs text-slate-400">Add medical consultations, diagnostic tests, and procedures</p>
                            </div>
                        </div>
                        <button type="button" @click="addItem()" 
                                class="btn-pill-secondary inline-flex items-center gap-1.5 text-xs py-2 px-3.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            <span>Add Item</span>
                        </button>
                    </div>

                    <!-- Items List -->
                    <div class="space-y-3">
                        <template x-for="(item, index) in items" :key="index">
                            <div class="glass-card rounded-2xl p-4 grid grid-cols-1 sm:grid-cols-12 gap-3 items-center hover:border-slate-300 transition">
                                
                                <!-- Description -->
                                <div class="sm:col-span-5">
                                    <label class="block text-[10px] uppercase font-bold text-slate-400 mb-1">Item Description *</label>
                                    <input type="text" :name="'items[' + index + '][description]'" x-model="item.description" required placeholder="e.g. ECG, Blood Test, Doctor Consultation"
                                           class="apple-input w-full text-xs py-2">
                                </div>

                                <!-- Type -->
                                <div class="sm:col-span-2">
                                    <label class="block text-[10px] uppercase font-bold text-slate-400 mb-1">Type</label>
                                    <select :name="'items[' + index + '][item_type]'" x-model="item.item_type"
                                            class="apple-input w-full text-xs py-2">
                                        <option value="consultation">Consultation</option>
                                        <option value="procedure">Procedure</option>
                                        <option value="medicine">Medicine</option>
                                        <option value="lab_test">Lab Test</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>

                                <!-- Qty -->
                                <div class="sm:col-span-1">
                                    <label class="block text-[10px] uppercase font-bold text-slate-400 mb-1 text-center">Qty</label>
                                    <input type="number" step="0.1" min="0.1" :name="'items[' + index + '][quantity]'" x-model="item.quantity" @input="updateRowTotal(index)" required
                                           class="apple-input w-full text-xs py-2 text-center font-bold">
                                </div>

                                <!-- Unit Price -->
                                <div class="sm:col-span-2">
                                    <label class="block text-[10px] uppercase font-bold text-slate-400 mb-1 text-right">Price (₹)</label>
                                    <input type="number" step="0.01" min="0" :name="'items[' + index + '][unit_price]'" x-model="item.unit_price" @input="updateRowTotal(index)" required
                                           class="apple-input w-full text-xs py-2 text-right font-bold">
                                </div>

                                <!-- Total & Delete -->
                                <div class="sm:col-span-2 flex items-center justify-between pl-2">
                                    <div class="text-right">
                                        <span class="block text-[10px] uppercase font-bold text-slate-400">Total</span>
                                        <span class="font-extrabold text-xs text-slate-900" x-text="'₹' + item.total"></span>
                                    </div>
                                    <button type="button" @click="removeItem(index)" x-show="items.length > 1" 
                                            class="w-7 h-7 rounded-full bg-rose-50 text-rose-500 hover:bg-rose-100 flex items-center justify-center text-xs font-bold transition ml-2" title="Remove item">
                                        ✕
                                    </button>
                                </div>

                            </div>
                        </template>
                    </div>

                    <!-- Notes & Terms -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-5 pt-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Invoice Notes</label>
                            <textarea name="notes" rows="3" placeholder="Clinical notes, package inclusions, or patient remarks.."
                                      class="apple-input w-full leading-relaxed">{{ old('notes') }}</textarea>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Terms & Conditions</label>
                            <textarea name="terms" rows="3" 
                                      class="apple-input w-full leading-relaxed">{{ old('terms', $defaultTerms) }}</textarea>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Right Column: Financial Calculation & Settlement -->
            <div class="space-y-6 sm:space-y-8">

                <!-- Calculation Summary Card -->
                <div class="glass-card-elevated rounded-2xl p-6 sm:p-7 space-y-5">
                    <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider border-b border-slate-100 pb-3 flex items-center justify-between">
                        <span>Computation & Billing</span>
                        <span class="text-[10px] font-semibold text-brand-600 bg-brand-50 px-2.5 py-0.5 rounded-md">Auto-calc</span>
                    </h3>

                    <!-- Subtotal -->
                    <div class="flex justify-between items-center text-xs">
                        <span class="text-slate-500 font-medium">Items Subtotal:</span>
                        <span class="font-extrabold text-slate-900" x-text="'₹' + subtotal().toFixed(2)"></span>
                    </div>

                    <!-- Discount Section -->
                    <div class="space-y-2 pt-3 border-t border-slate-100 text-xs">
                        <div class="flex justify-between items-center">
                            <span class="text-slate-500 font-semibold">Discount:</span>
                            <span class="font-bold text-emerald-600" x-text="'-₹' + discountAmount().toFixed(2)"></span>
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <select name="discount_type" x-model="discount_type" class="apple-input text-xs py-2">
                                <option value="fixed">Fixed (₹)</option>
                                <option value="percentage">Percentage (%)</option>
                            </select>
                            <input type="number" step="0.01" min="0" name="discount_value" x-model="discount_value" placeholder="0.00"
                                   class="apple-input text-xs py-2 text-right font-medium">
                        </div>
                    </div>

                    <!-- Tax Section -->
                    <div class="space-y-2 pt-3 border-t border-slate-100 text-xs">
                        <div class="flex justify-between items-center">
                            <span class="text-slate-500 font-semibold">Tax / GST:</span>
                            <span class="font-bold text-slate-900" x-text="'+₹' + taxAmount().toFixed(2)"></span>
                        </div>
                        <div class="flex items-center gap-2">
                            <input type="number" step="0.01" min="0" max="100" name="tax_percentage" x-model="tax_percentage" placeholder="0"
                                   class="apple-input w-24 text-xs py-2 text-center font-bold">
                            <span class="text-xs text-slate-400 font-medium">% tax rate</span>
                        </div>
                    </div>

                    <!-- Grand Total Banner -->
                    <div class="p-5 bg-gradient-to-br from-slate-900 to-slate-800 text-white rounded-2xl shadow-lg shadow-slate-900/10 space-y-1">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-brand-300">Grand Total Payable</span>
                        <div class="text-3xl font-extrabold tracking-tight" x-text="'₹' + grandTotal().toFixed(2)"></div>
                    </div>

                    <!-- Immediate Settlement Option -->
                    <div class="pt-3 border-t border-slate-100 space-y-3" x-data="{ logPaymentNow: false }">
                        <label class="flex items-center gap-2.5 cursor-pointer">
                            <input type="checkbox" id="logPay" x-model="logPaymentNow" class="rounded-lg text-brand-600 focus:ring-brand-500 w-4 h-4 border-slate-300">
                            <span class="text-xs font-bold text-slate-800">Record Immediate Payment</span>
                        </label>

                        <div x-show="logPaymentNow" x-cloak class="space-y-3 glass-card p-4 rounded-2xl text-xs border border-brand-100 bg-brand-50/30">
                            <div>
                                <label class="block text-[10px] uppercase font-bold text-slate-500 mb-1">Amount Paid (₹)</label>
                                <input type="number" step="0.01" min="0" name="initial_payment_amount" :value="grandTotal().toFixed(2)"
                                       class="apple-input w-full text-xs py-2 font-bold text-emerald-700">
                            </div>
                            <div>
                                <label class="block text-[10px] uppercase font-bold text-slate-500 mb-1">Payment Method</label>
                                <select name="initial_payment_method" class="apple-input w-full text-xs py-2">
                                    <option value="Cash">Cash</option>
                                    <option value="UPI">UPI / QR Code</option>
                                    <option value="Card">Card (Debit / Credit)</option>
                                    <option value="Bank Transfer">Bank Transfer / NEFT</option>
                                    <option value="Insurance">Insurance / TPA</option>
                                    <option value="Cheque">Cheque</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[10px] uppercase font-bold text-slate-500 mb-1">Reference / Transaction ID</label>
                                <input type="text" name="initial_payment_reference" placeholder="e.g. UPI Ref / Auth code"
                                       class="apple-input w-full text-xs py-2">
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="pt-3">
                        <button type="submit" 
                                class="btn-pill-primary w-full py-3.5 text-center justify-center font-bold text-sm">
                            Generate & Finalize Invoice
                        </button>
                    </div>

                </div>

            </div>

        </div>
    </form>
</div>
@endsection


@extends('layouts.app')

@section('title', 'Edit Invoice ' . $invoice->invoice_number)

@section('content')
<div class="space-y-8" x-data="{
    discount_type: '{{ $invoice->discount_type }}',
    discount_value: {{ $invoice->discount_value }},
    tax_percentage: {{ $invoice->tax_percentage }},
    items: {{ json_encode($invoice->items->map(function($it) {
        return [
            'description' => $it->description,
            'item_type' => $it->item_type,
            'quantity' => (float)$it->quantity,
            'unit_price' => (float)$it->unit_price,
            'total' => (float)$it->total,
        ];
    })) }},
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
                <span>Invoice Revision</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-slate-900">Edit Invoice {{ $invoice->invoice_number }}</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">Modify line items, tax percentage, or discount allowances.</p>
        </div>
        <a href="{{ route('invoices.show', $invoice) }}" class="btn-pill-secondary self-start sm:self-auto inline-flex items-center gap-2">
            &larr; <span>Cancel & Return</span>
        </a>
    </div>

    <form method="POST" action="{{ route('invoices.update', $invoice) }}" class="space-y-8" novalidate>
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 sm:gap-8">

            <!-- Left 2 Cols: Form -->
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
                            <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider">1. Invoice & Patient Metadata</h2>
                            <p class="text-xs text-slate-400">Review patient and attending clinician assignment</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-5">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Patient</label>
                            <input type="hidden" name="patient_id" value="{{ $invoice->patient_id }}">
                            <div class="px-4 py-3 bg-slate-50/90 border border-slate-200/80 rounded-2xl text-xs text-slate-900 flex items-center justify-between">
                                <span class="font-bold">{{ $invoice->patient->name }}</span>
                                <span class="font-mono text-slate-400">{{ $invoice->patient->patient_id }}</span>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Doctor</label>
                            @if(isset($doctors) && $doctors->count() === 1)
                                @php $singleDoc = $doctors->first(); @endphp
                                <input type="hidden" name="doctor_id" value="{{ $singleDoc->id }}">
                                <div class="px-4 py-3 bg-slate-50/90 border border-slate-200/80 rounded-2xl text-xs text-slate-800 flex items-center justify-between">
                                    <span class="font-bold">Dr. {{ $singleDoc->name }}</span>
                                    <span class="text-[10px] font-semibold text-brand-600 bg-brand-50 px-2 py-0.5 rounded-md">Primary</span>
                                </div>
                            @else
                                <select name="doctor_id" class="apple-input w-full">
                                    <option value="">-- General Clinic --</option>
                                    @foreach($doctors as $doc)
                                        <option value="{{ $doc->id }}" {{ old('doctor_id', $invoice->doctor_id) == $doc->id ? 'selected' : '' }}>
                                            {{ $doc->name }} ({{ ucfirst(str_replace('_', ' ', $doc->role)) }})
                                        </option>
                                    @endforeach
                                </select>
                            @endif
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Invoice Date *</label>
                            <input type="date" name="invoice_date" required value="{{ old('invoice_date', $invoice->invoice_date->toDateString()) }}"
                                   class="apple-input w-full">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Payment Due Date</label>
                            <input type="date" name="due_date" value="{{ old('due_date', $invoice->due_date?->toDateString()) }}"
                                   class="apple-input w-full">
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
                                <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider">2. Itemized Clinical Charges</h2>
                                <p class="text-xs text-slate-400">Add or modify services, diagnostic tests, or medicine</p>
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

                    <!-- Items Table -->
                    <div class="space-y-3">
                        <template x-for="(item, index) in items" :key="index">
                            <div class="glass-card rounded-2xl p-4 grid grid-cols-1 sm:grid-cols-12 gap-3 items-center hover:border-slate-300 transition">
                                
                                <div class="sm:col-span-5">
                                    <label class="block text-[10px] uppercase font-bold text-slate-400 mb-1">Item Description *</label>
                                    <input type="text" :name="'items[' + index + '][description]'" x-model="item.description" required
                                           class="apple-input w-full text-xs py-2">
                                </div>

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

                                <div class="sm:col-span-1">
                                    <label class="block text-[10px] uppercase font-bold text-slate-400 mb-1 text-center">Qty</label>
                                    <input type="number" step="0.1" min="0.1" :name="'items[' + index + '][quantity]'" x-model="item.quantity" @input="updateRowTotal(index)" required
                                           class="apple-input w-full text-xs py-2 text-center font-bold">
                                </div>

                                <div class="sm:col-span-2">
                                    <label class="block text-[10px] uppercase font-bold text-slate-400 mb-1 text-right">Price (₹)</label>
                                    <input type="number" step="0.01" min="0" :name="'items[' + index + '][unit_price]'" x-model="item.unit_price" @input="updateRowTotal(index)" required
                                           class="apple-input w-full text-xs py-2 text-right font-bold">
                                </div>

                                <div class="sm:col-span-2 flex items-center justify-between pl-2">
                                    <div class="text-right">
                                        <span class="block text-[10px] uppercase font-bold text-slate-400">Total</span>
                                        <span class="font-extrabold text-xs text-slate-900" x-text="'₹' + item.total"></span>
                                    </div>
                                    <button type="button" @click="removeItem(index)" x-show="items.length > 1" 
                                            class="w-7 h-7 rounded-full bg-rose-50 text-rose-500 hover:bg-rose-100 flex items-center justify-center text-xs font-bold transition ml-2" title="Remove row">
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
                            <textarea name="notes" rows="3"
                                      class="apple-input w-full leading-relaxed">{{ old('notes', $invoice->notes) }}</textarea>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Terms & Conditions</label>
                            <textarea name="terms" rows="3" 
                                      class="apple-input w-full leading-relaxed">{{ old('terms', $invoice->terms) }}</textarea>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Right Column: Calculation Summary -->
            <div class="space-y-6 sm:space-y-8">
                <div class="glass-card-elevated rounded-2xl p-6 sm:p-7 space-y-5">
                    <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider border-b border-slate-100 pb-3 flex items-center justify-between">
                        <span>Invoice Computation</span>
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
                            <input type="number" step="0.01" min="0" name="discount_value" x-model="discount_value"
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
                            <input type="number" step="0.01" min="0" max="100" name="tax_percentage" x-model="tax_percentage"
                                   class="apple-input w-24 text-xs py-2 text-center font-bold">
                            <span class="text-xs text-slate-400 font-medium">% tax rate</span>
                        </div>
                    </div>

                    <!-- Grand Total Banner -->
                    <div class="p-5 bg-gradient-to-br from-slate-900 to-slate-800 text-white rounded-2xl shadow-lg shadow-slate-900/10 space-y-1">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-brand-300">Revised Total</span>
                        <div class="text-3xl font-extrabold tracking-tight" x-text="'₹' + grandTotal().toFixed(2)"></div>
                    </div>

                    <!-- Already Paid Note -->
                    @if($invoice->paid_amount > 0)
                        <div class="p-4 bg-emerald-50/80 border border-emerald-200/80 rounded-2xl text-xs flex justify-between items-center">
                            <span class="text-emerald-800 font-medium">Settled Amount:</span>
                            <span class="font-extrabold text-emerald-800">₹{{ number_format($invoice->paid_amount, 2) }}</span>
                        </div>
                    @endif

                    <!-- Actions -->
                    <div class="pt-3">
                        <button type="submit" 
                                class="btn-pill-primary w-full py-3.5 text-center justify-center font-bold text-sm">
                            Save Updated Invoice
                        </button>
                    </div>

                </div>
            </div>

        </div>
    </form>
</div>
@endsection


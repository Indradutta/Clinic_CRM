@extends('layouts.app')

@section('title', 'Invoices & Billing')

@section('content')
<div class="space-y-8">

    <!-- Top Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-lg bg-brand-50/80 border border-brand-100 text-brand-700 text-xs font-semibold mb-2">
                <span class="w-2 h-2 rounded-full bg-brand-500 animate-pulse"></span>
                <span>Revenue & Settlements</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-slate-900">Invoices & Billing</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">Manage patient invoices, itemized charges, and payment settlements.</p>
        </div>
        <div class="flex items-center gap-3">
            @can('invoices.create')
            <a href="{{ route('invoices.create') }}" 
               class="btn-pill-primary inline-flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path>
                </svg>
                <span>Create Invoice</span>
            </a>
            @endcan
        </div>
    </div>

    <!-- Financial KPI Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- 1. Total Invoiced -->
        <div class="bg-white border border-border-default rounded-2xl p-5 flex items-center justify-between shadow-xs">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Invoiced</p>
                <p class="text-2xl sm:text-3xl font-bold text-slate-900 tracking-tight mt-1">₹{{ number_format($totalInvoiced, 2) }}</p>
                <span class="text-[11px] text-slate-500 mt-0.5 block">Lifetime billing volume</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-brand-50 text-brand-500 border border-brand-100 flex items-center justify-center font-bold text-xl shrink-0">
                ₹
            </div>
        </div>

        <!-- 2. Total Collected -->
        <div class="bg-white border border-border-default rounded-2xl p-5 flex items-center justify-between shadow-xs">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-emerald-600">Total Collected</p>
                <p class="text-2xl sm:text-3xl font-bold text-emerald-700 tracking-tight mt-1">₹{{ number_format($totalReceived, 2) }}</p>
                <span class="text-[11px] text-emerald-600/80 mt-0.5 block">Cleared payments</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 border border-emerald-100 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
        </div>

        <!-- 3. Outstanding Due -->
        <div class="bg-white border border-border-default rounded-2xl p-5 flex items-center justify-between shadow-xs">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-amber-600">Outstanding Due</p>
                <p class="text-2xl sm:text-3xl font-bold text-amber-700 tracking-tight mt-1">₹{{ number_format($totalOutstanding, 2) }}</p>
                <span class="text-[11px] text-amber-600/80 mt-0.5 block">Pending receivables</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 border border-amber-100 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
        </div>

        <!-- 4. Overdue Invoices -->
        <div class="bg-white border border-border-default rounded-2xl p-5 flex items-center justify-between shadow-xs">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-rose-600">Overdue Invoices</p>
                <p class="text-2xl sm:text-3xl font-bold text-rose-700 tracking-tight mt-1">{{ $overdueCount }}</p>
                <span class="text-[11px] text-rose-600/80 mt-0.5 block">Past payment deadline</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-rose-50 text-rose-600 border border-rose-100 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
            </div>
        </div>
    </div>

    <!-- Filters Bar -->
    <div class="glass-card rounded-2xl p-5">
        <form method="GET" action="{{ route('invoices.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3.5 items-end">

            <!-- Search -->
            <div class="lg:col-span-2">
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">Search Invoice</label>
                <div class="relative flex items-center">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <input type="text" name="search" value="{{ request('search') }}" 
                           placeholder="Invoice #, patient name, phone.."
                           class="apple-input w-full !pl-10 pr-3">
                </div>
            </div>

            <!-- Status Filter -->
            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">Status</label>
                <select name="status" class="apple-input w-full">
                    <option value="all">All Statuses</option>
                    <option value="unpaid" {{ request('status') === 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                    <option value="partially_paid" {{ request('status') === 'partially_paid' ? 'selected' : '' }}>Partially Paid</option>
                    <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Paid in Full</option>
                    <option value="overdue" {{ request('status') === 'overdue' ? 'selected' : '' }}>Overdue</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>

            <!-- Date Preset -->
            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">Date Preset</label>
                <select name="date_preset" class="apple-input w-full">
                    <option value="">Any Date</option>
                    <option value="today" {{ request('date_preset') === 'today' ? 'selected' : '' }}>Today</option>
                    <option value="this_week" {{ request('date_preset') === 'this_week' ? 'selected' : '' }}>This Week</option>
                    <option value="this_month" {{ request('date_preset') === 'this_month' ? 'selected' : '' }}>This Month</option>
                </select>
            </div>

            <!-- Doctor Filter -->
            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">Doctor</label>
                <select name="doctor_id" class="apple-input w-full">
                    <option value="">All Doctors</option>
                    @foreach($doctors as $doc)
                        <option value="{{ $doc->id }}" {{ request('doctor_id') == $doc->id ? 'selected' : '' }}>
                            {{ $doc->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Filter Buttons -->
            <div class="flex items-center gap-2">
                <button type="submit" class="btn-pill-primary w-full text-center justify-center">
                    Filter
                </button>
                @if(request()->hasAny(['search', 'status', 'date_preset', 'doctor_id', 'from_date', 'to_date']))
                    <a href="{{ route('invoices.index') }}" class="btn-pill-secondary px-3" title="Clear Filters">
                        ✕
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Invoices List Table -->
    <div class="table-container">
        @if($invoices->isEmpty())
            <div class="p-16 text-center text-slate-400">
                <div class="w-16 h-16 mx-auto rounded-3xl bg-slate-100/80 flex items-center justify-center text-slate-400 mb-4">
                    <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"></path>
                    </svg>
                </div>
                <p class="text-base font-bold text-slate-700">No invoices found</p>
                <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">No invoices match your selected search or filter criteria.</p>
                @can('invoices.create')
                <a href="{{ route('invoices.create') }}" class="mt-5 inline-flex items-center gap-2 btn-pill-primary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    <span>Create First Invoice</span>
                </a>
                @endcan
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-700">
                    <thead class="bg-slate-50/70 border-b border-slate-100/80 text-[11px] font-bold uppercase tracking-wider text-slate-400">
                        <tr>
                            <th class="px-6 py-4 whitespace-nowrap">Invoice #</th>
                            <th class="px-4 py-4 min-w-[180px]">Patient Details</th>
                            <th class="px-4 py-4 whitespace-nowrap">Date & Due</th>
                            <th class="px-4 py-4 whitespace-nowrap">Doctor</th>
                            <th class="px-4 py-4 text-right whitespace-nowrap">Grand Total</th>
                            <th class="px-4 py-4 text-right whitespace-nowrap">Paid</th>
                            <th class="px-4 py-4 text-right whitespace-nowrap">Balance Due</th>
                            <th class="px-4 py-4 text-center whitespace-nowrap min-w-[130px]">Status</th>
                            <th class="px-6 py-4 text-right whitespace-nowrap">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100/60">
                        @foreach($invoices as $inv)
                            <tr class="hover:bg-slate-50/60 transition-colors">
                                <td class="px-6 py-4 font-mono font-bold">
                                    <a href="{{ route('invoices.show', $inv) }}" class="text-brand-600 hover:text-brand-700 transition">
                                        {{ $inv->invoice_number }}
                                    </a>
                                </td>
                                <td class="px-4 py-4">
                                    @can('patients.view')
                                    <a href="{{ route('patients.show', $inv->patient) }}" class="font-bold text-slate-900 hover:text-brand-600 transition block">
                                        {{ $inv->patient->name }}
                                    </a>
                                    @else
                                        <span class="font-bold text-slate-900 block">{{ $inv->patient->name }}</span>
                                    @endcan
                                    <span class="text-[11px] text-slate-400 font-mono">{{ $inv->patient->patient_id }} • {{ $inv->patient->phone }}</span>
                                </td>
                                <td class="px-4 py-4">
                                    <span class="font-semibold text-slate-800">{{ $inv->invoice_date->format('M d, Y') }}</span>
                                    @if($inv->due_date)
                                        <span class="block text-[11px] text-slate-400">Due: {{ $inv->due_date->format('M d, Y') }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-4 text-slate-600">
                                    {{ $inv->doctor->name ?? '—' }}
                                </td>
                                <td class="px-4 py-4 text-right font-extrabold text-slate-900">
                                    ₹{{ number_format($inv->grand_total, 2) }}
                                </td>
                                <td class="px-4 py-4 text-right font-bold text-emerald-700">
                                    ₹{{ number_format($inv->paid_amount, 2) }}
                                </td>
                                <td class="px-4 py-4 text-right font-extrabold {{ $inv->balance_due > 0 ? 'text-rose-600' : 'text-slate-400' }}">
                                    ₹{{ number_format($inv->balance_due, 2) }}
                                </td>
                                <td class="px-4 py-4 text-center whitespace-nowrap">
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold border whitespace-nowrap shadow-2xs {{ $inv->status_badge_class }}">
                                        <span class="w-1.5 h-1.5 rounded-full shrink-0 {{ $inv->status === 'paid' ? 'bg-emerald-500' : ($inv->status === 'partially_paid' ? 'bg-amber-500' : ($inv->status === 'overdue' ? 'bg-rose-500' : ($inv->status === 'cancelled' ? 'bg-slate-400' : 'bg-blue-500'))) }}"></span>
                                        <span>{{ $inv->status_label }}</span>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right space-x-1.5 whitespace-nowrap">
                                    <a href="{{ route('invoices.print', $inv) }}" target="_blank" 
                                       class="px-2.5 py-1.5 rounded-md bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-[11px] transition inline-flex items-center gap-1" title="Print Invoice">
                                        🖨 <span>Print</span>
                                    </a>
                                    <a href="{{ route('invoices.show', $inv) }}" 
                                       class="px-3 py-1.5 rounded-md bg-brand-50 hover:bg-brand-100 text-brand-700 font-bold text-[11px] transition">
                                        View
                                    </a>
                                    @can('invoices.edit')
                                    <a href="{{ route('invoices.edit', $inv) }}" 
                                       class="px-3 py-1.5 rounded-md bg-slate-100 hover:bg-slate-200 text-slate-600 font-semibold text-[11px] transition">
                                        Edit
                                    </a>
                                    @endcan
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($invoices->hasPages())
                <div class="px-6 py-4 border-t border-slate-100">
                    {{ $invoices->links() }}
                </div>
            @endif
        @endif
    </div>

</div>
@endsection

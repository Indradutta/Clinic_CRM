@extends('layouts.app')

@section('title', 'Practice Reports & Analytics')

@section('content')
<div class="space-y-8">

    <!-- Page Header & Action Controls -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-6 pb-2">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-lg bg-[#EEF7F9] border border-[#D8EEF3] text-[#27758F] text-xs font-semibold mb-3">
                <span class="w-2 h-2 rounded-full bg-[#27758F] animate-pulse"></span>
                <span>Active Period: {{ $dateLabel }}</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900">Practice Reports & Analytics</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1.5 max-w-2xl leading-relaxed">
                Comprehensive clinical intelligence, revenue ledgers, appointment fulfillment, and medication metrics for {{ $clinicName }}.
            </p>
        </div>

        <!-- Export & Print Action Buttons -->
        <div class="flex items-center gap-3 shrink-0">
            @can('reports.print')
            <a href="{{ route('reports.print', request()->query()) }}" 
               target="_blank"
               class="btn-pill-secondary inline-flex items-center gap-2 text-xs py-2.5 px-4 shadow-2xs hover:shadow-xs transition">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                </svg>
                <span>Print Report</span>
            </a>
            @endcan

            @can('reports.export')
            <a href="{{ route('reports.export', request()->query()) }}" 
               class="btn-pill-primary inline-flex items-center gap-2 text-xs py-2.5 px-4 shadow-2xs hover:shadow-xs transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                </svg>
                <span>Export CSV</span>
            </a>
            @endcan
        </div>
    </div>

    <!-- Segmented Capsule Tabs -->
    <div class="flex overflow-x-auto pb-1 pt-1">
        <div class="bg-slate-100 p-1.5 rounded-xl inline-flex gap-1.5 border border-slate-200/60 shadow-2xs">
            <!-- 1. Financial Reports -->
            <a href="{{ route('reports.index', array_merge(request()->query(), ['type' => 'financial'])) }}"
               class="px-4 sm:px-5 py-2 rounded-lg font-bold text-xs sm:text-sm flex items-center gap-2 transition {{ $reportType === 'financial' ? 'bg-white text-slate-900 shadow-sm border border-slate-200/40' : 'text-slate-600 hover:text-slate-900' }}">
                <svg class="w-4 h-4 {{ $reportType === 'financial' ? 'text-[#27758F]' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span>Financials</span>
            </a>

            <!-- 2. Appointment Reports -->
            <a href="{{ route('reports.index', array_merge(request()->query(), ['type' => 'appointments'])) }}"
               class="px-4 sm:px-5 py-2 rounded-lg font-bold text-xs sm:text-sm flex items-center gap-2 transition {{ $reportType === 'appointments' ? 'bg-white text-slate-900 shadow-sm border border-slate-200/40' : 'text-slate-600 hover:text-slate-900' }}">
                <svg class="w-4 h-4 {{ $reportType === 'appointments' ? 'text-[#27758F]' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                <span>Appointments</span>
            </a>

            <!-- 3. Patient Reports -->
            <a href="{{ route('reports.index', array_merge(request()->query(), ['type' => 'patients'])) }}"
               class="px-4 sm:px-5 py-2 rounded-lg font-bold text-xs sm:text-sm flex items-center gap-2 transition {{ $reportType === 'patients' ? 'bg-white text-slate-900 shadow-sm border border-slate-200/40' : 'text-slate-600 hover:text-slate-900' }}">
                <svg class="w-4 h-4 {{ $reportType === 'patients' ? 'text-[#27758F]' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
                <span>Patients</span>
            </a>

            <!-- 4. Prescription Reports -->
            <a href="{{ route('reports.index', array_merge(request()->query(), ['type' => 'prescriptions'])) }}"
               class="px-4 sm:px-5 py-2 rounded-lg font-bold text-xs sm:text-sm flex items-center gap-2 transition {{ $reportType === 'prescriptions' ? 'bg-white text-slate-900 shadow-sm border border-slate-200/40' : 'text-slate-600 hover:text-slate-900' }}">
                <svg class="w-4 h-4 {{ $reportType === 'prescriptions' ? 'text-[#27758F]' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <span>Prescriptions</span>
            </a>
        </div>
    </div>

    <!-- Filter & Search Toolbar -->
    <div class="glass-card rounded-2xl p-5 sm:p-6" x-data="{ customRange: '{{ $dateFilter }}' === 'custom' }">
        <form method="GET" action="{{ route('reports.index') }}" class="space-y-4">
            <input type="hidden" name="type" value="{{ $reportType }}">

            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-5">
                <!-- Preset Date Buttons -->
                <div class="flex flex-wrap items-center gap-2 text-xs">
                    @foreach([
                        'today' => 'Today',
                        'yesterday' => 'Yesterday',
                        'this_week' => 'This Week',
                        'this_month' => 'This Month',
                        'last_month' => 'Last Month',
                        'this_year' => 'This Year',
                        'custom' => 'Custom Range',
                    ] as $key => $label)
                        <button type="submit" 
                                name="date_range" 
                                value="{{ $key }}"
                                @click="customRange = ('{{ $key }}' === 'custom')"
                                class="px-3.5 py-2 rounded-lg text-xs font-semibold transition {{ $dateFilter === $key ? 'bg-[#27758F] text-white shadow-xs font-bold' : 'bg-slate-100 hover:bg-slate-200/80 text-slate-700' }}">
                            {{ $label }}
                        </button>
                    @endforeach
                </div>

                <!-- Search Input with ample left icon padding -->
                <div class="flex items-center gap-2">
                    <div class="relative w-full sm:w-72">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                        <input type="text" 
                               name="search" 
                               value="{{ $search }}" 
                               placeholder="Search records..." 
                               class="apple-input !pl-10 !py-2 text-xs">
                    </div>
                    <button type="submit" class="btn-pill-primary text-xs py-2 px-4 shrink-0">
                        Filter
                    </button>
                    @if($search || $dateFilter !== 'this_month')
                        <a href="{{ route('reports.index', ['type' => $reportType]) }}" class="btn-pill-secondary text-xs py-2 px-3 shrink-0" title="Reset Filters">
                            ✕
                        </a>
                    @endif
                </div>
            </div>

            <!-- Custom Date Range Inputs -->
            <div x-show="customRange" x-cloak class="pt-4 border-t border-slate-100 flex flex-wrap items-center gap-3 text-xs">
                <div class="flex items-center gap-2">
                    <span class="text-slate-500 font-medium">From:</span>
                    <input type="date" name="from_date" value="{{ request('from_date', $startDate->toDateString()) }}" class="apple-input text-xs py-1.5">
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-slate-500 font-medium">To:</span>
                    <input type="date" name="to_date" value="{{ request('to_date', $endDate->toDateString()) }}" class="apple-input text-xs py-1.5">
                </div>
                <button type="submit" class="btn-pill-primary text-xs py-1.5 px-4">
                    Apply Dates
                </button>
            </div>
        </form>
    </div>

    <!-- Summary KPI Cards for Active Report Tab -->
    @if($reportType === 'financial')
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">
            <!-- 1. Collected Revenue -->
            <div class="bg-white border border-[#E2EDF1] rounded-2xl p-5 flex items-center justify-between shadow-2xs hover:shadow-xs hover:border-[#27758F]/30 transition group">
                <div class="space-y-1">
                    <p class="text-[11px] font-bold uppercase tracking-wider text-emerald-600">Collected Revenue</p>
                    <h3 class="text-2xl sm:text-3xl font-extrabold text-emerald-700 tracking-tight">
                        {{ $currencySymbol }}{{ number_format($metrics['total_revenue'], 2) }}
                    </h3>
                    <p class="text-[11px] text-slate-500 font-medium flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 shrink-0"></span>
                        Settled payments in period
                    </p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-600 shrink-0 group-hover:scale-105 transition-transform">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>

            <!-- 2. Total Invoiced -->
            <div class="bg-white border border-[#E2EDF1] rounded-2xl p-5 flex items-center justify-between shadow-2xs hover:shadow-xs hover:border-[#27758F]/30 transition group">
                <div class="space-y-1">
                    <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Total Invoiced</p>
                    <h3 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                        {{ $currencySymbol }}{{ number_format($metrics['total_invoiced'], 2) }}
                    </h3>
                    <p class="text-[11px] text-slate-500 font-medium flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-slate-400 shrink-0"></span>
                        Grand total billed
                    </p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-slate-100 border border-slate-200/80 flex items-center justify-center text-slate-600 shrink-0 group-hover:scale-105 transition-transform">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"></path>
                    </svg>
                </div>
            </div>

            <!-- 3. Outstanding Balance -->
            <div class="bg-white border border-[#E2EDF1] rounded-2xl p-5 flex items-center justify-between shadow-2xs hover:shadow-xs hover:border-[#27758F]/30 transition group">
                <div class="space-y-1">
                    <p class="text-[11px] font-bold uppercase tracking-wider text-rose-600">Outstanding Balance</p>
                    <h3 class="text-2xl sm:text-3xl font-extrabold text-rose-700 tracking-tight">
                        {{ $currencySymbol }}{{ number_format($metrics['outstanding_amount'], 2) }}
                    </h3>
                    <p class="text-[11px] text-rose-600 font-medium flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500 shrink-0"></span>
                        {{ $metrics['outstanding_count'] }} invoices pending
                    </p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-rose-50 border border-rose-100 flex items-center justify-center text-rose-600 shrink-0 group-hover:scale-105 transition-transform">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                </div>
            </div>

            <!-- 4. Paid Invoices -->
            <div class="bg-white border border-[#E2EDF1] rounded-2xl p-5 flex items-center justify-between shadow-2xs hover:shadow-xs hover:border-[#27758F]/30 transition group">
                <div class="space-y-1">
                    <p class="text-[11px] font-bold uppercase tracking-wider text-[#27758F]">Paid Invoices</p>
                    <h3 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                        {{ number_format($metrics['paid_invoices_count']) }}
                    </h3>
                    <p class="text-[11px] text-slate-500 font-medium flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#27758F] shrink-0"></span>
                        Fully cleared receipts
                    </p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-[#EEF7F9] border border-[#D8EEF3] flex items-center justify-center text-[#27758F] shrink-0 group-hover:scale-105 transition-transform">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        @if(isset($paymentMethods) && $paymentMethods->isNotEmpty())
            <!-- Payment Methods Breakdown -->
            <div class="glass-card rounded-2xl p-6">
                <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider mb-4">Payment Methods Distribution</h3>
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
                    @foreach($paymentMethods as $pm)
                        <div class="p-3.5 rounded-2xl bg-slate-50/80 border border-slate-100">
                            <span class="text-xs font-bold text-slate-600 block capitalize">{{ $pm->payment_method }}</span>
                            <span class="text-base font-extrabold text-slate-900 mt-1 block">
                                {{ $currencySymbol }}{{ number_format($pm->total_amount, 2) }}
                            </span>
                            <span class="text-[10px] text-slate-400 font-medium">{{ $pm->tx_count }} transactions</span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

    @elseif($reportType === 'appointments')
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">
            <!-- 1. Total Booked -->
            <div class="bg-white border border-[#E2EDF1] rounded-2xl p-5 flex items-center justify-between shadow-2xs hover:shadow-xs transition group">
                <div class="space-y-1">
                    <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Total Booked</p>
                    <h3 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">{{ number_format($metrics['total_appointments']) }}</h3>
                    <p class="text-[11px] text-slate-500 font-medium flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-slate-400 shrink-0"></span>
                        Scheduled appointments
                    </p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-slate-100 border border-slate-200/80 flex items-center justify-center text-slate-600 shrink-0 group-hover:scale-105 transition-transform">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                </div>
            </div>

            <!-- 2. Completed Visits -->
            <div class="bg-white border border-[#E2EDF1] rounded-2xl p-5 flex items-center justify-between shadow-2xs hover:shadow-xs transition group">
                <div class="space-y-1">
                    <p class="text-[11px] font-bold uppercase tracking-wider text-emerald-600">Completed Visits</p>
                    <h3 class="text-2xl sm:text-3xl font-extrabold text-emerald-700 tracking-tight">{{ number_format($metrics['completed_count']) }}</h3>
                    <p class="text-[11px] text-emerald-600 font-bold flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 shrink-0"></span>
                        {{ $metrics['completion_rate'] }}% completion rate
                    </p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-600 shrink-0 group-hover:scale-105 transition-transform">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>

            <!-- 3. Active / Confirmed -->
            <div class="bg-white border border-[#E2EDF1] rounded-2xl p-5 flex items-center justify-between shadow-2xs hover:shadow-xs transition group">
                <div class="space-y-1">
                    <p class="text-[11px] font-bold uppercase tracking-wider text-[#27758F]">Active / Confirmed</p>
                    <h3 class="text-2xl sm:text-3xl font-extrabold text-[#1F677E] tracking-tight">{{ number_format($metrics['confirmed_count']) }}</h3>
                    <p class="text-[11px] text-slate-500 font-medium flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#27758F] shrink-0"></span>
                        Confirmed / in consultation
                    </p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-[#EEF7F9] border border-[#D8EEF3] flex items-center justify-center text-[#27758F] shrink-0 group-hover:scale-105 transition-transform">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>

            <!-- 4. Cancelled / No-Show -->
            <div class="bg-white border border-[#E2EDF1] rounded-2xl p-5 flex items-center justify-between shadow-2xs hover:shadow-xs transition group">
                <div class="space-y-1">
                    <p class="text-[11px] font-bold uppercase tracking-wider text-rose-600">Cancelled / No-Show</p>
                    <h3 class="text-2xl sm:text-3xl font-extrabold text-rose-700 tracking-tight">{{ number_format($metrics['cancelled_count'] + $metrics['no_show_count']) }}</h3>
                    <p class="text-[11px] text-rose-500 font-medium flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500 shrink-0"></span>
                        {{ $metrics['cancelled_count'] }} cancelled • {{ $metrics['no_show_count'] }} no-show
                    </p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-rose-50 border border-rose-100 flex items-center justify-center text-rose-600 shrink-0 group-hover:scale-105 transition-transform">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </div>
            </div>
        </div>

    @elseif($reportType === 'patients')
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 sm:gap-5">
            <!-- 1. New Patients -->
            <div class="bg-white border border-[#E2EDF1] rounded-2xl p-5 flex items-center justify-between shadow-2xs hover:shadow-xs transition group">
                <div class="space-y-1">
                    <p class="text-[11px] font-bold uppercase tracking-wider text-[#27758F]">New Patients</p>
                    <h3 class="text-2xl sm:text-3xl font-extrabold text-[#1F677E] tracking-tight">{{ number_format($metrics['new_patients']) }}</h3>
                    <p class="text-[11px] text-slate-500 font-medium flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#27758F] shrink-0"></span>
                        Registered in selected range
                    </p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-[#EEF7F9] border border-[#D8EEF3] flex items-center justify-center text-[#27758F] shrink-0 group-hover:scale-105 transition-transform">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                    </svg>
                </div>
            </div>

            <!-- 2. Returning Patients -->
            <div class="bg-white border border-[#E2EDF1] rounded-2xl p-5 flex items-center justify-between shadow-2xs hover:shadow-xs transition group">
                <div class="space-y-1">
                    <p class="text-[11px] font-bold uppercase tracking-wider text-indigo-600">Returning Patients</p>
                    <h3 class="text-2xl sm:text-3xl font-extrabold text-indigo-700 tracking-tight">{{ number_format($metrics['returning_patients']) }}</h3>
                    <p class="text-[11px] text-slate-500 font-medium flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-indigo-500 shrink-0"></span>
                        Follow-up / recurring visits
                    </p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-600 shrink-0 group-hover:scale-105 transition-transform">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                </div>
            </div>

            <!-- 3. Total Registered -->
            <div class="bg-white border border-[#E2EDF1] rounded-2xl p-5 flex items-center justify-between shadow-2xs hover:shadow-xs transition group">
                <div class="space-y-1">
                    <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Total Registered</p>
                    <h3 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">{{ number_format($metrics['total_registered']) }}</h3>
                    <p class="text-[11px] text-slate-500 font-medium flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-slate-400 shrink-0"></span>
                        Cumulative patient database
                    </p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-slate-100 border border-slate-200/80 flex items-center justify-center text-slate-600 shrink-0 group-hover:scale-105 transition-transform">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

    @elseif($reportType === 'prescriptions')
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-5">
            <!-- 1. Total Prescriptions -->
            <div class="bg-white border border-[#E2EDF1] rounded-2xl p-5 flex items-center justify-between shadow-2xs hover:shadow-xs transition group">
                <div class="space-y-1">
                    <p class="text-[11px] font-bold uppercase tracking-wider text-[#27758F]">Total Prescriptions</p>
                    <h3 class="text-2xl sm:text-3xl font-extrabold text-[#1F677E] tracking-tight">{{ number_format($metrics['total_prescriptions']) }}</h3>
                    <p class="text-[11px] text-slate-500 font-medium flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#27758F] shrink-0"></span>
                        Clinical scripts issued
                    </p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-[#EEF7F9] border border-[#D8EEF3] flex items-center justify-center text-[#27758F] shrink-0 group-hover:scale-105 transition-transform">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
            </div>

            <!-- 2. Distinct Patients Treated -->
            <div class="bg-white border border-[#E2EDF1] rounded-2xl p-5 flex items-center justify-between shadow-2xs hover:shadow-xs transition group">
                <div class="space-y-1">
                    <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Distinct Patients Treated</p>
                    <h3 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">{{ number_format($metrics['distinct_patients']) }}</h3>
                    <p class="text-[11px] text-slate-500 font-medium flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-slate-400 shrink-0"></span>
                        Unique patients prescribed medications
                    </p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-slate-100 border border-slate-200/80 flex items-center justify-center text-slate-600 shrink-0 group-hover:scale-105 transition-transform">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        @if(isset($topMedicines) && $topMedicines->isNotEmpty())
            <div class="glass-card rounded-2xl p-6">
                <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider mb-4">Top Prescribed Medications (Period)</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                    @foreach($topMedicines as $med)
                        <div class="p-3.5 rounded-2xl bg-brand-50/40 border border-brand-100">
                            <span class="text-xs font-bold text-slate-900 block truncate">{{ $med->medicine_name }}</span>
                            <span class="text-[11px] text-brand-700 font-bold mt-1 block">{{ $med->frequency }} times prescribed</span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    @endif

    <!-- Data Table Container -->
    <div class="table-container">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <h2 class="font-bold text-sm sm:text-base text-slate-900 capitalize">
                {{ $reportType }} Ledger ({{ $records->total() }} records)
            </h2>
            <span class="text-xs text-slate-400 font-medium">Page {{ $records->currentPage() }} of {{ $records->lastPage() }}</span>
        </div>

        @if($records->isEmpty())
            <div class="p-16 text-center text-slate-400">
                <div class="w-14 h-14 mx-auto rounded-3xl bg-slate-100/80 flex items-center justify-center text-slate-400 mb-3">
                    <svg class="w-7 h-7 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <p class="text-sm font-bold text-slate-700">No records found for the selected filter criteria</p>
                <p class="text-xs text-slate-400 mt-1">Try expanding the date range or clearing the search keyword.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-700">
                    <thead class="bg-slate-50/70 border-b border-slate-100 text-[11px] font-bold uppercase tracking-wider text-slate-400">
                        @if($reportType === 'financial')
                            <tr>
                                <th class="px-6 py-4">Invoice #</th>
                                <th class="px-4 py-4">Date</th>
                                <th class="px-4 py-4">Patient</th>
                                <th class="px-4 py-4">Doctor</th>
                                <th class="px-4 py-4 text-right">Grand Total</th>
                                <th class="px-4 py-4 text-right">Paid</th>
                                <th class="px-4 py-4 text-right">Balance Due</th>
                                <th class="px-6 py-4 text-right">Status</th>
                            </tr>
                        @elseif($reportType === 'appointments')
                            <tr>
                                <th class="px-6 py-4">Appointment #</th>
                                <th class="px-4 py-4">Date & Time</th>
                                <th class="px-4 py-4">Patient</th>
                                <th class="px-4 py-4">Doctor</th>
                                <th class="px-4 py-4">Type</th>
                                <th class="px-4 py-4">Status</th>
                                <th class="px-6 py-4 text-right">Payment</th>
                            </tr>
                        @elseif($reportType === 'patients')
                            <tr>
                                <th class="px-6 py-4">Patient ID</th>
                                <th class="px-4 py-4">Full Name</th>
                                <th class="px-4 py-4">Phone</th>
                                <th class="px-4 py-4">Gender/Age</th>
                                <th class="px-4 py-4">Registered Date</th>
                                <th class="px-6 py-4 text-right">Appointments</th>
                            </tr>
                        @elseif($reportType === 'prescriptions')
                            <tr>
                                <th class="px-6 py-4">Rx #</th>
                                <th class="px-4 py-4">Date</th>
                                <th class="px-4 py-4">Patient</th>
                                <th class="px-4 py-4">Doctor</th>
                                <th class="px-4 py-4">Diagnosis</th>
                                <th class="px-4 py-4">Medicines</th>
                                <th class="px-6 py-4 text-right">Follow-up</th>
                            </tr>
                        @endif
                    </thead>
                    <tbody class="divide-y divide-slate-100/60">
                        @if($reportType === 'financial')
                            @foreach($records as $inv)
                                <tr class="hover:bg-slate-50/60 transition">
                                    <td class="px-6 py-4 font-bold text-slate-900 font-mono text-[11px]">
                                        @can('invoices.view')
                                            <a href="{{ route('invoices.show', $inv) }}" class="text-brand-600 hover:text-brand-700">
                                                {{ $inv->invoice_number }}
                                            </a>
                                        @else
                                            {{ $inv->invoice_number }}
                                        @endcan
                                    </td>
                                    <td class="px-4 py-4 text-slate-600 whitespace-nowrap">{{ $inv->invoice_date?->format('d M Y') }}</td>
                                    <td class="px-4 py-4 font-bold text-slate-900">{{ $inv->patient->name ?? 'N/A' }}</td>
                                    <td class="px-4 py-4 text-slate-600">{{ $inv->doctor->name ?? 'N/A' }}</td>
                                    <td class="px-4 py-4 text-right font-extrabold text-slate-900">{{ $currencySymbol }}{{ number_format($inv->grand_total, 2) }}</td>
                                    <td class="px-4 py-4 text-right font-bold text-emerald-600">{{ $currencySymbol }}{{ number_format($inv->paid_amount, 2) }}</td>
                                    <td class="px-4 py-4 text-right font-extrabold {{ $inv->balance_due > 0 ? 'text-rose-600' : 'text-slate-400' }}">
                                        {{ $currencySymbol }}{{ number_format($inv->balance_due, 2) }}
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-[10px] font-bold uppercase {{ $inv->status === 'paid' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : ($inv->status === 'partially_paid' ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-rose-50 text-rose-700 border border-rose-200') }}">
                                            {{ str_replace('_', ' ', $inv->status) }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        @elseif($reportType === 'appointments')
                            @foreach($records as $apt)
                                <tr class="hover:bg-slate-50/60 transition">
                                    <td class="px-6 py-4 font-bold text-slate-900 font-mono text-[11px]">
                                        @can('appointments.view')
                                            <a href="{{ route('appointments.show', $apt) }}" class="text-brand-600 hover:text-brand-700">
                                                {{ $apt->appointment_id }}
                                            </a>
                                        @else
                                            {{ $apt->appointment_id }}
                                        @endcan
                                    </td>
                                    <td class="px-4 py-4 text-slate-700 whitespace-nowrap font-medium">
                                        {{ $apt->appointment_date?->format('d M Y') }} • {{ date('h:i A', strtotime($apt->appointment_time)) }}
                                    </td>
                                    <td class="px-4 py-4 font-bold text-slate-900">{{ $apt->patient->name ?? 'N/A' }}</td>
                                    <td class="px-4 py-4 text-slate-600">{{ $apt->doctor->name ?? 'N/A' }}</td>
                                    <td class="px-4 py-4"><span class="px-2.5 py-0.5 bg-slate-100 rounded-md text-slate-700 text-[11px] font-medium">{{ $apt->appointment_type }}</span></td>
                                    <td class="px-4 py-4">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-[10px] font-bold border {{ $apt->status_badge_class }}">
                                            {{ $apt->status_label }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right font-medium capitalize text-slate-600">{{ $apt->payment_status }}</td>
                                </tr>
                            @endforeach
                        @elseif($reportType === 'patients')
                            @foreach($records as $pat)
                                <tr class="hover:bg-slate-50/60 transition">
                                    <td class="px-6 py-4 font-bold font-mono text-[11px] text-slate-400">
                                        @can('patients.view')
                                            <a href="{{ route('patients.show', $pat) }}" class="text-brand-600 hover:text-brand-700">
                                                {{ $pat->patient_id }}
                                            </a>
                                        @else
                                            {{ $pat->patient_id }}
                                        @endcan
                                    </td>
                                    <td class="px-4 py-4 font-bold text-slate-900">{{ $pat->name }}</td>
                                    <td class="px-4 py-4 text-slate-600 font-mono text-[11px]">{{ $pat->phone ?: '—' }}</td>
                                    <td class="px-4 py-4 text-slate-600">{{ $pat->gender ?: 'N/A' }} • {{ $pat->age ?: '—' }} yrs</td>
                                    <td class="px-4 py-4 text-slate-600 whitespace-nowrap">{{ $pat->created_at?->format('d M Y') }}</td>
                                    <td class="px-6 py-4 text-right font-extrabold text-brand-700">{{ $pat->appointments_count }} visits</td>
                                </tr>
                            @endforeach
                        @elseif($reportType === 'prescriptions')
                            @foreach($records as $rx)
                                <tr class="hover:bg-slate-50/60 transition">
                                    <td class="px-6 py-4 font-bold font-mono text-[11px] text-slate-900">
                                        @can('prescriptions.view')
                                            <a href="{{ route('prescriptions.show', $rx) }}" class="text-brand-600 hover:text-brand-700">
                                                {{ $rx->prescription_number }}
                                            </a>
                                        @else
                                            {{ $rx->prescription_number }}
                                        @endcan
                                    </td>
                                    <td class="px-4 py-4 text-slate-600 whitespace-nowrap">{{ $rx->prescription_date?->format('d M Y') }}</td>
                                    <td class="px-4 py-4 font-bold text-slate-900">{{ $rx->patient->name ?? 'N/A' }}</td>
                                    <td class="px-4 py-4 text-slate-600">{{ $rx->doctor->name ?? 'N/A' }}</td>
                                    <td class="px-4 py-4 text-slate-800 font-medium max-w-xs truncate">{{ $rx->diagnosis }}</td>
                                    <td class="px-4 py-4 text-slate-600">{{ $rx->items->count() }} items</td>
                                    <td class="px-6 py-4 text-right text-slate-500 whitespace-nowrap">{{ $rx->follow_up_date?->format('d M Y') ?? '—' }}</td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-4 border-t border-slate-100">
                {{ $records->links() }}
            </div>
        @endif
    </div>

</div>
@endsection

@extends('layouts.app')

@section('title', 'Global Search Results')

@section('content')
<div class="space-y-8">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-lg bg-brand-50/80 border border-brand-100 text-brand-700 text-xs font-semibold mb-2">
                <span>Universal Lookup</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-slate-900">Global Search</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">
                @if($query)
                    Search results for <span class="font-bold text-brand-600">"{{ $query }}"</span> ({{ $totalMatches }} records found)
                @else
                    Search across all patient records, scheduled appointments, clinical prescriptions, and billing invoices.
                @endif
            </p>
        </div>
    </div>

    <!-- Search Input Bar (Apple Spotlight style) -->
    <div class="glass-card rounded-2xl p-4 sm:p-5">
        <form method="GET" action="{{ route('search') }}" class="flex items-center gap-3">
            <div class="relative flex items-center flex-1">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
                <input type="text" 
                       name="q" 
                       value="{{ $query }}" 
                       placeholder="Search by patient name, phone number, APT-#, RX-#, or INV-#..." 
                       autofocus
                       class="apple-input w-full !pl-12 pr-4 text-sm py-3">
            </div>
            <button type="submit" class="btn-pill-primary py-3 px-6 text-sm">
                Search
            </button>
        </form>
    </div>

    @if(empty($query))
        <div class="p-16 glass-card rounded-2xl text-center text-slate-400">
            <div class="w-16 h-16 mx-auto rounded-3xl bg-slate-100/80 flex items-center justify-center text-slate-400 mb-4">
                <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </div>
            <h3 class="text-base font-bold text-slate-800">Enter a search keyword</h3>
            <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">Try typing a patient's phone number or name to pull up their complete profile, appointments, prescriptions, and invoices in one view.</p>
        </div>
    @elseif($totalMatches === 0)
        <div class="p-16 glass-card rounded-2xl text-center text-slate-400">
            <div class="w-16 h-16 mx-auto rounded-3xl bg-slate-100/80 flex items-center justify-center text-slate-400 mb-4">
                <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <h3 class="text-base font-bold text-slate-800">No matching records found</h3>
            <p class="text-xs text-slate-500 mt-1">We couldn't find any patient, appointment, prescription, or invoice matching "{{ $query }}".</p>
        </div>
    @else
        <!-- Results Sections -->
        <div class="space-y-6 sm:space-y-8">
            
            <!-- 1. Matching Patients -->
            @if($patients->isNotEmpty())
                <div class="glass-card rounded-2xl overflow-hidden">
                    <div class="px-6 py-4 bg-slate-50/70 border-b border-slate-100 flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-800 uppercase tracking-wider">Patients ({{ $patients->count() }})</span>
                        <a href="{{ route('patients.index', ['search' => $query]) }}" class="text-xs font-bold text-brand-600 hover:text-brand-700 hover:underline">View All &rarr;</a>
                    </div>
                    <div class="divide-y divide-slate-100/60">
                        @foreach($patients as $p)
                            <div class="p-5 flex items-center justify-between hover:bg-slate-50/60 transition-colors">
                                <div class="flex items-center gap-3.5">
                                    <div class="w-10 h-10 rounded-2xl bg-brand-50 text-brand-700 flex items-center justify-center font-bold text-xs shrink-0 border border-brand-200/80">
                                        {{ strtoupper(substr($p->name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <a href="{{ route('patients.show', $p) }}" class="font-bold text-sm text-slate-900 hover:text-brand-600 transition">
                                            {{ $p->name }}
                                        </a>
                                        <p class="text-xs text-slate-400 font-mono mt-0.5">{{ $p->patient_id }} • {{ $p->phone ?: 'No phone' }} • {{ $p->gender ?: 'N/A' }}</p>
                                    </div>
                                </div>
                                <a href="{{ route('patients.show', $p) }}" class="btn-pill-secondary text-xs py-1.5 px-3.5">
                                    Open Profile
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- 2. Matching Appointments -->
            @if($appointments->isNotEmpty())
                <div class="glass-card rounded-2xl overflow-hidden">
                    <div class="px-6 py-4 bg-slate-50/70 border-b border-slate-100 flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-800 uppercase tracking-wider">Appointments ({{ $appointments->count() }})</span>
                        <a href="{{ route('appointments.index', ['search' => $query]) }}" class="text-xs font-bold text-brand-600 hover:text-brand-700 hover:underline">View All &rarr;</a>
                    </div>
                    <div class="divide-y divide-slate-100/60">
                        @foreach($appointments as $apt)
                            <div class="p-5 flex items-center justify-between hover:bg-slate-50/60 transition-colors">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="font-mono text-xs font-bold text-brand-700 bg-brand-50 px-2.5 py-0.5 rounded-md">{{ $apt->appointment_id }}</span>
                                        <span class="text-xs font-bold text-slate-900">{{ $apt->patient->name ?? 'N/A' }}</span>
                                        <span class="px-2.5 py-0.5 rounded-md text-[10px] font-bold border {{ $apt->status_badge_class }}">{{ $apt->status_label }}</span>
                                    </div>
                                    <p class="text-xs text-slate-500 mt-1">
                                        {{ $apt->appointment_date?->format('d M Y') }} at {{ date('h:i A', strtotime($apt->appointment_time)) }} • {{ $apt->appointment_type }} • Dr. {{ $apt->doctor->name ?? 'N/A' }}
                                    </p>
                                </div>
                                <a href="{{ route('appointments.show', $apt) }}" class="btn-pill-secondary text-xs py-1.5 px-3.5">
                                    View
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- 3. Matching Prescriptions -->
            @if($prescriptions->isNotEmpty())
                <div class="glass-card rounded-2xl overflow-hidden">
                    <div class="px-6 py-4 bg-slate-50/70 border-b border-slate-100 flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-800 uppercase tracking-wider">Prescriptions ({{ $prescriptions->count() }})</span>
                        <a href="{{ route('prescriptions.index') }}" class="text-xs font-bold text-brand-600 hover:text-brand-700 hover:underline">View All &rarr;</a>
                    </div>
                    <div class="divide-y divide-slate-100/60">
                        @foreach($prescriptions as $rx)
                            <div class="p-5 flex items-center justify-between hover:bg-slate-50/60 transition-colors">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="font-mono text-xs font-bold text-teal-700 bg-teal-50 px-2.5 py-0.5 rounded-md">{{ $rx->prescription_number }}</span>
                                        <span class="text-xs font-bold text-slate-900">{{ $rx->patient->name ?? 'N/A' }}</span>
                                    </div>
                                    <p class="text-xs text-slate-700 mt-1 font-semibold">{{ $rx->diagnosis }}</p>
                                    <p class="text-[11px] text-slate-400 mt-0.5">{{ $rx->prescription_date?->format('d M Y') }} • {{ $rx->items->count() }} medications prescribed</p>
                                </div>
                                <a href="{{ route('prescriptions.show', $rx) }}" class="btn-pill-secondary text-xs py-1.5 px-3.5">
                                    View Rx
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- 4. Matching Invoices -->
            @if($invoices->isNotEmpty())
                <div class="glass-card rounded-2xl overflow-hidden">
                    <div class="px-6 py-4 bg-slate-50/70 border-b border-slate-100 flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-800 uppercase tracking-wider">Invoices ({{ $invoices->count() }})</span>
                        <a href="{{ route('invoices.index') }}" class="text-xs font-bold text-brand-600 hover:text-brand-700 hover:underline">View All &rarr;</a>
                    </div>
                    <div class="divide-y divide-slate-100/60">
                        @foreach($invoices as $inv)
                            <div class="p-5 flex items-center justify-between hover:bg-slate-50/60 transition-colors">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="font-mono text-xs font-bold text-slate-900 bg-slate-100 px-2.5 py-0.5 rounded-md">{{ $inv->invoice_number }}</span>
                                        <span class="text-xs font-bold text-slate-900">{{ $inv->patient->name ?? 'N/A' }}</span>
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase border whitespace-nowrap {{ $inv->status_badge_class }}"><span class="w-1.5 h-1.5 rounded-full shrink-0 {{ $inv->status === "paid" ? "bg-emerald-500" : ($inv->status === "partially_paid" ? "bg-amber-500" : ($inv->status === "overdue" ? "bg-rose-500" : ($inv->status === "cancelled" ? "bg-slate-400" : "bg-blue-500"))) }}"></span><span>{{ $inv->status_label }}</span></span>
                                    </div>
                                    <p class="text-xs text-slate-500 mt-1">
                                        {{ $inv->invoice_date?->format('d M Y') }} • Grand Total: ₹{{ number_format($inv->grand_total, 2) }} (Due: ₹{{ number_format($inv->balance_due, 2) }})
                                    </p>
                                </div>
                                <a href="{{ route('invoices.show', $inv) }}" class="btn-pill-secondary text-xs py-1.5 px-3.5">
                                    View Invoice
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

        </div>
    @endif

</div>
@endsection

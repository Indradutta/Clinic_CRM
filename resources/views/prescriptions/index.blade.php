@extends('layouts.app')

@section('title', 'Digital Prescriptions')

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-lg bg-brand-50 border border-brand-100/60 text-brand-700 text-xs font-semibold mb-2">
                <span class="w-1.5 h-1.5 rounded-full bg-brand-500"></span>
                Pharmacy & Clinical Orders
            </div>
            <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-slate-900">Digital Prescriptions</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">Author, examine, and print digital medication orders with structured dosing.</p>
        </div>
        @can('prescriptions.create')
        <a href="{{ route('prescriptions.create') }}" 
           class="btn-pill-primary">
            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path>
            </svg>
            <span>Write New Prescription</span>
        </a>
        @endcan
    </div>

    <!-- Top KPI Strip -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white border border-border-default rounded-2xl p-5 shadow-xs">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block">Total Issued</span>
            <span class="text-2xl sm:text-3xl font-bold text-slate-900 mt-1 block tracking-tight">{{ $totalCount }}</span>
        </div>
        <div class="bg-white border border-border-default rounded-2xl p-5 shadow-xs">
            <span class="text-[11px] font-bold uppercase tracking-wider text-brand-500 block">Prescriptions Today</span>
            <span class="text-2xl sm:text-3xl font-bold text-brand-600 mt-1 block tracking-tight">{{ $todayCount }}</span>
        </div>
        <div class="bg-white border border-border-default rounded-2xl p-5 shadow-xs">
            <span class="text-[11px] font-bold uppercase tracking-wider text-indigo-600 block">This Month</span>
            <span class="text-2xl sm:text-3xl font-bold text-indigo-700 mt-1 block tracking-tight">{{ $thisMonthCount }}</span>
        </div>
    </div>

    <!-- Search & Filters -->
    <div class="glass-card rounded-2xl p-5">
        <form method="GET" action="{{ route('prescriptions.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5">
            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Search Prescriptions</label>
                <div class="relative flex items-center">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Rx ID, Patient, Diagnosis..." 
                           class="apple-input w-full !pl-10 !py-2.5">
                </div>
            </div>

            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Date Preset</label>
                <select name="date_preset" class="apple-input !py-2.5">
                    <option value="">All Dates</option>
                    <option value="today" {{ request('date_preset') === 'today' ? 'selected' : '' }}>Today</option>
                    <option value="this_week" {{ request('date_preset') === 'this_week' ? 'selected' : '' }}>This Week</option>
                    <option value="this_month" {{ request('date_preset') === 'this_month' ? 'selected' : '' }}>This Month</option>
                </select>
            </div>

            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Prescribing Doctor</label>
                <select name="doctor_id" class="apple-input !py-2.5">
                    <option value="">All Doctors</option>
                    @foreach($doctors as $doc)
                        <option value="{{ $doc->id }}" {{ request('doctor_id') == $doc->id ? 'selected' : '' }}>
                            {{ $doc->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 btn-pill-primary !py-2.5">
                    Filter
                </button>
                <a href="{{ route('prescriptions.index') }}" class="btn-pill-secondary !py-2.5 !px-3.5">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Prescriptions Table -->
    <div class="table-container">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50/70 text-[11px] uppercase font-bold text-slate-400 tracking-wider border-b border-slate-100">
                    <tr>
                        <th class="px-6 py-4">Rx Number</th>
                        <th class="px-4 py-4">Patient</th>
                        <th class="px-4 py-4">Date</th>
                        <th class="px-4 py-4">Prescribed By</th>
                        <th class="px-4 py-4">Diagnosis</th>
                        <th class="px-4 py-4">Medications</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($prescriptions as $rx)
                        <tr class="hover:bg-white/60 transition">
                            <td class="px-6 py-4 font-mono font-bold text-brand-600">
                                <a href="{{ route('prescriptions.show', $rx) }}" class="hover:underline">
                                    {{ $rx->prescription_number }}
                                </a>
                            </td>
                            <td class="px-4 py-4">
                                <div class="font-bold text-slate-900">
                                    {{ $rx->patient->name ?? 'Unknown Patient' }}
                                </div>
                                <span class="text-[11px] text-slate-400 font-mono">{{ $rx->patient->patient_id ?? '' }}</span>
                            </td>
                            <td class="px-4 py-4 text-slate-800 whitespace-nowrap font-medium">
                                {{ $rx->prescription_date->format('d M Y') }}
                            </td>
                            <td class="px-4 py-4 text-slate-700 whitespace-nowrap">
                                {{ $rx->doctor->name ?? 'Dr. Specialist' }}
                            </td>
                            <td class="px-4 py-4 font-medium text-slate-900">
                                {{ $rx->diagnosis ?: '—' }}
                            </td>
                            <td class="px-4 py-4">
                                <span class="inline-block px-2.5 py-0.5 rounded-md text-[11px] font-bold bg-brand-50 text-brand-700 border border-brand-100/60">
                                    {{ $rx->items->count() }} item(s)
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right space-x-1.5 whitespace-nowrap">
                                <a href="{{ route('prescriptions.show', $rx) }}" 
                                   class="btn-pill-primary !py-1.5 !px-3 !text-[11px]">
                                    View
                                </a>
                                @can('prescriptions.print')
                                <a href="{{ route('prescriptions.print', $rx) }}" target="_blank"
                                   class="btn-pill-secondary !py-1.5 !px-3 !text-[11px]">
                                    Print 🖨
                                </a>
                                @endcan
                                @can('prescriptions.edit')
                                <a href="{{ route('prescriptions.edit', $rx) }}" 
                                   class="btn-pill-secondary !py-1.5 !px-3 !text-[11px]">
                                    Edit
                                </a>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-slate-400">
                                No digital prescriptions found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($prescriptions->hasPages())
            <div class="px-6 py-4 border-t border-slate-100 bg-white/40">
                {{ $prescriptions->links() }}
            </div>
        @endif
    </div>

</div>
@endsection

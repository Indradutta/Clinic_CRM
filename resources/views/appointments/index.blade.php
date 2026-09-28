@extends('layouts.app')

@section('title', 'Appointments & Scheduling')

@section('content')
<div class="space-y-6">

    <!-- Top Header & Primary Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-text-primary">Appointments</h1>
            <p class="text-xs sm:text-sm text-text-secondary mt-0.5">Plan visits and keep today’s schedule up to date.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2.5">
            <!-- View Mode Switcher: List vs Calendar (Liquid Jelly Pill) -->
            <div class="relative inline-flex items-center rounded-xl bg-slate-200/70 p-1 text-xs font-semibold shadow-inner select-none backdrop-blur-sm">
                <!-- Sliding Jelly Blue Pill -->
                <div id="view-mode-pill"
                     class="absolute top-1 bottom-1 rounded-lg bg-brand-500 shadow-sm pointer-events-none transition-all duration-350 ease-[cubic-bezier(0.34,1.56,0.64,1)] {{ request('view') === 'calendar' ? 'left-[calc(50%+2px)] w-[calc(50%-6px)]' : 'left-1 w-[calc(50%-6px)]' }}">
                </div>
                
                <a href="{{ route('appointments.index') }}" 
                   onclick="var p=document.getElementById('view-mode-pill'); if(p){p.style.left='4px'; p.classList.add('jelly-bounce');}"
                   class="relative z-10 px-3.5 py-1.5 rounded-lg transition-colors duration-200 flex items-center space-x-1.5 {{ request('view') !== 'calendar' ? 'text-white font-bold' : 'text-slate-600 hover:text-slate-900' }}">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
                    </svg>
                    <span>List View</span>
                </a>
                
                <a href="{{ route('appointments.index', array_merge(request()->query(), ['view' => 'calendar'])) }}" 
                   onclick="var p=document.getElementById('view-mode-pill'); if(p){p.style.left='calc(50% + 2px)'; p.classList.add('jelly-bounce');}"
                   class="relative z-10 px-3.5 py-1.5 rounded-lg transition-colors duration-200 flex items-center space-x-1.5 {{ request('view') === 'calendar' ? 'text-white font-bold' : 'text-slate-600 hover:text-slate-900' }}">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <span>Calendar</span>
                </a>
            </div>

            <!-- New Appointment Button -->
            @can('appointments.create')
            <a href="{{ route('appointments.create') }}" 
               class="btn-pill-primary inline-flex items-center space-x-2 px-4 py-2 text-xs font-semibold">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 4v16m8-8H4"></path>
                </svg>
                <span>Book Appointment</span>
            </a>
            @endcan
        </div>
    </div>

    <!-- Top KPI Strip for Today's Appointments -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
        <a href="{{ route('appointments.index', ['date_preset' => 'today']) }}"
           class="bg-white border border-border-default rounded-2xl p-3.5 text-center hover:shadow-md hover:border-brand-200 transition">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Today's Total</span>
            <span class="text-xl font-bold text-text-primary mt-0.5 block">{{ $todayStats['total'] }}</span>
        </a>
        <a href="{{ route('appointments.index', ['date_preset' => 'today', 'status' => 'confirmed']) }}"
           class="bg-white border border-border-default rounded-2xl p-3.5 text-center hover:shadow-md hover:border-emerald-200 transition">
            <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-600 block">Confirmed</span>
            <span class="text-xl font-bold text-emerald-700 mt-0.5 block">{{ $todayStats['confirmed'] }}</span>
        </a>
        <a href="{{ route('appointments.index', ['date_preset' => 'today', 'status' => 'checked_in']) }}"
           class="bg-white border border-border-default rounded-2xl p-3.5 text-center hover:shadow-md hover:border-brand-200 transition">
            <span class="text-[10px] font-bold uppercase tracking-wider text-brand-500 block">Checked In</span>
            <span class="text-xl font-bold text-brand-500 mt-0.5 block">{{ $todayStats['checked_in'] }}</span>
        </a>
        <a href="{{ route('appointments.index', ['date_preset' => 'today', 'status' => 'in_consultation']) }}"
           class="bg-white border border-border-default rounded-2xl p-3.5 text-center hover:shadow-md hover:border-indigo-200 transition">
            <span class="text-[10px] font-bold uppercase tracking-wider text-indigo-600 block">In Consult</span>
            <span class="text-xl font-bold text-indigo-700 mt-0.5 block">{{ $todayStats['in_consultation'] }}</span>
        </a>
        <a href="{{ route('appointments.index', ['date_preset' => 'today', 'status' => 'completed']) }}"
           class="bg-white border border-border-default rounded-2xl p-3.5 text-center hover:shadow-md transition">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Completed</span>
            <span class="text-xl font-bold text-slate-700 mt-0.5 block">{{ $todayStats['completed'] }}</span>
        </a>
        <a href="{{ route('appointments.index', ['date_preset' => 'today', 'status' => 'cancelled']) }}"
           class="bg-white border border-border-default rounded-2xl p-3.5 text-center hover:shadow-md hover:border-rose-200 transition">
            <span class="text-[10px] font-bold uppercase tracking-wider text-rose-500 block">Cancelled</span>
            <span class="text-xl font-bold text-rose-600 mt-0.5 block">{{ $todayStats['cancelled'] }}</span>
        </a>
    </div>

    <!-- Search & Filters Container -->
    <div class="glass-card rounded-2xl p-5 space-y-4">
        <form method="GET" action="{{ route('appointments.index') }}" class="space-y-3">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                
                <!-- Search by ID or Patient -->
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Search</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                        <input type="text" 
                               name="search" 
                               value="{{ request('search') }}" 
                               placeholder="Appt ID, Patient name, phone..." 
                               class="w-full !pl-10 pr-4 py-2 apple-input text-xs">
                    </div>
                </div>

                <!-- Date Presets -->
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Date Preset</label>
                    <select name="date_preset" class="w-full px-3.5 py-2 apple-input text-xs">
                        <option value="">All Dates</option>
                        <option value="today" {{ request('date_preset') === 'today' ? 'selected' : '' }}>Today</option>
                        <option value="tomorrow" {{ request('date_preset') === 'tomorrow' ? 'selected' : '' }}>Tomorrow</option>
                        <option value="this_week" {{ request('date_preset') === 'this_week' ? 'selected' : '' }}>This Week</option>
                        <option value="upcoming" {{ request('date_preset') === 'upcoming' ? 'selected' : '' }}>All Upcoming</option>
                        <option value="past" {{ request('date_preset') === 'past' ? 'selected' : '' }}>Past Appointments</option>
                    </select>
                </div>

                <!-- Doctor Filter -->
                @if($doctors->count() > 1)
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Doctor</label>
                        <select name="doctor_id" class="w-full px-3.5 py-2 apple-input text-xs">
                            <option value="">All Doctors</option>
                            @foreach($doctors as $doc)
                                <option value="{{ $doc->id }}" {{ request('doctor_id') == $doc->id ? 'selected' : '' }}>
                                    {{ $doc->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <!-- Status Filter (7 statuses) -->
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Lifecycle Status</label>
                    <select name="status" class="w-full px-3.5 py-2 apple-input text-xs">
                        <option value="">All Statuses</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="confirmed" {{ request('status') === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                        <option value="checked_in" {{ request('status') === 'checked_in' ? 'selected' : '' }}>Checked In</option>
                        <option value="in_consultation" {{ request('status') === 'in_consultation' ? 'selected' : '' }}>In Consultation</option>
                        <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        <option value="no_show" {{ request('status') === 'no_show' ? 'selected' : '' }}>No Show</option>
                    </select>
                </div>

            </div>

            <!-- Second Row: Custom Dates & Payment Status -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 pt-1">
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">From Date</label>
                    <input type="date" name="from_date" value="{{ request('from_date') }}"
                           class="w-full px-3.5 py-2 apple-input text-xs">
                </div>

                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">To Date</label>
                    <input type="date" name="to_date" value="{{ request('to_date') }}"
                           class="w-full px-3.5 py-2 apple-input text-xs">
                </div>

                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Payment Status</label>
                    <select name="payment_status" class="w-full px-3.5 py-2 apple-input text-xs">
                        <option value="">All Payment Statuses</option>
                        <option value="unpaid" {{ request('payment_status') === 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                        <option value="paid" {{ request('payment_status') === 'paid' ? 'selected' : '' }}>Paid</option>
                        <option value="partially_paid" {{ request('payment_status') === 'partially_paid' ? 'selected' : '' }}>Partially Paid</option>
                    </select>
                </div>

                <div class="flex items-end space-x-2">
                    <button type="submit" class="btn-pill-primary flex-1 py-2 text-xs font-semibold">
                        Apply Filters
                    </button>
                    <a href="{{ route('appointments.index') }}" class="btn-pill-secondary px-4 py-2 text-xs font-semibold">
                        Reset
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- Appointment List Table -->
    <div class="table-container">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-white/40 text-[10px] uppercase font-bold text-slate-400 tracking-wider border-b border-slate-100">
                    <tr>
                        <th class="px-6 py-3.5">Appointment ID</th>
                        <th class="px-4 py-3.5">Patient</th>
                        <th class="px-4 py-3.5">Date & Time</th>
                        <th class="px-4 py-3.5">Doctor</th>
                        <th class="px-4 py-3.5">Type</th>
                        <th class="px-4 py-3.5">Status</th>
                        <th class="px-4 py-3.5">Payment</th>
                        <th class="px-6 py-3.5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100/70">
                    @forelse($appointments as $apt)
                        <tr class="hover:bg-white/60 transition">
                            <!-- Appointment ID -->
                            <td class="px-6 py-4 font-mono font-bold text-brand-500">
                                <a href="{{ route('appointments.show', $apt) }}" class="hover:underline">
                                    {{ $apt->appointment_id }}
                                </a>
                            </td>

                            <!-- Patient Details -->
                            <td class="px-4 py-4">
                                @if($apt->patient)
                                    <div class="flex items-center space-x-2.5">
                                        <div class="w-8 h-8 rounded-full bg-brand-50 text-brand-500 border border-brand-100 font-bold flex items-center justify-center text-[11px] shrink-0">
                                            {{ strtoupper(substr($apt->patient->name, 0, 2)) }}
                                        </div>
                                        <div>
                                            @can('patients.view')
                                            <a href="{{ route('patients.show', $apt->patient) }}" class="font-bold text-text-primary hover:text-brand-500 block transition">
                                                {{ $apt->patient->name }}
                                            </a>
                                            @else
                                                <span class="font-bold text-text-primary block">{{ $apt->patient->name }}</span>
                                            @endcan
                                            <span class="text-[11px] text-slate-400 font-mono">{{ $apt->patient->phone }}</span>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-slate-400 italic">Unknown Patient</span>
                                @endif
                            </td>

                            <!-- Date & Time -->
                            <td class="px-4 py-4 whitespace-nowrap">
                                <div class="font-semibold text-text-primary">
                                    {{ $apt->appointment_date->format('d M Y') }}
                                </div>
                                <span class="text-[11px] text-brand-500 font-medium">
                                    🕒 {{ $apt->appointment_time }}
                                </span>
                            </td>

                            <!-- Doctor -->
                            <td class="px-4 py-4 text-slate-700 whitespace-nowrap font-medium">
                                {{ $apt->doctor->name ?? 'Dr. Specialist' }}
                            </td>

                            <!-- Type -->
                            <td class="px-4 py-4 whitespace-nowrap">
                                <span class="inline-block px-2.5 py-0.5 rounded-md text-[11px] font-medium bg-slate-100 text-slate-700">
                                    {{ $apt->appointment_type }}
                                </span>
                            </td>

                            <!-- Lifecycle Status -->
                            <td class="px-4 py-4 whitespace-nowrap">
                                <span class="inline-block px-2.5 py-0.5 rounded-md text-[11px] font-semibold border {{ $apt->status_badge_class }}">
                                    {{ $apt->status_label }}
                                </span>
                            </td>

                            <!-- Payment Status -->
                            <td class="px-4 py-4 whitespace-nowrap">
                                <span class="inline-block px-2 py-0.5 rounded-md text-[10px] font-bold uppercase border {{ $apt->payment_badge_class }}">
                                    {{ $apt->payment_status }}
                                </span>
                            </td>

                            <!-- Row Actions -->
                            <td class="px-6 py-4 text-right space-x-1.5 whitespace-nowrap">
                                <!-- Quick Transition Buttons -->
                                @can('appointments.edit')
                                @if($apt->status === 'pending')
                                    <form action="{{ route('appointments.update-status', $apt) }}" method="POST" class="inline">
                                        @csrf
                                        <input type="hidden" name="status" value="confirmed">
                                        <button type="submit" class="px-3 py-1 rounded-md bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-semibold text-[11px] transition">
                                            Confirm
                                        </button>
                                    </form>
                                @elseif($apt->status === 'confirmed')
                                    <form action="{{ route('appointments.update-status', $apt) }}" method="POST" class="inline">
                                        @csrf
                                        <input type="hidden" name="status" value="checked_in">
                                        <button type="submit" class="px-3 py-1 rounded-md bg-brand-50 hover:bg-brand-100 text-brand-500 font-semibold text-[11px] transition">
                                            Check In
                                        </button>
                                    </form>
                                @elseif($apt->status === 'checked_in')
                                    <form action="{{ route('appointments.update-status', $apt) }}" method="POST" class="inline">
                                        @csrf
                                        <input type="hidden" name="status" value="in_consultation">
                                        <button type="submit" class="btn-pill-primary px-3 py-1 text-[11px] font-semibold shadow-xs">
                                            Start Consult
                                        </button>
                                    </form>
                                @elseif($apt->status === 'in_consultation')
                                    <form action="{{ route('appointments.update-status', $apt) }}" method="POST" class="inline">
                                        @csrf
                                        <input type="hidden" name="status" value="completed">
                                        <button type="submit" class="px-3 py-1 rounded-md bg-slate-800 hover:bg-slate-900 text-white font-semibold text-[11px] transition">
                                            Complete
                                        </button>
                                    </form>
                                @endif
                                @endcan

                                <a href="{{ route('appointments.show', $apt) }}" 
                                   class="px-3 py-1 rounded-md bg-white/80 hover:bg-white text-slate-700 font-semibold text-[11px] border border-white/90 transition shadow-2xs">
                                    Details
                                </a>

                                @can('appointments.edit')
                                <a href="{{ route('appointments.edit', $apt) }}" 
                                   class="px-3 py-1 rounded-md bg-white/80 hover:bg-white text-slate-700 font-semibold text-[11px] border border-white/90 transition shadow-2xs">
                                    Edit
                                </a>
                                @endcan

                                @if(!in_array($apt->status, ['completed', 'cancelled']))
                                    @can('appointments.cancel')
                                    <form action="{{ route('appointments.update-status', $apt) }}" method="POST" class="inline" onsubmit="return confirm('Cancel this appointment?');">
                                        @csrf
                                        <input type="hidden" name="status" value="cancelled">
                                        <button type="submit" class="px-2.5 py-1 rounded-md text-rose-600 bg-rose-50 hover:bg-rose-100 text-[11px] font-semibold transition">
                                            Cancel
                                        </button>
                                    </form>
                                    @endcan
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-slate-400">
                                No appointments found matching your filters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($appointments->hasPages())
            <div class="px-6 py-4 border-t border-slate-100 bg-white/40">
                {{ $appointments->links() }}
            </div>
        @endif
    </div>

</div>
@endsection

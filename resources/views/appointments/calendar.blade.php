@extends('layouts.app')

@section('title', 'Appointments & Scheduling')

@section('content')
<div class="space-y-6">

    <!-- Top Header & Primary Actions (Synchronized 100% with List View to prevent any layout jumping) -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-text-primary">Appointments</h1>
            <p class="text-xs sm:text-sm text-text-secondary mt-0.5">Plan visits and keep today’s schedule up to date. <span class="sr-only">Interactive Calendar</span></p>
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

    <!-- Calendar Controls Bar: Sub-views (Day, Week, Month), Date Navigation, Doctor Filter -->
    <div class="glass-card-elevated rounded-2xl p-4 sm:p-5 border border-white/80 shadow-[0_8px_30px_rgb(0,0,0,0.04)] flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        
        <!-- Left: Month / Week / Day view buttons -->
        <div class="flex items-center gap-3 flex-wrap">
            <div class="inline-flex rounded-xl bg-slate-100/80 p-1 text-xs font-semibold border border-slate-200/50">
                <a href="{{ route('appointments.index', array_merge(request()->query(), ['view' => 'calendar', 'cal_type' => 'day'])) }}" 
                   class="px-3.5 py-1.5 rounded-lg transition {{ $calType === 'day' ? 'bg-brand-500 text-white shadow-xs font-bold' : 'text-slate-600 hover:text-slate-900' }}">
                    Day View
                </a>
                <a href="{{ route('appointments.index', array_merge(request()->query(), ['view' => 'calendar', 'cal_type' => 'week'])) }}" 
                   class="px-3.5 py-1.5 rounded-lg transition {{ $calType === 'week' ? 'bg-brand-500 text-white shadow-xs font-bold' : 'text-slate-600 hover:text-slate-900' }}">
                    Week View
                </a>
                <a href="{{ route('appointments.index', array_merge(request()->query(), ['view' => 'calendar', 'cal_type' => 'month'])) }}" 
                   class="px-3.5 py-1.5 rounded-lg transition {{ $calType === 'month' ? 'bg-brand-500 text-white shadow-xs font-bold' : 'text-slate-600 hover:text-slate-900' }}">
                    Month View
                </a>
            </div>

            <!-- Date Navigation: Prev / Today / Next -->
            <div class="flex items-center gap-1.5">
                @php
                    $prevDate = match($calType) {
                        'day' => $currentDate->copy()->subDay()->toDateString(),
                        'week' => $currentDate->copy()->subWeek()->toDateString(),
                        default => $currentDate->copy()->subMonth()->toDateString(),
                    };
                    $nextDate = match($calType) {
                        'day' => $currentDate->copy()->addDay()->toDateString(),
                        'week' => $currentDate->copy()->addWeek()->toDateString(),
                        default => $currentDate->copy()->addMonth()->toDateString(),
                    };
                @endphp
                <a href="{{ route('appointments.index', array_merge(request()->query(), ['date' => $prevDate])) }}" 
                   class="w-9 h-9 rounded-full bg-white border border-slate-200/70 hover:bg-slate-50 flex items-center justify-center text-slate-600 transition shadow-xs" title="Previous">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </a>
                <a href="{{ route('appointments.index', array_merge(request()->query(), ['date' => now()->toDateString()])) }}" 
                   class="px-3.5 py-1.5 text-xs font-bold bg-white border border-slate-200/70 hover:bg-slate-50 text-slate-700 rounded-lg transition shadow-xs">
                    Today
                </a>
                <a href="{{ route('appointments.index', array_merge(request()->query(), ['date' => $nextDate])) }}" 
                   class="w-9 h-9 rounded-full bg-white border border-slate-200/70 hover:bg-slate-50 flex items-center justify-center text-slate-600 transition shadow-xs" title="Next">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path>
                    </svg>
                </a>
            </div>
        </div>

        <!-- Center: Current Heading -->
        <div class="text-center md:text-left font-bold text-slate-900 text-base sm:text-lg">
            @if($calType === 'day')
                {{ $currentDate->format('l, F d, Y') }}
            @elseif($calType === 'week')
                Week of {{ $currentDate->copy()->startOfWeek()->format('M d') }} – {{ $currentDate->copy()->endOfWeek()->format('M d, Y') }}
            @else
                {{ $currentDate->format('F Y') }}
            @endif
        </div>

        <!-- Right: Doctor Filter (Only if multiple doctors) -->
        @if($doctors->count() > 1)
            <form method="GET" action="{{ route('appointments.index') }}" class="flex items-center gap-2">
                <input type="hidden" name="view" value="calendar">
                <input type="hidden" name="cal_type" value="{{ $calType }}">
                <input type="hidden" name="date" value="{{ $currentDate->toDateString() }}">
                
                <select name="doctor_id" onchange="this.form.submit()" class="apple-input !py-1.5 !px-3.5 !rounded-full !text-xs font-medium">
                    <option value="">All Doctors</option>
                    @foreach($doctors as $doc)
                        <option value="{{ $doc->id }}" {{ request('doctor_id') == $doc->id ? 'selected' : '' }}>
                            {{ $doc->name }}
                        </option>
                    @endforeach
                </select>
            </form>
        @endif

    </div>

    <!-- CALENDAR VIEWS -->
    <div class="glass-card-elevated rounded-2xl border border-white/80 shadow-[0_12px_40px_rgb(0,0,0,0.04)] overflow-hidden p-4 sm:p-7">

        @php
            // Scrubber days for mobile: current month span so user can tap any date without losing navigation
            $scrubberStart = $currentDate->copy()->startOfMonth();
            $scrubberEnd = $currentDate->copy()->endOfMonth();
            $scrubberDays = [];
            $sIter = $scrubberStart->copy();
            while ($sIter <= $scrubberEnd) {
                $scrubberDays[] = $sIter->copy();
                $sIter->addDay();
            }
        @endphp

        <!-- Universal Mobile Date Scrubber (Google Calendar Inspired - ALWAYS present on phone size) -->
        <div class="md:hidden mb-5">
            <div class="flex items-center justify-between mb-2.5">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-700">
                    {{ $currentDate->format('F Y') }}
                </span>
                <div class="flex items-center gap-1">
                    @php
                        $mPrev = $currentDate->copy()->subMonth()->toDateString();
                        $mNext = $currentDate->copy()->addMonth()->toDateString();
                    @endphp
                    <a href="{{ route('appointments.index', array_merge(request()->query(), ['date' => $mPrev])) }}" 
                       class="w-7 h-7 rounded-lg bg-slate-100 flex items-center justify-center text-slate-600 hover:bg-slate-200 transition" title="Previous Month">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
                    </a>
                    <a href="{{ route('appointments.index', array_merge(request()->query(), ['date' => now()->toDateString()])) }}" 
                       class="px-2.5 py-1 text-[11px] font-bold bg-slate-100 rounded-lg text-slate-700 hover:bg-slate-200 transition">
                        Today
                    </a>
                    <a href="{{ route('appointments.index', array_merge(request()->query(), ['date' => $mNext])) }}" 
                       class="w-7 h-7 rounded-lg bg-slate-100 flex items-center justify-center text-slate-600 hover:bg-slate-200 transition" title="Next Month">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>
            </div>

            <!-- Horizontal swipeable date pills -->
            <div id="mobile-scrubber-track" class="flex items-center gap-1.5 overflow-x-auto pb-2 scrollbar-none -mx-1 px-1">
                @foreach($scrubberDays as $sDay)
                    @php
                        $sDayStr = $sDay->toDateString();
                        $sIsToday = $sDay->isToday();
                        $sIsSelected = $sDayStr === $currentDate->toDateString();
                        $sDayAppts = $groupedAppointments[$sDayStr] ?? collect();
                        $sHasAppts = $sDayAppts->count() > 0;
                    @endphp
                    <a href="{{ route('appointments.index', array_merge(request()->query(), ['view' => 'calendar', 'cal_type' => $calType, 'date' => $sDayStr])) }}#day-{{ $sDayStr }}"
                       id="scrubber-pill-{{ $sDayStr }}"
                       class="flex flex-col items-center justify-center min-w-[3.25rem] py-2 px-1 rounded-2xl border transition-all shrink-0
                              {{ $sIsSelected ? 'bg-brand-500 text-white border-brand-500 shadow-md ring-2 ring-brand-300' : ($sIsToday ? 'bg-brand-50 border-brand-300 text-brand-700 font-bold' : 'bg-white border-slate-200/80 text-slate-700 hover:border-brand-300') }}">
                        <span class="text-[10px] font-bold uppercase tracking-wider {{ $sIsSelected ? 'text-brand-100' : ($sIsToday ? 'text-brand-600' : 'text-slate-400') }}">
                            {{ $sDay->format('D') }}
                        </span>
                        <span class="text-sm font-bold mt-0.5">
                            {{ $sDay->format('j') }}
                        </span>
                        @if($sHasAppts)
                            <span class="w-1.5 h-1.5 rounded-full mt-1 {{ $sIsSelected ? 'bg-white' : 'bg-brand-500' }}"></span>
                        @else
                            <span class="w-1.5 h-1.5 mt-1 opacity-0"></span>
                        @endif
                    </a>
                @endforeach
            </div>
        </div>

        <!-- 1. MONTH VIEW -->
        @if($calType === 'month')
            @php
                $startOfCalendar = $currentDate->copy()->startOfMonth()->startOfWeek(Carbon\Carbon::MONDAY);
                $endOfCalendar = $currentDate->copy()->endOfMonth()->endOfWeek(Carbon\Carbon::SUNDAY);
                $days = [];
                $dayIterator = $startOfCalendar->copy();
                while ($dayIterator <= $endOfCalendar) {
                    $days[] = $dayIterator->copy();
                    $dayIterator->addDay();
                }
                $appointmentsByDay = $appointments->groupBy(fn($a) => $a->appointment_date->toDateString());
            @endphp

            <!-- Mobile View (Google Calendar Inspired Schedule Stream) -->
            <div class="md:hidden space-y-4">
                <!-- If the user tapped a date with no appointments, show clean banner for that day -->
                @if(!$appointmentsByDay->has($currentDate->toDateString()) && $appointments->isNotEmpty())
                    <div id="day-{{ $currentDate->toDateString() }}" class="p-3.5 rounded-2xl bg-brand-50/50 border border-brand-200/60 flex items-center justify-between gap-3">
                        <div>
                            <span class="text-xs font-bold text-brand-800 block">{{ $currentDate->format('l, F d') }}</span>
                            <span class="text-[11px] text-slate-500">No appointments scheduled for this date</span>
                        </div>
                        @can('appointments.create')
                        <a href="{{ route('appointments.create', ['date' => $currentDate->toDateString()]) }}" class="btn-pill-primary !text-[11px] !py-1 !px-2.5 shrink-0">
                            + Book Slot
                        </a>
                        @endcan
                    </div>
                @endif

                <!-- Google Calendar Agenda Stream -->
                @if($appointments->isEmpty())
                    <div class="py-12 text-center text-slate-400 bg-white/60 rounded-2xl border border-slate-100 p-6">
                        <div class="w-12 h-12 mx-auto rounded-full bg-brand-50 flex items-center justify-center text-brand-500 mb-3 border border-brand-100/60">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <p class="text-sm font-bold text-slate-700">No appointments scheduled this month</p>
                        <p class="text-xs text-slate-400 mt-1">Tap any day in the scrubber above or book a new visit.</p>
                        @can('appointments.create')
                        <a href="{{ route('appointments.create') }}" class="btn-pill-primary inline-flex mt-4 text-xs py-2 px-4">
                            + Book Appointment
                        </a>
                        @endcan
                    </div>
                @else
                    @foreach($appointmentsByDay as $dateKey => $dayGroup)
                        @php
                            $groupDate = Carbon\Carbon::parse($dateKey);
                            $isGroupToday = $groupDate->isToday();
                            $isGroupTomorrow = $groupDate->isTomorrow();
                            $isGroupSelected = $dateKey === $currentDate->toDateString();
                        @endphp
                        <div id="day-{{ $dateKey }}" class="space-y-2.5 scroll-mt-20">
                            <!-- Date Divider -->
                            <div class="flex items-center justify-between pt-2 pb-1 border-b border-slate-100">
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-bold uppercase tracking-wider {{ $isGroupSelected ? 'text-brand-600' : ($isGroupToday ? 'text-brand-600' : 'text-slate-800') }}">
                                        @if($isGroupToday)
                                            Today · {{ $groupDate->format('D, M d') }}
                                        @elseif($isGroupTomorrow)
                                            Tomorrow · {{ $groupDate->format('D, M d') }}
                                        @else
                                            {{ $groupDate->format('l, M d') }}
                                        @endif
                                    </span>
                                    @if($isGroupToday)
                                        <span class="px-2 py-0.5 rounded-full text-[9px] font-bold bg-brand-50 text-brand-700 border border-brand-100">Today</span>
                                    @endif
                                </div>
                                <span class="text-[11px] font-bold text-slate-400">{{ $dayGroup->count() }} {{ Str::plural('visit', $dayGroup->count()) }}</span>
                            </div>

                            <!-- Google Calendar Style Cards -->
                            @foreach($dayGroup as $apt)
                                @php
                                    $accentColor = match($apt->status) {
                                        'confirmed' => 'bg-emerald-500',
                                        'checked_in' => 'bg-brand-500',
                                        'in_consultation' => 'bg-indigo-500',
                                        'completed' => 'bg-slate-400',
                                        default => 'bg-rose-500',
                                    };
                                @endphp
                                <div class="relative flex items-stretch rounded-2xl bg-white border border-slate-100 shadow-2xs hover:shadow-xs transition overflow-hidden p-3.5">
                                    <div class="w-1.5 rounded-full mr-3 shrink-0 {{ $accentColor }}"></div>
                                    
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center justify-between gap-2 mb-1.5">
                                            <span class="inline-flex items-center text-xs font-bold text-slate-800">
                                                <svg class="w-3.5 h-3.5 mr-1 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                                {{ $apt->appointment_time }}
                                            </span>
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $apt->status_badge_class }}">
                                                {{ $apt->status_label }}
                                            </span>
                                        </div>

                                        <div class="flex items-center gap-2 mb-1">
                                            <div class="w-6 h-6 rounded-full bg-brand-50 text-brand-700 font-bold text-xs flex items-center justify-center shrink-0">
                                                {{ substr($apt->patient->name ?? 'P', 0, 1) }}
                                            </div>
                                            <a href="{{ route('appointments.show', $apt) }}" class="text-sm font-bold text-slate-900 truncate hover:text-brand-600 transition">
                                                {{ $apt->patient->name ?? 'Patient' }}
                                            </a>
                                            <span class="text-[10px] font-mono text-slate-400 shrink-0">#{{ $apt->appointment_id }}</span>
                                        </div>

                                        <p class="text-xs text-slate-500 truncate">
                                            <span class="font-medium text-slate-700">Dr. {{ $apt->doctor->name ?? 'Specialist' }}</span> • {{ $apt->appointment_type }}
                                        </p>

                                        <div class="flex items-center justify-between pt-2.5 mt-2 border-t border-slate-50">
                                            @can('appointments.edit')
                                                @if($apt->status === 'confirmed')
                                                    <form action="{{ route('appointments.update-status', $apt) }}" method="POST">
                                                        @csrf
                                                        <input type="hidden" name="status" value="checked_in">
                                                        <button type="submit" class="btn-pill-secondary !text-[11px] !py-1 !px-2.5">
                                                            Check In
                                                        </button>
                                                    </form>
                                                @elseif($apt->status === 'checked_in')
                                                    <form action="{{ route('appointments.update-status', $apt) }}" method="POST">
                                                        @csrf
                                                        <input type="hidden" name="status" value="in_consultation">
                                                        <button type="submit" class="btn-pill-primary !text-[11px] !py-1 !px-2.5">
                                                            Consultation
                                                        </button>
                                                    </form>
                                                @else
                                                    <span></span>
                                                @endif
                                            @else
                                                <span></span>
                                            @endcan
                                            
                                            <a href="{{ route('appointments.show', $apt) }}" class="text-xs font-bold text-brand-600 hover:text-brand-800 flex items-center gap-1">
                                                <span>Details</span>
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                @endif
            </div>

            <!-- Desktop View (7-Column Full Calendar Grid) -->
            <div class="hidden md:grid md:grid-cols-7 gap-1.5 sm:gap-2">
                <!-- Day of week headers -->
                @foreach(['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'] as $dName)
                    <div class="py-2.5 text-center text-[11px] font-bold uppercase tracking-wider text-slate-400">
                        {{ $dName }}
                    </div>
                @endforeach

                <!-- Days cells -->
                @foreach($days as $day)
                    @php
                        $dayStr = $day->toDateString();
                        $isCurrentMonth = $day->month === $currentDate->month;
                        $isToday = $day->isToday();
                        $dayAppointments = $groupedAppointments[$dayStr] ?? collect();
                    @endphp
                    <div class="min-h-[115px] p-2.5 rounded-2xl border transition-all flex flex-col justify-between {{ $isCurrentMonth ? 'bg-white/80 border-slate-100/90 shadow-2xs hover:shadow-xs' : 'bg-slate-50/40 border-transparent text-slate-300' }} {{ $isToday ? 'ring-2 ring-brand-500/80 bg-brand-50/30' : '' }}">
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-xs font-bold {{ $isToday ? 'w-6 h-6 rounded-full bg-brand-500 text-white flex items-center justify-center shadow-xs' : ($isCurrentMonth ? 'text-slate-800' : 'text-slate-400') }}">
                                {{ $day->format('j') }}
                            </span>
                            @if($dayAppointments->count() > 0)
                                <span class="text-[10px] font-bold text-brand-700 bg-brand-50 px-2 py-0.5 rounded-md border border-brand-100/60">
                                    {{ $dayAppointments->count() }}
                                </span>
                            @endif
                        </div>

                        <!-- Mini Appointment Chips -->
                        <div class="space-y-1 overflow-y-auto max-h-24 py-1">
                            @foreach($dayAppointments->take(3) as $apt)
                                <a href="{{ route('appointments.show', $apt) }}" 
                                   class="block px-2 py-1 rounded-xl text-[10px] truncate border font-medium transition hover:scale-[1.02] {{ $apt->status_badge_class }}"
                                   title="{{ $apt->appointment_time }} - {{ $apt->patient->name ?? 'Patient' }} ({{ $apt->status_label }})">
                                    <span class="font-bold">{{ $apt->appointment_time }}</span> {{ $apt->patient->name ?? 'Patient' }}
                                </a>
                            @endforeach
                            @if($dayAppointments->count() > 3)
                                <a href="{{ route('appointments.index', array_merge(request()->query(), ['view' => 'calendar', 'cal_type' => 'day', 'date' => $dayStr])) }}" 
                                   class="text-[10px] text-brand-600 font-bold block text-center hover:underline">
                                    +{{ $dayAppointments->count() - 3 }} more
                                </a>
                            @endif
                        </div>

                        <!-- Quick add shortcut -->
                        @can('appointments.create')
                        <div class="text-right pt-1 opacity-100 sm:opacity-0 sm:hover:opacity-100 transition">
                            <a href="{{ route('appointments.create', ['date' => $dayStr]) }}" class="text-[10px] text-brand-600 hover:text-brand-800 font-bold">+ Add</a>
                        </div>
                        @endcan
                    </div>
                @endforeach
            </div>

        <!-- 2. WEEK VIEW -->
        @elseif($calType === 'week')
            @php
                $weekDays = [];
                $wStart = $currentDate->copy()->startOfWeek(Carbon\Carbon::MONDAY);
                for($i = 0; $i < 7; $i++) {
                    $weekDays[] = $wStart->copy()->addDays($i);
                }
                $appointmentsByDay = $appointments->groupBy(fn($a) => $a->appointment_date->toDateString());
            @endphp

            <!-- Mobile Week View (Clean Google Calendar Stream) -->
            <div class="md:hidden space-y-4">
                @if($appointments->isEmpty())
                    <div class="py-12 text-center text-slate-400 bg-white/60 rounded-2xl border border-slate-100 p-6">
                        <p class="text-sm font-bold text-slate-700">No appointments scheduled for this week</p>
                        @can('appointments.create')
                        <a href="{{ route('appointments.create') }}" class="btn-pill-primary inline-flex mt-4 text-xs py-2 px-4">
                            + Book Appointment
                        </a>
                        @endcan
                    </div>
                @else
                    @foreach($weekDays as $wDay)
                        @php
                            $dayStr = $wDay->toDateString();
                            $dayAppointments = $groupedAppointments[$dayStr] ?? collect();
                            $isToday = $wDay->isToday();
                        @endphp
                        @if($dayAppointments->count() > 0)
                            <div id="day-{{ $dayStr }}" class="space-y-2.5">
                                <div class="flex items-center justify-between pt-2 pb-1 border-b border-slate-100">
                                    <span class="text-xs font-bold uppercase tracking-wider {{ $isToday ? 'text-brand-600' : 'text-slate-800' }}">
                                        {{ $wDay->format('l, M d') }} {{ $isToday ? '(Today)' : '' }}
                                    </span>
                                    <span class="text-[11px] font-bold text-slate-400">{{ $dayAppointments->count() }} visits</span>
                                </div>
                                @foreach($dayAppointments as $apt)
                                    @php
                                        $accentColor = match($apt->status) {
                                            'confirmed' => 'bg-emerald-500',
                                            'checked_in' => 'bg-brand-500',
                                            'in_consultation' => 'bg-indigo-500',
                                            'completed' => 'bg-slate-400',
                                            default => 'bg-rose-500',
                                        };
                                    @endphp
                                    <div class="relative flex items-stretch rounded-2xl bg-white border border-slate-100 shadow-2xs hover:shadow-xs transition overflow-hidden p-3.5">
                                        <div class="w-1.5 rounded-full mr-3 shrink-0 {{ $accentColor }}"></div>
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center justify-between gap-2 mb-1.5">
                                                <span class="inline-flex items-center text-xs font-bold text-slate-800">
                                                    {{ $apt->appointment_time }}
                                                </span>
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $apt->status_badge_class }}">
                                                    {{ $apt->status_label }}
                                                </span>
                                            </div>
                                            <a href="{{ route('appointments.show', $apt) }}" class="text-sm font-bold text-slate-900 truncate hover:text-brand-600 block">
                                                {{ $apt->patient->name ?? 'Patient' }}
                                            </a>
                                            <p class="text-xs text-slate-500 truncate mt-0.5">
                                                Dr. {{ $apt->doctor->name ?? 'Specialist' }} • {{ $apt->appointment_type }}
                                            </p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    @endforeach
                @endif
            </div>

            <!-- Desktop Week View (7 columns) -->
            <div class="hidden md:grid md:grid-cols-7 gap-3">
                @foreach($weekDays as $wDay)
                    @php
                        $dayStr = $wDay->toDateString();
                        $isToday = $wDay->isToday();
                        $dayAppointments = $groupedAppointments[$dayStr] ?? collect();
                    @endphp
                    <div class="rounded-2xl border {{ $isToday ? 'border-brand-300 bg-brand-50/30 ring-1 ring-brand-300' : 'border-slate-100 bg-white/70' }} p-3.5 flex flex-col h-[480px] shadow-2xs backdrop-blur-sm">
                        <div class="text-center pb-2.5 border-b border-slate-100 mb-2">
                            <span class="text-[11px] uppercase font-bold text-slate-400 block">{{ $wDay->format('D') }}</span>
                            <span class="text-sm font-bold {{ $isToday ? 'text-brand-600' : 'text-slate-800' }}">
                                {{ $wDay->format('M d') }}
                            </span>
                        </div>

                        <div class="flex-1 overflow-y-auto space-y-2 pr-1">
                            @forelse($dayAppointments as $apt)
                                <div class="p-2.5 bg-white/95 rounded-xl border border-slate-100 shadow-2xs hover:shadow-xs transition">
                                    <div class="flex items-center justify-between gap-1">
                                        <span class="text-[10px] font-bold text-brand-600">🕒 {{ $apt->appointment_time }}</span>
                                        <span class="px-2 py-0.5 rounded-md text-[9px] font-bold border {{ $apt->status_badge_class }}">
                                            {{ $apt->status_label }}
                                        </span>
                                    </div>
                                    <a href="{{ route('appointments.show', $apt) }}" class="font-bold text-xs text-slate-900 hover:text-brand-600 block mt-1 truncate">
                                        {{ $apt->patient->name ?? 'Patient' }}
                                    </a>
                                    <span class="text-[10px] text-slate-400 block truncate">{{ $apt->appointment_type }}</span>
                                </div>
                            @empty
                                <div class="text-center py-12 text-slate-400 text-xs">
                                    No visits
                                </div>
                            @endforelse
                        </div>

                        @can('appointments.create')
                        <a href="{{ route('appointments.create', ['date' => $dayStr]) }}" 
                           class="mt-2 block py-2 text-center text-xs font-bold text-brand-600 hover:bg-brand-50 rounded-xl transition border border-dashed border-brand-200">
                            + Book Slot
                        </a>
                        @endcan
                    </div>
                @endforeach
            </div>

        <!-- 3. DAY VIEW -->
        @else
            <div class="space-y-4">
                <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                    <div>
                        <h2 class="text-base sm:text-lg font-bold text-slate-900">Timeline Schedule</h2>
                        <p class="text-xs sm:text-sm text-slate-500">Appointments scheduled for {{ $currentDate->format('l, d F Y') }}</p>
                    </div>
                    @can('appointments.create')
                    <a href="{{ route('appointments.create', ['date' => $currentDate->toDateString()]) }}" 
                       class="btn-pill-primary !text-xs !py-2 !px-4">
                        + Schedule for Today
                    </a>
                    @endcan
                </div>

                @if($appointments->isEmpty())
                    <div class="py-16 text-center text-slate-400">
                        <div class="w-14 h-14 mx-auto rounded-full bg-brand-50 flex items-center justify-center text-brand-500 mb-3 border border-brand-100/60">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <p class="text-sm font-bold text-slate-700">No appointments scheduled on this day</p>
                        <p class="text-xs text-slate-400 mt-1">Tap another day in the date scrubber above or book a new appointment.</p>
                        @can('appointments.create')
                        <a href="{{ route('appointments.create', ['date' => $currentDate->toDateString()]) }}" class="btn-pill-primary inline-flex mt-4 text-xs py-2 px-4">
                            + Book Slot
                        </a>
                        @endcan
                    </div>
                @else
                    <div class="space-y-2.5">
                        @foreach($appointments as $apt)
                            <div class="p-4 rounded-2xl bg-white border border-slate-100/80 shadow-2xs hover:shadow-xs flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 transition">
                                <div class="flex items-start gap-4">
                                    <div class="w-16 shrink-0 font-mono text-xs font-bold text-brand-600 bg-brand-50 py-1.5 px-2 rounded-xl text-center border border-brand-100/60">
                                        {{ $apt->appointment_time }}
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <a href="{{ route('appointments.show', $apt) }}" class="text-sm font-bold text-slate-900 hover:text-brand-600 transition">
                                                {{ $apt->patient->name ?? 'Unknown Patient' }}
                                            </a>
                                            <span class="font-mono text-xs text-slate-400">({{ $apt->appointment_id }})</span>
                                            <span class="px-2.5 py-0.5 rounded-md text-[10px] font-bold border {{ $apt->status_badge_class }}">
                                                {{ $apt->status_label }}
                                            </span>
                                        </div>
                                        <p class="text-xs text-slate-500 mt-1">
                                            Doctor: <span class="font-bold text-slate-700">{{ $apt->doctor->name ?? 'Dr. Specialist' }}</span> • Type: <span class="font-medium">{{ $apt->appointment_type }}</span>
                                            @if($apt->reason_for_visit)
                                                • <span class="italic text-slate-400">"{{ Str::limit($apt->reason_for_visit, 40) }}"</span>
                                            @endif
                                        </p>
                                    </div>
                                </div>

                                <div class="flex items-center gap-2 shrink-0">
                                    @can('appointments.edit')
                                    @if($apt->status === 'confirmed')
                                        <form action="{{ route('appointments.update-status', $apt) }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="status" value="checked_in">
                                            <button type="submit" class="btn-pill-secondary !text-xs !py-1.5 !px-3.5">
                                                Check In
                                            </button>
                                        </form>
                                    @elseif($apt->status === 'checked_in')
                                        <form action="{{ route('appointments.update-status', $apt) }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="status" value="in_consultation">
                                            <button type="submit" class="btn-pill-primary !text-xs !py-1.5 !px-3.5">
                                                Start Consultation
                                            </button>
                                        </form>
                                    @endif
                                    @endcan

                                    <a href="{{ route('appointments.show', $apt) }}" class="btn-pill-secondary !text-xs !py-1.5 !px-3.5">
                                        View
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @endif

    </div>

</div>

<!-- Auto-scroll active date pill into view in the mobile horizontal scrubber -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    var activePill = document.getElementById('scrubber-pill-{{ $currentDate->toDateString() }}');
    if (activePill) {
        activePill.scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' });
    }
});
</script>
@endsection

@extends('layouts.app')

@section('title', 'Appointment Details - ' . $appointment->appointment_id)

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Page Header & Action Buttons -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2.5 flex-wrap mb-2">
                <span class="font-mono text-xs font-bold text-brand-700 bg-brand-50 px-3 py-1 rounded-lg border border-brand-100/60">
                    {{ $appointment->appointment_id }}
                </span>
                <span class="px-3 py-1 rounded-lg text-xs font-bold border {{ $appointment->status_badge_class }}">
                    {{ $appointment->status_label }}
                </span>
                <span class="px-2.5 py-0.5 rounded-md text-[11px] font-bold uppercase border {{ $appointment->payment_badge_class }}">
                    {{ $appointment->payment_status }}
                </span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-slate-900">
                Appointment with {{ $appointment->patient->name ?? 'Patient' }}
            </h1>
        </div>
        <div class="flex items-center gap-2.5 flex-wrap">
            @can('appointments.edit')
                <a href="{{ route('appointments.edit', $appointment) }}" class="btn-pill-primary">Edit / Reschedule</a>
            @endcan
            <a href="{{ route('appointments.index') }}" 
               class="btn-pill-secondary">
                &larr; All Appointments
            </a>
        </div>
    </div>

    <!-- LIFECYCLE ACTION COCKPIT -->
    @if(auth()->user()->can('appointments.edit') || auth()->user()->can('appointments.cancel'))
    <div class="glass-card-elevated rounded-2xl p-6 sm:p-7 border border-brand-100 bg-gradient-to-br from-white/90 via-brand-50/20 to-white/80 shadow-[0_8px_30px_rgb(0,0,0,0.04)] ">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-lg bg-brand-50 border border-brand-100/60 text-brand-700 text-xs font-semibold mb-1.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-brand-500 animate-pulse"></span>
                    Clinical Lifecycle Flow
                </div>
                <h3 class="text-lg font-bold text-slate-900">Manage Visit Workflow</h3>
                <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Advance the appointment through arrival, doctor consultation, and completion.</p>
            </div>

            <div class="flex items-center flex-wrap gap-2.5">
                @can('appointments.edit')
                @if($appointment->status === 'pending')
                    <form action="{{ route('appointments.update-status', $appointment) }}" method="POST">
                        @csrf
                        <input type="hidden" name="status" value="confirmed">
                        <button type="submit" class="btn-pill-primary !bg-emerald-600 hover:!bg-emerald-700 !shadow-emerald-600/20">
                            ✓ Confirm Appointment
                        </button>
                    </form>
                @endif

                @if(in_array($appointment->status, ['pending', 'confirmed']))
                    <form action="{{ route('appointments.update-status', $appointment) }}" method="POST">
                        @csrf
                        <input type="hidden" name="status" value="checked_in">
                        <button type="submit" class="btn-pill-primary">
                            🏥 Check In Patient
                        </button>
                    </form>
                @endif

                @if($appointment->status === 'checked_in')
                    <form action="{{ route('appointments.update-status', $appointment) }}" method="POST">
                        @csrf
                        <input type="hidden" name="status" value="in_consultation">
                        <button type="submit" class="btn-pill-primary !bg-indigo-600 hover:!bg-indigo-700 !shadow-indigo-600/20">
                            🩺 Start Consultation
                        </button>
                    </form>
                @endif

                @if($appointment->status === 'in_consultation')
                    <form action="{{ route('appointments.update-status', $appointment) }}" method="POST">
                        @csrf
                        <input type="hidden" name="status" value="completed">
                        <button type="submit" class="btn-pill-primary !bg-emerald-600 hover:!bg-emerald-700 !shadow-emerald-600/20">
                            ✓ Mark Completed
                        </button>
                    </form>
                @endif

                @if(!in_array($appointment->status, ['completed', 'cancelled']))
                    <form action="{{ route('appointments.update-status', $appointment) }}" method="POST" onsubmit="return confirm('Mark this patient as No-Show?');">
                        @csrf
                        <input type="hidden" name="status" value="no_show">
                        <button type="submit" class="btn-pill-secondary !text-slate-600 hover:!text-slate-900">
                            Mark No Show
                        </button>
                    </form>

                @endif
                @endcan
                @can('appointments.cancel')
                    @if(!in_array($appointment->status, ['completed', 'cancelled']))
                        <form action="{{ route('appointments.update-status', $appointment) }}" method="POST" onsubmit="return confirm('Cancel this appointment?');">
                            @csrf
                            <input type="hidden" name="status" value="cancelled">
                            <button type="submit" class="btn-pill-secondary !text-rose-600 hover:!bg-rose-50 hover:!text-rose-700 !border-rose-200/80">
                                Cancel Visit
                            </button>
                        </form>
                    @endif
                @endcan
            </div>
        </div>
    </div>
    @endif

    <!-- Appointment & Patient Summary Cards -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Left: Appointment Details -->
        <div class="lg:col-span-2 space-y-6">
            <div class="glass-card-elevated rounded-2xl p-6 sm:p-7 border border-white/80 shadow-[0_12px_40px_rgb(0,0,0,0.04)] space-y-5">
                <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider border-b border-slate-100 pb-3.5 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-brand-500"></span>
                    <span>Appointment Details</span>
                </h3>

                <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 text-xs">
                    <div class="p-3.5 bg-white/70 rounded-2xl border border-slate-100 shadow-2xs">
                        <span class="text-slate-400 font-bold uppercase text-[10px] block">Scheduled Date</span>
                        <p class="text-slate-900 font-bold text-sm mt-1">
                            {{ $appointment->appointment_date->format('d M Y') }}
                        </p>
                    </div>

                    <div class="p-3.5 bg-white/70 rounded-2xl border border-slate-100 shadow-2xs">
                        <span class="text-slate-400 font-bold uppercase text-[10px] block">Scheduled Time</span>
                        <p class="text-brand-600 font-bold text-sm mt-1">
                            🕒 {{ $appointment->appointment_time }}
                        </p>
                    </div>

                    <div class="p-3.5 bg-white/70 rounded-2xl border border-slate-100 shadow-2xs">
                        <span class="text-slate-400 font-bold uppercase text-[10px] block">Appointment Type</span>
                        <p class="text-slate-900 font-semibold mt-1">
                            {{ $appointment->appointment_type }}
                        </p>
                    </div>

                    <div class="sm:col-span-2 p-3.5 bg-white/70 rounded-2xl border border-slate-100 shadow-2xs">
                        <span class="text-slate-400 font-bold uppercase text-[10px] block">Assigned Doctor</span>
                        <p class="text-slate-900 font-bold text-sm mt-1 flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            {{ $appointment->doctor->name ?? 'Dr. Specialist' }}
                        </p>
                    </div>

                    <div class="p-3.5 bg-white/70 rounded-2xl border border-slate-100 shadow-2xs">
                        <span class="text-slate-400 font-bold uppercase text-[10px] block">Booking Date</span>
                        <p class="text-slate-600 mt-1">
                            {{ $appointment->created_at->format('d M Y, h:i A') }}
                        </p>
                    </div>
                </div>

                @if($appointment->reason_for_visit)
                    <div class="pt-2">
                        <span class="text-slate-400 font-bold uppercase text-[10px] block mb-1">Reason for Visit / Chief Complaint</span>
                        <p class="text-slate-800 text-xs p-4 bg-white/80 rounded-2xl border border-slate-100 leading-relaxed font-medium shadow-2xs">
                            {{ $appointment->reason_for_visit }}
                        </p>
                    </div>
                @endif

                @if($appointment->notes)
                    <div class="pt-2">
                        <span class="text-slate-400 font-bold uppercase text-[10px] block mb-1">Clinical / Reception Notes</span>
                        <p class="text-slate-800 text-xs p-4 bg-white/80 rounded-2xl border border-slate-100 leading-relaxed shadow-2xs">
                            {{ $appointment->notes }}
                        </p>
                    </div>
                @endif

                @if($appointment->cancelled_reason)
                    <div class="p-4 rounded-2xl bg-rose-50/80 border border-rose-200/80">
                        <span class="text-rose-800 font-bold uppercase text-[10px] block">Cancellation Reason</span>
                        <p class="text-rose-700 text-xs mt-1">{{ $appointment->cancelled_reason }}</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Right: Patient Quick Info & Clinical Alerts -->
        <div class="space-y-6">
            @if($appointment->patient)
                <div class="glass-card-elevated rounded-2xl p-6 sm:p-7 border border-white/80 shadow-[0_12px_40px_rgb(0,0,0,0.04)] space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider">Patient Summary</h3>
                        <a href="{{ route('patients.show', $appointment->patient) }}" class="text-xs font-bold text-brand-600 hover:text-brand-700">
                            Full Profile &rarr;
                        </a>
                    </div>

                    <div class="flex items-center gap-3.5">
                        <div class="w-12 h-12 rounded-full bg-brand-500 text-white flex items-center justify-center font-bold text-base shadow-xs">
                            {{ strtoupper(substr($appointment->patient->name, 0, 2)) }}
                        </div>
                        <div>
                            <h4 class="font-bold text-slate-900 text-sm">{{ $appointment->patient->name }}</h4>
                            <p class="text-xs text-slate-400 font-mono">{{ $appointment->patient->patient_id }}</p>
                        </div>
                    </div>

                    <div class="space-y-2 text-xs divide-y divide-slate-100 pt-1">
                        <div class="flex justify-between py-2">
                            <span class="text-slate-400 font-medium">Gender & Age:</span>
                            <span class="font-semibold text-slate-800">{{ $appointment->patient->gender }}, {{ $appointment->patient->age ?? 'N/A' }} yrs</span>
                        </div>
                        <div class="flex justify-between py-2">
                            <span class="text-slate-400 font-medium">Blood Group:</span>
                            <span class="font-bold text-rose-600">{{ $appointment->patient->blood_group ?? '—' }}</span>
                        </div>
                        <div class="flex justify-between py-2">
                            <span class="text-slate-400 font-medium">Phone:</span>
                            <span class="font-semibold text-slate-800">{{ $appointment->patient->phone }}</span>
                        </div>
                        @if($appointment->patient->emergency_contact_phone)
                            <div class="flex justify-between py-2">
                                <span class="text-slate-400 font-medium">Emergency:</span>
                                <span class="text-slate-800">{{ $appointment->patient->emergency_contact_name }} ({{ $appointment->patient->emergency_contact_phone }})</span>
                            </div>
                        @endif
                    </div>

                    <!-- Clinical Allergies Warning -->
                    @if(!empty($appointment->patient->allergies))
                        <div class="p-3.5 bg-rose-50/80 border border-rose-200/80 rounded-2xl text-xs">
                            <span class="text-rose-800 font-bold uppercase text-[10px] block">⚠️ High-Risk Allergies</span>
                            <p class="text-rose-700 font-medium mt-1">{{ $appointment->patient->allergies }}</p>
                        </div>
                    @endif

                    <!-- Chronic Conditions -->
                    @if(!empty($appointment->patient->chronic_conditions))
                        <div class="p-3.5 bg-amber-50/70 border border-amber-200/80 rounded-2xl text-xs">
                            <span class="text-amber-800 font-bold uppercase text-[10px] block">Chronic Conditions</span>
                            <p class="text-amber-900 font-medium mt-1">{{ $appointment->patient->chronic_conditions }}</p>
                        </div>
                    @endif
                </div>
            @endif
        </div>

    </div>

</div>
@endsection

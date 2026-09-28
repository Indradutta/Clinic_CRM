@extends('layouts.app')

@section('title', 'Edit Appointment - ' . $appointment->appointment_id)

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2 mb-2">
                <span class="font-mono text-xs font-bold text-brand-600 bg-brand-50 px-2.5 py-0.5 rounded-md border border-brand-100/60">{{ $appointment->appointment_id }}</span>
                <span class="px-2.5 py-0.5 rounded-md text-xs font-bold border {{ $appointment->status_badge_class }}">{{ $appointment->status_label }}</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-slate-900">Edit / Reschedule Appointment</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">Modify appointment details. Change its status with the workflow actions on the appointment page.</p>
        </div>
        <a href="{{ route('appointments.show', $appointment) }}" class="btn-pill-secondary">
            &larr; View Details
        </a>
    </div>

    <form method="POST" action="{{ route('appointments.update', $appointment) }}" class="space-y-6" novalidate>
        @csrf
        @method('PUT')

        <div class="glass-card-elevated rounded-2xl p-6 sm:p-8 border border-white/80 shadow-[0_12px_40px_rgb(0,0,0,0.04)] space-y-6">
            <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider border-b border-slate-100 pb-3.5 flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-brand-500"></span>
                <span>Appointment Information</span>
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <!-- Patient (Read-only banner) -->
                <div class="sm:col-span-2 p-4 bg-white/70 rounded-2xl border border-slate-200/70 flex items-center justify-between shadow-2xs">
                    <div>
                        <span class="text-[10px] font-bold text-slate-400 uppercase block tracking-wider">Patient</span>
                        <span class="text-sm font-bold text-slate-900">{{ $appointment->patient->name ?? 'Unknown' }}</span>
                        <span class="text-xs text-slate-500 font-mono">({{ $appointment->patient->patient_id ?? '' }})</span>
                    </div>
                    <span class="text-xs font-medium text-slate-600 bg-slate-100 px-3 py-1 rounded-lg">📞 {{ $appointment->patient->phone ?? '' }}</span>
                </div>

                <!-- Doctor Selection -->
                @if($doctors->count() > 1)
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Doctor *</label>
                        <select name="doctor_id" required class="apple-input">
                            @foreach($doctors as $doc)
                                <option value="{{ $doc->id }}" {{ old('doctor_id', $appointment->doctor_id) == $doc->id ? 'selected' : '' }}>
                                    {{ $doc->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('doctor_id') <p class="text-[11px] text-rose-500 mt-1 font-medium">{{ $message }}</p> @enderror
                    </div>
                @else
                    @php $currentDoc = $appointment->doctor ?? $doctors->first(); @endphp
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Doctor</label>
                        <input type="hidden" name="doctor_id" value="{{ old('doctor_id', $currentDoc->id ?? '') }}">
                        <div class="px-4 py-3 bg-white/80 border border-slate-200/80 rounded-2xl text-xs text-slate-800 font-semibold flex items-center justify-between shadow-2xs">
                            <div class="flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                <span>{{ $currentDoc->name ?? 'Dr. Specialist' }}</span>
                            </div>
                            <span class="text-[10px] text-slate-400 font-normal uppercase tracking-wider">Assigned Doctor</span>
                        </div>
                    </div>
                @endif

                <!-- Appointment Type -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Appointment Type *</label>
                    <select name="appointment_type" required class="apple-input">
                        @foreach($appointmentTypes as $type)
                            <option value="{{ $type }}" {{ old('appointment_type', $appointment->appointment_type) === $type ? 'selected' : '' }}>{{ $type }}</option>
                        @endforeach
                    </select>
                    @error('appointment_type') <p class="text-[11px] text-rose-500 mt-1 font-medium">{{ $message }}</p> @enderror
                </div>

                <!-- Date -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Appointment Date *</label>
                    <input type="date" name="appointment_date" required 
                           value="{{ old('appointment_date', $appointment->appointment_date->format('Y-m-d')) }}"
                           class="apple-input">
                    @error('appointment_date') <p class="text-[11px] text-rose-500 mt-1 font-medium">{{ $message }}</p> @enderror
                </div>

                <!-- Time Slot -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Time Slot *</label>
                    <select name="appointment_time" required class="apple-input">
                        @foreach($timeSlots as $slot)
                            <option value="{{ $slot }}" {{ old('appointment_time', $appointment->appointment_time) === $slot ? 'selected' : '' }}>
                                {{ $slot }}
                            </option>
                        @endforeach
                    </select>
                    @error('appointment_time') <p class="text-[11px] text-rose-500 mt-1 font-medium">{{ $message }}</p> @enderror
                </div>

                <!-- Status -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Lifecycle Status *</label>
                    <select name="status" required class="apple-input">
                        <option value="pending" {{ old('status', $appointment->status) === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="confirmed" {{ old('status', $appointment->status) === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                        <option value="checked_in" {{ old('status', $appointment->status) === 'checked_in' ? 'selected' : '' }}>Checked In</option>
                        <option value="in_consultation" {{ old('status', $appointment->status) === 'in_consultation' ? 'selected' : '' }}>In Consultation</option>
                        <option value="completed" {{ old('status', $appointment->status) === 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="cancelled" {{ old('status', $appointment->status) === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        <option value="no_show" {{ old('status', $appointment->status) === 'no_show' ? 'selected' : '' }}>No Show</option>
                    </select>
                    @error('status') <p class="text-[11px] text-rose-500 mt-1 font-medium">{{ $message }}</p> @enderror
                </div>

                <!-- Reason for Visit -->
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Reason for Visit</label>
                    <input type="text" name="reason_for_visit" value="{{ old('reason_for_visit', $appointment->reason_for_visit) }}"
                           class="apple-input">
                    @error('reason_for_visit') <p class="text-[11px] text-rose-500 mt-1 font-medium">{{ $message }}</p> @enderror
                </div>

                <!-- Notes -->
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Clinical / Administrative Notes</label>
                    <textarea name="notes" rows="3"
                              class="apple-input !rounded-2xl">{{ old('notes', $appointment->notes) }}</textarea>
                    @error('notes') <p class="text-[11px] text-rose-500 mt-1 font-medium">{{ $message }}</p> @enderror
                </div>

                <!-- Cancellation Reason -->
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Cancellation Reason (if applicable)</label>
                    <input type="text" name="cancelled_reason" value="{{ old('cancelled_reason', $appointment->cancelled_reason) }}" placeholder="e.g. Patient travel conflict, weather..."
                           class="apple-input">
                    @error('cancelled_reason') <p class="text-[11px] text-rose-500 mt-1 font-medium">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('appointments.show', $appointment) }}" class="btn-pill-secondary">
                Cancel
            </a>
            <button type="submit" class="btn-pill-primary">
                Update Appointment Details
            </button>
        </div>
    </form>

</div>
@endsection

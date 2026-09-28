@extends('layouts.app')

@section('title', 'New appointment')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-lg bg-brand-50 border border-brand-100/60 text-brand-700 text-xs font-semibold mb-2">
                <span class="w-1.5 h-1.5 rounded-full bg-brand-500"></span>
                Visit schedule
            </div>
            <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-slate-900">Schedule an appointment</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">Choose a patient, doctor, and available time.</p>
        </div>
        <a href="{{ route('appointments.index') }}" class="btn-pill-secondary">
            &larr; Back to Schedule
        </a>
    </div>

    <!-- If patient was pre-selected via query param -->
    @if($selectedPatient)
        <div class="glass-card rounded-2xl p-5 border border-brand-200/50 bg-brand-50/40 flex items-center justify-between backdrop-blur-md">
            <div class="flex items-center gap-3.5">
                <div class="w-11 h-11 rounded-full bg-brand-500 text-white flex items-center justify-center font-bold text-sm shadow-xs">
                    {{ strtoupper(substr($selectedPatient->name, 0, 2)) }}
                </div>
                <div>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-brand-700 block">Booking for Patient</span>
                    <p class="text-sm font-bold text-slate-900">{{ $selectedPatient->name }} <span class="font-mono text-xs font-normal text-slate-500">({{ $selectedPatient->patient_id }})</span></p>
                    <p class="text-xs text-slate-500 mt-0.5">Phone: {{ $selectedPatient->phone }} • Blood: {{ $selectedPatient->blood_group ?? 'N/A' }}</p>
                </div>
            </div>
            <a href="{{ route('patients.show', $selectedPatient) }}" class="text-xs font-bold text-brand-700 hover:text-brand-800 transition">
                View Medical Profile &rarr;
            </a>
        </div>
    @endif

    <form method="POST" action="{{ route('appointments.store') }}" class="space-y-6" novalidate>
        @csrf

        <div class="glass-card-elevated rounded-2xl p-6 sm:p-8 border border-white/80 shadow-[0_12px_40px_rgb(0,0,0,0.04)] space-y-6">
            <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider border-b border-slate-100 pb-3.5 flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-brand-500"></span>
                <span>Visit details</span>
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <!-- 1. Patient Selection -->
                <div class="sm:col-span-2">
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Patient *</label>
                        <a href="{{ route('patients.create') }}" class="text-xs font-bold text-brand-600 hover:text-brand-700">
                            + Register New Patient
                        </a>
                    </div>
                    <select name="patient_id" required class="apple-input">
                        <option value="">-- Select Patient --</option>
                        @foreach($patients as $patient)
                            <option value="{{ $patient->id }}" {{ old('patient_id', $selectedPatient->id ?? '') == $patient->id ? 'selected' : '' }}>
                                {{ $patient->name }} ({{ $patient->patient_id }}) — 📞 {{ $patient->phone }}
                            </option>
                        @endforeach
                    </select>
                    @error('patient_id') <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- 2. Doctor Selection -->
                @if($doctors->count() > 1)
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Doctor *</label>
                        <select name="doctor_id" required class="apple-input">
                            <option value="">-- Select Doctor --</option>
                            @foreach($doctors as $doc)
                                <option value="{{ $doc->id }}" {{ old('doctor_id') == $doc->id ? 'selected' : '' }}>
                                    {{ $doc->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('doctor_id') <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                @elseif($doctors->isNotEmpty())
                    @php $singleDoc = $doctors->first(); @endphp
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Doctor</label>
                        <input type="hidden" name="doctor_id" value="{{ old('doctor_id', $singleDoc->id) }}">
                        <div class="px-4 py-3 bg-white/80 border border-slate-200/80 rounded-2xl text-xs text-slate-800 font-semibold flex items-center justify-between shadow-2xs">
                            <div class="flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                <span>{{ $singleDoc->name }}</span>
                            </div>
                            <span class="text-[10px] text-slate-400 font-normal uppercase tracking-wider">Assigned Doctor</span>
                        </div>
                    </div>
                @else
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Doctor *</label>
                        <p class="text-xs text-amber-600">No active doctors available.</p>
                    </div>
                @endif

                <!-- 3. Appointment Type -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Appointment Type *</label>
                    <select name="appointment_type" required class="apple-input">
                        @foreach($appointmentTypes as $type)
                            <option value="{{ $type }}" {{ old('appointment_type') === $type ? 'selected' : '' }}>{{ $type }}</option>
                        @endforeach
                    </select>
                    @error('appointment_type') <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- 4. Appointment Date -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Appointment Date *</label>
                    <input type="date" name="appointment_date" required 
                           value="{{ old('appointment_date', $selectedDate) }}" min="{{ now()->toDateString() }}"
                           class="apple-input">
                    @error('appointment_date') <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- 5. Appointment Time Slot -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Scheduled Time Slot *</label>
                    <select name="appointment_time" required class="apple-input">
                        <option value="">-- Choose Slot --</option>
                        @foreach($timeSlots as $slot)
                            <option value="{{ $slot }}" {{ old('appointment_time') === $slot ? 'selected' : '' }}>
                                {{ $slot }}
                            </option>
                        @endforeach
                    </select>
                    @error('appointment_time') <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- 6. Reason for Visit -->
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Reason for Visit</label>
                    <input type="text" name="reason_for_visit" value="{{ old('reason_for_visit') }}" placeholder="Primary chief complaint (e.g. Fever, persistent cough, routine follow-up)..."
                           class="apple-input">
                </div>

                <!-- 9. Preliminary Clinical Notes -->
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Preliminary Notes / Instructions</label>
                    <textarea name="notes" rows="3" placeholder="Any special requirements, fasting instructions, or front-desk memos..."
                              class="apple-input !rounded-2xl">{{ old('notes') }}</textarea>
                </div>
            </div>
        </div>

        <!-- Submit Controls -->
        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('appointments.index') }}" class="btn-pill-secondary">
                Cancel
            </a>
            <button type="submit" class="btn-pill-primary">
                Confirm & Schedule Appointment
            </button>
        </div>
    </form>

</div>
@endsection

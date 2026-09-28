@extends('layouts.app')

@section('title', 'Clinical Consultation Encounter')

@section('content')
<div class="space-y-8">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-2">
                @if($appointment)
                    <span class="font-mono text-xs font-bold text-brand-700 bg-brand-50 px-3 py-1 rounded-lg border border-brand-200/80">
                        Appt: {{ $appointment->appointment_id }}
                    </span>
                @endif
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-lg bg-brand-50/80 border border-brand-100 text-brand-700 text-xs font-semibold">
                    <span>Clinical Encounter</span>
                </div>
            </div>
            <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-slate-900">Clinical Consultation</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">Record patient symptoms, vital signs, physical exam findings, and assessment.</p>
        </div>
        <a href="{{ $appointment ? route('appointments.show', $appointment) : route('patients.index') }}" 
           class="btn-pill-secondary self-start sm:self-auto inline-flex items-center gap-2">
            &larr; <span>Exit Encounter</span>
        </a>
    </div>

    <form method="POST" action="{{ route('consultations.store') }}" class="space-y-8" novalidate>
        @csrf
        @if($appointment)
            <input type="hidden" name="appointment_id" value="{{ $appointment->id }}">
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 sm:gap-8">

            <!-- Left Column: Patient Context & Clinical Medical History -->
            <div class="space-y-6">
                @if($patient)
                    <input type="hidden" name="patient_id" value="{{ $patient->id }}">
                    <div class="glass-card-elevated rounded-2xl p-6 space-y-5">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                            <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Patient Summary</h3>
                            <a href="{{ route('patients.show', $patient) }}" target="_blank" class="text-xs font-bold text-brand-600 hover:text-brand-700 inline-flex items-center gap-1">
                                <span>Profile</span> ↗
                            </a>
                        </div>

                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-2xl bg-brand-50 text-brand-700 border border-brand-200 flex items-center justify-center font-extrabold text-base shadow-xs">
                                {{ strtoupper(substr($patient->name, 0, 2)) }}
                            </div>
                            <div>
                                <h4 class="font-bold text-slate-900 text-sm">{{ $patient->name }}</h4>
                                <p class="text-xs text-slate-400 font-mono">{{ $patient->patient_id }}</p>
                            </div>
                        </div>

                        <div class="space-y-2 text-xs divide-y divide-slate-100/80 pt-1">
                            <div class="flex justify-between py-1.5">
                                <span class="text-slate-400">Gender & Age:</span>
                                <span class="font-bold text-slate-800">{{ $patient->gender }}, {{ $patient->age ?? 'N/A' }} yrs</span>
                            </div>
                            <div class="flex justify-between py-1.5">
                                <span class="text-slate-400">Blood Group:</span>
                                <span class="font-extrabold text-rose-600">{{ $patient->blood_group ?? '—' }}</span>
                            </div>
                            <div class="flex justify-between py-1.5">
                                <span class="text-slate-400">Phone:</span>
                                <span class="font-mono font-semibold text-slate-800">{{ $patient->phone }}</span>
                            </div>
                        </div>

                        <!-- High-Risk Allergies Warning -->
                        @if(!empty($patient->allergies))
                            <div class="p-4 bg-rose-50/90 border border-rose-200 rounded-2xl text-xs">
                                <span class="text-rose-800 font-bold uppercase text-[10px] tracking-wider block">⚠️ High-Risk Allergies</span>
                                <p class="text-rose-700 font-medium mt-1 leading-relaxed">{{ $patient->allergies }}</p>
                            </div>
                        @endif

                        <!-- Chronic Conditions -->
                        @if(!empty($patient->chronic_conditions))
                            <div class="p-4 bg-amber-50/80 border border-amber-200 rounded-2xl text-xs">
                                <span class="text-amber-800 font-bold uppercase text-[10px] tracking-wider block">Chronic Conditions</span>
                                <p class="text-amber-900 font-medium mt-1 leading-relaxed">{{ $patient->chronic_conditions }}</p>
                            </div>
                        @endif

                        <!-- Current Medications -->
                        @if(!empty($patient->current_medications))
                            <div class="p-4 bg-slate-50/80 border border-slate-200 rounded-2xl text-xs">
                                <span class="text-slate-500 font-bold uppercase text-[10px] tracking-wider block">Current Regimen</span>
                                <p class="text-slate-800 font-medium mt-1 leading-relaxed">{{ $patient->current_medications }}</p>
                            </div>
                        @endif
                    </div>

                    <!-- Previous Consultations Accordion/Snapshot -->
                    @if($previousConsultations->isNotEmpty())
                        <div class="glass-card rounded-2xl p-6 space-y-3">
                            <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider border-b border-slate-100 pb-3">
                                Past Visits ({{ $previousConsultations->count() }})
                            </h3>
                            <div class="space-y-3 max-h-64 overflow-y-auto">
                                @foreach($previousConsultations as $pastCon)
                                    <div class="p-3.5 bg-slate-50/70 rounded-2xl border border-slate-100 text-xs space-y-1">
                                        <div class="flex justify-between text-[11px] font-semibold text-slate-500">
                                            <span>{{ $pastCon->consultation_date->format('d M Y') }}</span>
                                            <span>{{ $pastCon->doctor->name ?? 'Doctor' }}</span>
                                        </div>
                                        <p class="font-bold text-slate-900">Dx: {{ $pastCon->diagnosis }}</p>
                                        <p class="text-slate-500 text-[11px] truncate">{{ $pastCon->treatment }}</p>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @else
                    <div class="glass-card-elevated rounded-2xl p-6 space-y-3">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Select Patient *</label>
                        <select name="patient_id" required class="apple-input w-full">
                            <option value="">-- Choose Patient --</option>
                            @foreach($patients as $p)
                                <option value="{{ $p->id }}" {{ old('patient_id') == $p->id ? 'selected' : '' }}>
                                    {{ $p->name }} ({{ $p->patient_id }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif
            </div>

            <!-- Right Column: Clinical Encounter Intake Form -->
            <div class="lg:col-span-2 space-y-6 sm:space-y-8">

                <!-- 1. Encounter Metadata & Vitals -->
                <div class="glass-card-elevated rounded-2xl p-6 sm:p-8 space-y-6">
                    <div class="flex items-center gap-3 border-b border-slate-100 pb-4">
                        <div class="w-9 h-9 rounded-xl bg-brand-50 text-brand-600 flex items-center justify-center font-bold">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider">1. Encounter Metadata & Patient Vitals</h2>
                            <p class="text-xs text-slate-400">Doctor assignment and baseline vital metrics</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-5">
                        <!-- Attending Doctor (Single Doctor Rule) -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Consulting Doctor *</label>
                            @if(isset($doctors) && $doctors->count() === 1)
                                @php $singleDoc = $doctors->first(); @endphp
                                <input type="hidden" name="doctor_id" value="{{ $singleDoc->id }}">
                                <div class="px-4 py-3 bg-slate-50/90 border border-slate-200/80 rounded-2xl text-xs text-slate-800 flex items-center justify-between">
                                    <span class="font-bold">Dr. {{ $singleDoc->name }}</span>
                                    <span class="text-[10px] font-semibold text-brand-600 bg-brand-50 px-2 py-0.5 rounded-md">Primary</span>
                                </div>
                            @else
                                <select name="doctor_id" required class="apple-input w-full">
                                    @foreach($doctors as $doc)
                                        <option value="{{ $doc->id }}" {{ old('doctor_id', $appointment->doctor_id ?? auth()->id()) == $doc->id ? 'selected' : '' }}>
                                            {{ $doc->name }} ({{ ucfirst(str_replace('_', ' ', $doc->role)) }})
                                        </option>
                                    @endforeach
                                </select>
                            @endif
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Encounter Date & Time *</label>
                            <input type="datetime-local" name="consultation_date" required 
                                   value="{{ old('consultation_date', now()->format('Y-m-d\TH:i')) }}"
                                   class="apple-input w-full">
                        </div>
                    </div>

                    <!-- Vitals Entry Grid -->
                    <div class="pt-2">
                        <span class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-3">Patient Vital Signs</span>
                        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 text-xs">
                            <div>
                                <label class="block text-[10px] text-slate-400 uppercase font-semibold mb-1">BP (mmHg)</label>
                                <input type="text" name="vital_bp" value="{{ old('vital_bp') }}" placeholder="120/80"
                                       class="apple-input w-full text-xs py-2 text-center font-bold">
                            </div>
                            <div>
                                <label class="block text-[10px] text-slate-400 uppercase font-semibold mb-1">Pulse (bpm)</label>
                                <input type="text" name="vital_pulse" value="{{ old('vital_pulse') }}" placeholder="72"
                                       class="apple-input w-full text-xs py-2 text-center font-bold">
                            </div>
                            <div>
                                <label class="block text-[10px] text-slate-400 uppercase font-semibold mb-1">Temp (°F)</label>
                                <input type="text" name="vital_temperature" value="{{ old('vital_temperature') }}" placeholder="98.6"
                                       class="apple-input w-full text-xs py-2 text-center font-bold">
                            </div>
                            <div>
                                <label class="block text-[10px] text-slate-400 uppercase font-semibold mb-1">SpO2 (%)</label>
                                <input type="text" name="vital_spo2" value="{{ old('vital_spo2') }}" placeholder="99"
                                       class="apple-input w-full text-xs py-2 text-center font-bold">
                            </div>
                            <div>
                                <label class="block text-[10px] text-slate-400 uppercase font-semibold mb-1">Weight (kg)</label>
                                <input type="text" name="vital_weight" value="{{ old('vital_weight') }}" placeholder="68"
                                       class="apple-input w-full text-xs py-2 text-center font-bold">
                            </div>
                            <div>
                                <label class="block text-[10px] text-slate-400 uppercase font-semibold mb-1">Height (cm)</label>
                                <input type="text" name="vital_height" value="{{ old('vital_height') }}" placeholder="172"
                                       class="apple-input w-full text-xs py-2 text-center font-bold">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2. Symptoms & Diagnosis -->
                <div class="glass-card-elevated rounded-2xl p-6 sm:p-8 space-y-6">
                    <div class="flex items-center gap-3 border-b border-slate-100 pb-4">
                        <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider">2. Symptoms & Clinical Diagnosis</h2>
                            <p class="text-xs text-slate-400">Chief complaints and diagnostic impression</p>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                                Presenting Symptoms & Chief Complaints *
                            </label>
                            <textarea name="symptoms" rows="3" required placeholder="Describe complaints, onset, duration, and aggravating factors..."
                                      class="apple-input w-full leading-relaxed">{{ old('symptoms', $appointment->reason_for_visit ?? '') }}</textarea>
                            @error('symptoms') <p class="text-[11px] text-rose-500 mt-1 font-semibold">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                                Clinical Diagnosis / Assessment *
                            </label>
                            <input type="text" name="diagnosis" value="{{ old('diagnosis') }}" required placeholder="e.g. Acute Pharyngitis, Type 2 Diabetes, Essential Hypertension..."
                                   class="apple-input w-full">
                            @error('diagnosis') <p class="text-[11px] text-rose-500 mt-1 font-semibold">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <!-- 3. Physical Examination Notes & Treatment -->
                <div class="glass-card-elevated rounded-2xl p-6 sm:p-8 space-y-6">
                    <div class="flex items-center gap-3 border-b border-slate-100 pb-4">
                        <div class="w-9 h-9 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center font-bold">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider">3. Clinical Notes & Treatment Plan</h2>
                            <p class="text-xs text-slate-400">Exam observations, plan of care, and follow-up guidance</p>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                                Physical Examination & Systemic Notes
                            </label>
                            <textarea name="medical_notes" rows="3" placeholder="Chest clear, abdomen soft non-tender, throat congestion noted..."
                                      class="apple-input w-full leading-relaxed">{{ old('medical_notes') }}</textarea>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                                Treatment Plan & Clinical Recommendations
                            </label>
                            <textarea name="treatment" rows="3" placeholder="Rest, hydration, steam inhalation, order routine blood panel, review in 5 days..."
                                      class="apple-input w-full leading-relaxed">{{ old('treatment') }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Submit Action Buttons -->
                <div class="flex flex-col sm:flex-row items-center justify-end gap-3 pt-2">
                    <button type="submit" name="action" value="save_only"
                            class="btn-pill-secondary w-full sm:w-auto">
                        Save Consultation Only
                    </button>
                    <button type="submit" name="action" value="prescribe"
                            class="btn-pill-primary w-full sm:w-auto inline-flex items-center justify-center gap-2">
                        <span>Save & Author Digital Prescription</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                        </svg>
                    </button>
                </div>

            </div>

        </div>
    </form>

</div>
@endsection

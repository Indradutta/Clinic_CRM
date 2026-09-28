@extends('layouts.app')

@section('title', 'Edit Consultation Encounter')

@section('content')
<div class="space-y-8">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-2">
                @if($consultation->appointment)
                    <span class="font-mono text-xs font-bold text-brand-700 bg-brand-50 px-3 py-1 rounded-lg border border-brand-200/80">
                        Appt: {{ $consultation->appointment->appointment_id }}
                    </span>
                @endif
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-lg bg-brand-50/80 border border-brand-100 text-brand-700 text-xs font-semibold">
                    <span>Clinical Update</span>
                </div>
            </div>
            <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-slate-900">Edit Clinical Consultation</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">Modify symptoms, updated vitals, or clinical treatment plans.</p>
        </div>
        <a href="{{ route('consultations.show', $consultation) }}" 
           class="btn-pill-secondary self-start sm:self-auto inline-flex items-center gap-2">
            &larr; <span>Cancel & Return</span>
        </a>
    </div>

    <form method="POST" action="{{ route('consultations.update', $consultation) }}" class="space-y-8" novalidate>
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 sm:gap-8">

            <!-- Left Column: Patient Summary -->
            <div class="space-y-6">
                @php $patient = $consultation->patient; @endphp
                <div class="glass-card-elevated rounded-2xl p-6 space-y-5">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Patient Details</h3>
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
                </div>
            </div>

            <!-- Right Column: Encounter Form -->
            <div class="lg:col-span-2 space-y-6 sm:space-y-8">

                <!-- 1. Metadata & Vitals -->
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
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Attending Doctor *</label>
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
                                        <option value="{{ $doc->id }}" {{ old('doctor_id', $consultation->doctor_id) == $doc->id ? 'selected' : '' }}>
                                            {{ $doc->name }} ({{ ucfirst(str_replace('_', ' ', $doc->role)) }})
                                        </option>
                                    @endforeach
                                </select>
                            @endif
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Encounter Date & Time *</label>
                            <input type="datetime-local" name="consultation_date" required 
                                   value="{{ old('consultation_date', $consultation->consultation_date ? $consultation->consultation_date->format('Y-m-d\TH:i') : now()->format('Y-m-d\TH:i')) }}"
                                   class="apple-input w-full">
                        </div>
                    </div>

                    <!-- Vitals Entry Grid -->
                    @php $v = $consultation->vitals ?? []; @endphp
                    <div class="pt-2">
                        <span class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-3">Patient Vital Signs</span>
                        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 text-xs">
                            <div>
                                <label class="block text-[10px] text-slate-400 uppercase font-semibold mb-1">BP (mmHg)</label>
                                <input type="text" name="vital_bp" value="{{ old('vital_bp', $v['blood_pressure'] ?? ($v['bp'] ?? '')) }}" placeholder="120/80"
                                       class="apple-input w-full text-xs py-2 text-center font-bold">
                            </div>
                            <div>
                                <label class="block text-[10px] text-slate-400 uppercase font-semibold mb-1">Pulse (bpm)</label>
                                <input type="text" name="vital_pulse" value="{{ old('vital_pulse', $v['heart_rate'] ?? ($v['pulse'] ?? '')) }}" placeholder="72"
                                       class="apple-input w-full text-xs py-2 text-center font-bold">
                            </div>
                            <div>
                                <label class="block text-[10px] text-slate-400 uppercase font-semibold mb-1">Temp (°F)</label>
                                <input type="text" name="vital_temperature" value="{{ old('vital_temperature', $v['temperature'] ?? '') }}" placeholder="98.6"
                                       class="apple-input w-full text-xs py-2 text-center font-bold">
                            </div>
                            <div>
                                <label class="block text-[10px] text-slate-400 uppercase font-semibold mb-1">SpO2 (%)</label>
                                <input type="text" name="vital_spo2" value="{{ old('vital_spo2', $v['oxygen_saturation'] ?? ($v['spo2'] ?? '')) }}" placeholder="99"
                                       class="apple-input w-full text-xs py-2 text-center font-bold">
                            </div>
                            <div>
                                <label class="block text-[10px] text-slate-400 uppercase font-semibold mb-1">Weight (kg)</label>
                                <input type="text" name="vital_weight" value="{{ old('vital_weight', $v['weight'] ?? ($v['weight_kg'] ?? '')) }}" placeholder="68"
                                       class="apple-input w-full text-xs py-2 text-center font-bold">
                            </div>
                            <div>
                                <label class="block text-[10px] text-slate-400 uppercase font-semibold mb-1">Height (cm)</label>
                                <input type="text" name="vital_height" value="{{ old('vital_height', $v['height'] ?? ($v['height_cm'] ?? '')) }}" placeholder="172"
                                       class="apple-input w-full text-xs py-2 text-center font-bold">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2. Symptoms, Diagnosis & Clinical Evaluation -->
                <div class="glass-card-elevated rounded-2xl p-6 sm:p-8 space-y-5">
                    <div class="flex items-center gap-3 border-b border-slate-100 pb-4">
                        <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider">2. Clinical Symptoms & Diagnosis</h2>
                            <p class="text-xs text-slate-400">Chief complaints and diagnostic impression</p>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Presenting Symptoms / Chief Complaint *</label>
                            <textarea name="symptoms" rows="3" required
                                      class="apple-input w-full leading-relaxed">{{ old('symptoms', $consultation->symptoms) }}</textarea>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Clinical Diagnosis / Impression *</label>
                            <input type="text" name="diagnosis" required value="{{ old('diagnosis', $consultation->diagnosis) }}"
                                   class="apple-input w-full font-bold">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Physical Examination & Doctor Clinical Notes</label>
                            <textarea name="medical_notes" rows="4"
                                      class="apple-input w-full leading-relaxed">{{ old('medical_notes', $consultation->medical_notes) }}</textarea>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Treatment Plan & Non-Pharmacological Advice</label>
                            <textarea name="treatment" rows="3"
                                      class="apple-input w-full leading-relaxed">{{ old('treatment', $consultation->treatment) }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Form Action Buttons -->
                <div class="flex items-center justify-end gap-3 pt-2">
                    <a href="{{ route('consultations.show', $consultation) }}" 
                       class="btn-pill-secondary">
                        Cancel
                    </a>
                    <button type="submit" 
                            class="btn-pill-primary px-8">
                        Save Consultation Changes
                    </button>
                </div>

            </div>
        </div>
    </form>
</div>
@endsection


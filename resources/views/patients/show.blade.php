@extends('layouts.app')

@section('title', $patient->name . ' - Patient Profile')

@section('content')
<div class="space-y-6">
    
    <!-- Top Patient Medical Header Card -->
    <div class="glass-card-elevated rounded-2xl p-6 sm:p-7 border border-white/80 shadow-[0_12px_40px_rgb(0,0,0,0.04)] ">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            
            <!-- Left Info Block -->
            <div class="flex items-start sm:items-center gap-4">
                <div class="w-16 h-16 rounded-full bg-brand-500 text-white flex items-center justify-center font-bold text-xl shadow-xs shrink-0">
                    {{ strtoupper(substr($patient->name, 0, 2)) }}
                </div>
                <div>
                    <div class="flex items-center gap-2.5 flex-wrap">
                        <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-slate-900">{{ $patient->name }}</h1>
                        <span class="font-mono text-xs font-bold text-brand-700 bg-brand-50 px-3 py-1 rounded-lg border border-brand-100/60">
                            {{ $patient->patient_id }}
                        </span>
                        @if($patient->blood_group)
                            <span class="px-3 py-1 rounded-lg text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                Blood: {{ $patient->blood_group }}
                            </span>
                        @endif
                    </div>
                    <p class="text-xs text-slate-500 mt-1.5 flex items-center gap-2.5 flex-wrap">
                        <span class="font-medium text-slate-700">{{ $patient->gender ?? '—' }}</span>
                        <span class="text-slate-300">•</span>
                        <span class="font-medium text-slate-700">{{ $patient->age ? $patient->age . ' years old' : 'Age not recorded' }}</span>
                        <span class="text-slate-300">•</span>
                        <span>📞 {{ $patient->phone }}</span>
                        @if($patient->email)
                            <span class="text-slate-300">•</span>
                            <span>✉️ {{ $patient->email }}</span>
                        @endif
                    </p>
                </div>
            </div>

            <!-- Right Actions -->
            <div class="flex items-center gap-2.5 shrink-0 flex-wrap">
                @can('patients.edit')
                <a href="{{ route('patients.edit', $patient) }}" 
                   class="btn-pill-secondary">
                    Edit Details
                </a>
                @endcan
                @can('appointments.create')
                <a href="{{ route('appointments.create') }}?patient_id={{ $patient->id }}" 
                   class="btn-pill-primary">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path>
                    </svg>
                    <span>Book Appointment</span>
                </a>
                @endcan
            </div>
        </div>

        <!-- Clinical High-Priority Alert Strip (Allergies) -->
        @if(!empty($patient->allergies))
            <div class="mt-5 p-4 rounded-2xl bg-amber-50/80 border border-amber-200/80 text-amber-900 text-xs flex items-center gap-3 shadow-2xs">
                <span class="text-base">⚠️</span>
                <div>
                    <span class="font-bold uppercase tracking-wider text-[11px] text-amber-800">Critical Clinical Alert — Known Allergies:</span>
                    <span class="font-semibold ml-1">{{ $patient->allergies }}</span>
                </div>
            </div>
        @endif
    </div>

    <!-- The 9-Section Navigation Bar (Sec 5, pp. 5-6) -->
    <div class="overflow-x-auto scrollbar-none py-1">
        <nav class="inline-flex rounded-xl bg-slate-200/60 p-1.5 gap-1 min-w-max backdrop-blur-md border border-white/70 shadow-2xs">
            @php
                $tabs = [
                    'personal' => ['1. Personal Info', 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'],
                    'medical' => ['2. Medical History', 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
                    'appointments' => ['3. Appointment History (' . $patient->appointments->count() . ')', 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
                    'consultations' => ['4. Consultations', 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
                    'prescriptions' => ['5. Prescriptions', 'M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z'],
                    'invoices' => ['6. Invoices', 'M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z'],
                    'payments' => ['7. Payments', 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z'],
                    'notes' => ['8. Notes (' . $patient->notes->count() . ')', 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z'],
                    'documents' => ['9. Documents (' . $patient->documents->count() . ')', 'M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z'],
                ];
            @endphp

            @foreach($tabs as $tabKey => [$tabLabel, $iconPath])
                <a href="{{ route('patients.show', ['patient' => $patient, 'tab' => $tabKey]) }}"
                   class="flex items-center px-4 py-2 text-xs font-semibold rounded-lg transition {{ $activeTab === $tabKey ? 'bg-brand-500 text-white shadow-xs font-bold' : 'text-slate-600 hover:text-slate-900' }}">
                    <svg class="w-3.5 h-3.5 mr-1.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="{{ $iconPath }}"></path>
                    </svg>
                    <span>{{ $tabLabel }}</span>
                </a>
            @endforeach
        </nav>
    </div>

    <!-- 9-Section Content Panels -->
    <div>
        
        <!-- Tab 1: Personal Information -->
        @if($activeTab === 'personal')
            <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-xs space-y-6">
                <div class="border-b border-slate-100 pb-3 flex items-center justify-between">
                    <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider">Demographic Profile</h3>
                    @can('patients.edit')
                    <a href="{{ route('patients.edit', $patient) }}" class="text-xs font-semibold text-blue-600 hover:text-blue-700">Edit Info</a>
                    @endcan
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 text-xs">
                    <div>
                        <span class="text-slate-400 font-medium block uppercase text-[10px]">Full Name</span>
                        <span class="text-slate-900 font-semibold text-sm mt-0.5 block">{{ $patient->name }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 font-medium block uppercase text-[10px]">Patient ID</span>
                        <span class="font-mono text-blue-600 font-bold text-sm mt-0.5 block">{{ $patient->patient_id }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 font-medium block uppercase text-[10px]">Date of Birth</span>
                        <span class="text-slate-900 font-semibold mt-0.5 block">{{ $patient->dob ? $patient->dob->format('d M Y') : 'Not recorded' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 font-medium block uppercase text-[10px]">Age</span>
                        <span class="text-slate-900 font-semibold mt-0.5 block">{{ $patient->age ? $patient->age . ' years' : '—' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 font-medium block uppercase text-[10px]">Gender</span>
                        <span class="text-slate-900 font-semibold mt-0.5 block">{{ $patient->gender ?? '—' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 font-medium block uppercase text-[10px]">Blood Group</span>
                        <span class="text-slate-900 font-semibold mt-0.5 block">{{ $patient->blood_group ?? '—' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 font-medium block uppercase text-[10px]">Marital Status</span>
                        <span class="text-slate-900 font-semibold mt-0.5 block">{{ $patient->marital_status ?? '—' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 font-medium block uppercase text-[10px]">Preferred Language</span>
                        <span class="text-slate-900 font-semibold mt-0.5 block">{{ $patient->preferred_language ?? '—' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 font-medium block uppercase text-[10px]">Height / Weight</span>
                        <span class="text-slate-900 font-semibold mt-0.5 block">{{ $patient->height_cm ? $patient->height_cm.' cm' : '—' }} / {{ $patient->weight_kg ? $patient->weight_kg.' kg' : '—' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 font-medium block uppercase text-[10px]">Primary Phone</span>
                        <span class="text-slate-900 font-semibold mt-0.5 block">{{ $patient->phone }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 font-medium block uppercase text-[10px]">Email Address</span>
                        <span class="text-slate-900 font-semibold mt-0.5 block">{{ $patient->email ?? '—' }}</span>
                    </div>
                    <div class="sm:col-span-2">
                        <span class="text-slate-400 font-medium block uppercase text-[10px]">Residential Address</span>
                        <span class="text-slate-900 font-semibold mt-0.5 block">{{ $patient->address ?? '—' }}</span>
                    </div>
                    <div class="sm:col-span-2">
                        <span class="text-slate-400 font-medium block uppercase text-[10px]">Main Health Concern</span>
                        <span class="text-slate-900 font-semibold mt-0.5 block">{{ $patient->primary_concern ?? '—' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 font-medium block uppercase text-[10px]">Emergency Contact</span>
                        <span class="text-slate-900 font-semibold mt-0.5 block">
                            {{ $patient->emergency_contact_name ?? '—' }}
                            @if($patient->emergency_contact_phone)
                                ({{ $patient->emergency_contact_phone }})
                            @endif
                        </span>
                    </div>
                </div>
            </div>
        @endif

        <!-- Tab 2: Medical History -->
        @if($activeTab === 'medical')
            <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-xs space-y-6">
                <div class="border-b border-slate-100 pb-3 flex items-center justify-between">
                    <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider">Clinical Medical Record</h3>
                    @can('patients.edit')
                    <a href="{{ route('patients.edit', $patient) }}" class="text-xs font-semibold text-blue-600 hover:text-blue-700">Update Medical Info</a>
                    @endcan
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-xs">
                    <!-- Allergies -->
                    <div class="p-4 rounded-xl bg-rose-50/50 border border-rose-100">
                        <span class="text-rose-800 font-bold uppercase text-[11px] block">⚠️ Known Drug / Food Allergies</span>
                        <p class="text-slate-800 font-medium mt-1.5 leading-relaxed">{{ $patient->allergies ?: 'No known drug allergies (NKDA).' }}</p>
                    </div>

                    <!-- Chronic Conditions -->
                    <div class="p-4 rounded-xl bg-slate-50 border border-slate-100">
                        <span class="text-slate-500 font-bold uppercase text-[11px] block">Chronic Conditions</span>
                        <p class="text-slate-800 font-medium mt-1.5 leading-relaxed">{{ $patient->chronic_conditions ?: 'None reported.' }}</p>
                    </div>

                    <!-- Current Medications -->
                    <div class="p-4 rounded-xl bg-slate-50 border border-slate-100">
                        <span class="text-slate-500 font-bold uppercase text-[11px] block">Current Medications</span>
                        <p class="text-slate-800 font-medium mt-1.5 leading-relaxed">{{ $patient->current_medications ?: 'None reported.' }}</p>
                    </div>

                    <!-- Past Surgeries -->
                    <div class="p-4 rounded-xl bg-slate-50 border border-slate-100">
                        <span class="text-slate-500 font-bold uppercase text-[11px] block">Past Surgeries / Procedures</span>
                        <p class="text-slate-800 font-medium mt-1.5 leading-relaxed">{{ $patient->past_surgeries ?: 'None reported.' }}</p>
                    </div>

                    <!-- Family History -->
                    <div class="p-4 rounded-xl bg-slate-50 border border-slate-100">
                        <span class="text-slate-500 font-bold uppercase text-[11px] block">Family Medical History</span>
                        <p class="text-slate-800 font-medium mt-1.5 leading-relaxed">{{ $patient->family_history ?: 'Non-contributory / None reported.' }}</p>
                    </div>

                    <!-- Lifestyle & Habits -->
                    <div class="p-4 rounded-xl bg-slate-50 border border-slate-100 space-y-2">
                        <span class="text-slate-500 font-bold uppercase text-[11px] block">Lifestyle & Habits</span>
                        <p class="text-slate-800 font-medium"><span class="text-slate-400">Smoking:</span> {{ $patient->smoking_habits ?: 'Not specified' }}</p>
                        <p class="text-slate-800 font-medium"><span class="text-slate-400">Alcohol:</span> {{ $patient->alcohol_consumption ?: 'Not specified' }}</p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Tab 3: Appointment History -->
        @if($activeTab === 'appointments')
            <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-xs space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider">Scheduled & Past Appointments</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Chronological history of patient visits, consultations, and routine checks.</p>
                    </div>
                    @can('appointments.create')
                    <a href="{{ route('appointments.create') }}?patient_id={{ $patient->id }}" 
                       class="px-3.5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-semibold shadow-xs transition flex items-center space-x-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        <span>Schedule Appointment</span>
                    </a>
                    @endcan
                </div>

                @if($patient->appointments->isEmpty())
                    <div class="p-8 text-center text-slate-400 text-xs">
                        <p>No prior appointment bookings logged for this patient yet.</p>
                        @can('appointments.create')
                        <a href="{{ route('appointments.create') }}?patient_id={{ $patient->id }}" class="mt-2 inline-block font-semibold text-blue-600 hover:underline">
                            + Book First Appointment
                        </a>
                        @endcan
                    </div>
                @else
                    <div class="divide-y divide-slate-100">
                        @foreach($patient->appointments as $apt)
                            <div class="py-3.5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 hover:bg-slate-50/60 rounded-xl px-3 transition">
                                <div class="flex items-start space-x-3">
                                    <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 font-bold flex flex-col items-center justify-center shrink-0">
                                        <span class="text-[10px] uppercase font-bold">{{ $apt->appointment_date->format('M') }}</span>
                                        <span class="text-xs font-bold leading-none">{{ $apt->appointment_date->format('d') }}</span>
                                    </div>
                                    <div>
                                        <div class="flex items-center space-x-2">
                                            @can('appointments.view')
                                            <a href="{{ route('appointments.show', $apt) }}" class="font-bold text-xs text-slate-900 hover:text-blue-600">
                                                {{ $apt->appointment_id }}
                                            </a>
                                            @else
                                                <span class="font-bold text-xs text-slate-900">{{ $apt->appointment_id }}</span>
                                            @endcan
                                            <span class="px-2 py-0.5 rounded-md text-[10px] font-bold border {{ $apt->status_badge_class }}">
                                                {{ $apt->status_label }}
                                            </span>
                                            <span class="px-1.5 py-0.5 rounded text-[9px] font-bold uppercase border {{ $apt->payment_badge_class }}">
                                                {{ $apt->payment_status }}
                                            </span>
                                        </div>
                                        <p class="text-xs text-slate-500 mt-0.5">
                                            🕒 {{ $apt->appointment_time }} • Doctor: {{ $apt->doctor->name ?? 'Dr. Specialist' }} • Type: {{ $apt->appointment_type }}
                                        </p>
                                        @if($apt->reason_for_visit)
                                            <p class="text-[11px] text-slate-600 italic mt-0.5">"{{ $apt->reason_for_visit }}"</p>
                                        @endif
                                    </div>
                                </div>

                                <div class="flex items-center space-x-2 shrink-0">
                                    @can('appointments.view')
                                    <a href="{{ route('appointments.show', $apt) }}" 
                                       class="px-2.5 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition">
                                        View Details
                                    </a>
                                    @endcan
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @endif

        <!-- Tab 4: Consultation History -->
        @if($activeTab === 'consultations')
            <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-xs space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider">Clinical Consultations & Diagnoses</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Physical exams, vitals recordings, diagnoses, and doctor clinical notes.</p>
                    </div>
                    @can('consultations.create')
                    <a href="{{ route('consultations.create') }}?patient_id={{ $patient->id }}" 
                       class="px-3.5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-semibold shadow-xs transition flex items-center space-x-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        <span>New Consultation</span>
                    </a>
                    @endcan
                </div>

                @if($patient->consultations->isEmpty())
                    <div class="p-8 text-center text-slate-400 text-xs">
                        <svg class="w-10 h-10 mx-auto text-slate-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <p>No recorded medical consultations on file for this patient.</p>
                        @can('consultations.create')
                        <a href="{{ route('consultations.create') }}?patient_id={{ $patient->id }}" class="mt-2 inline-block font-semibold text-blue-600 hover:underline">
                            + Record First Consultation
                        </a>
                        @endcan
                    </div>
                @else
                    <div class="divide-y divide-slate-100">
                        @foreach($patient->consultations as $consultation)
                            <div class="py-4 hover:bg-slate-50/60 rounded-xl px-3 transition space-y-3">
                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 font-bold flex flex-col items-center justify-center shrink-0">
                                            <span class="text-[10px] uppercase font-bold">{{ $consultation->consultation_date->format('M') }}</span>
                                            <span class="text-xs font-bold leading-none">{{ $consultation->consultation_date->format('d') }}</span>
                                        </div>
                                        <div>
                                            <div class="flex items-center space-x-2">
                                                <h4 class="font-bold text-sm text-slate-900">{{ $consultation->diagnosis ?: 'Clinical Evaluation' }}</h4>
                                                @if($consultation->appointment)
                                                    <span class="font-mono text-[10px] bg-slate-100 text-slate-600 px-2 py-0.5 rounded">
                                                        {{ $consultation->appointment->appointment_id }}
                                                    </span>
                                                @endif
                                            </div>
                                            <p class="text-xs text-slate-500 mt-0.5">
                                                Doctor: <span class="font-medium text-slate-700">{{ $consultation->doctor->name ?? 'Dr. Specialist' }}</span> • {{ $consultation->consultation_date->format('F d, Y') }}
                                            </p>
                                        </div>
                                    </div>

                                    <div class="flex items-center space-x-2 shrink-0">
                                        @can('prescriptions.create')
                                        <a href="{{ route('prescriptions.create') }}?patient_id={{ $patient->id }}&consultation_id={{ $consultation->id }}"
                                           class="px-2.5 py-1.5 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-semibold text-xs transition flex items-center space-x-1">
                                            <span>💊 Prescribe</span>
                                        </a>
                                        @endcan
                                        @can('consultations.view')
                                        <a href="{{ route('consultations.show', $consultation) }}" 
                                           class="px-2.5 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition">
                                            View Notes
                                        </a>
                                        @endcan
                                        @can('consultations.edit')
                                        <a href="{{ route('consultations.edit', $consultation) }}" 
                                           class="px-2.5 py-1.5 rounded-lg bg-slate-50 hover:bg-slate-100 text-slate-600 font-semibold text-xs border border-slate-200 transition">
                                            Edit
                                        </a>
                                        @endcan
                                    </div>
                                </div>

                                <!-- Symptoms & Vitals pills -->
                                <div class="bg-slate-50 rounded-xl p-3 text-xs space-y-2">
                                    @if($consultation->symptoms)
                                        <div class="flex items-start space-x-2">
                                            <span class="text-slate-400 font-medium shrink-0">Symptoms:</span>
                                            <span class="text-slate-700">{{ $consultation->symptoms }}</span>
                                        </div>
                                    @endif

                                    @php $vitals = $consultation->vitals ?? []; @endphp
                                    @if(!empty(array_filter($vitals)))
                                        <div class="flex flex-wrap gap-2 pt-1 border-t border-slate-200/60">
                                            @if(!empty($vitals['blood_pressure']))
                                                <span class="bg-white px-2 py-0.5 rounded border border-slate-200 text-[11px] text-slate-700">
                                                    BP: <strong>{{ $vitals['blood_pressure'] }}</strong>
                                                </span>
                                            @endif
                                            @if(!empty($vitals['heart_rate']))
                                                <span class="bg-white px-2 py-0.5 rounded border border-slate-200 text-[11px] text-slate-700">
                                                    Pulse: <strong>{{ $vitals['heart_rate'] }} bpm</strong>
                                                </span>
                                            @endif
                                            @if(!empty($vitals['temperature']))
                                                <span class="bg-white px-2 py-0.5 rounded border border-slate-200 text-[11px] text-slate-700">
                                                    Temp: <strong>{{ $vitals['temperature'] }}°F</strong>
                                                </span>
                                            @endif
                                            @if(!empty($vitals['oxygen_saturation']))
                                                <span class="bg-white px-2 py-0.5 rounded border border-slate-200 text-[11px] text-slate-700">
                                                    SpO2: <strong>{{ $vitals['oxygen_saturation'] }}%</strong>
                                                </span>
                                            @endif
                                            @if(!empty($vitals['weight']))
                                                <span class="bg-white px-2 py-0.5 rounded border border-slate-200 text-[11px] text-slate-700">
                                                    Weight: <strong>{{ $vitals['weight'] }} kg</strong>
                                                </span>
                                            @endif
                                            @if(!empty($vitals['bmi']))
                                                <span class="bg-white px-2 py-0.5 rounded border border-slate-200 text-[11px] text-slate-700">
                                                    BMI: <strong>{{ $vitals['bmi'] }}</strong>
                                                </span>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @endif

        <!-- Tab 5: Prescriptions -->
        @if($activeTab === 'prescriptions')
            <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-xs space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider">Digital Prescriptions Issued</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Rx medication regimens, dosages, intake instructions, and follow-up schedules.</p>
                    </div>
                    @can('prescriptions.create')
                    <a href="{{ route('prescriptions.create') }}?patient_id={{ $patient->id }}" 
                       class="px-3.5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-semibold shadow-xs transition flex items-center space-x-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        <span>Issue Prescription</span>
                    </a>
                    @endcan
                </div>

                @if($patient->prescriptions->isEmpty())
                    <div class="p-8 text-center text-slate-400 text-xs">
                        <svg class="w-10 h-10 mx-auto text-slate-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <p>No digital prescriptions issued for this patient yet.</p>
                        @can('prescriptions.create')
                        <a href="{{ route('prescriptions.create') }}?patient_id={{ $patient->id }}" class="mt-2 inline-block font-semibold text-blue-600 hover:underline">
                            + Issue First Prescription
                        </a>
                        @endcan
                    </div>
                @else
                    <div class="divide-y divide-slate-100">
                        @foreach($patient->prescriptions as $rx)
                            <div class="py-4 hover:bg-slate-50/60 rounded-xl px-3 transition space-y-3">
                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                                    <div class="flex items-start space-x-3">
                                        <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 font-bold flex flex-col items-center justify-center shrink-0">
                                            <span class="text-xs font-serif font-black">℞</span>
                                            <span class="text-[9px] font-mono leading-none">{{ $rx->items->count() }} Med</span>
                                        </div>
                                        <div>
                                            <div class="flex items-center space-x-2">
                                                @can('prescriptions.view')
                                                <a href="{{ route('prescriptions.show', $rx) }}" class="font-bold text-xs font-mono text-slate-900 hover:text-blue-600">
                                                    {{ $rx->prescription_number }}
                                                </a>
                                                @else
                                                    <span class="font-bold text-xs font-mono text-slate-900">{{ $rx->prescription_number }}</span>
                                                @endcan
                                                @if($rx->diagnosis)
                                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-semibold bg-slate-100 text-slate-700">
                                                        {{ $rx->diagnosis }}
                                                    </span>
                                                @endif
                                            </div>
                                            <p class="text-xs text-slate-500 mt-0.5">
                                                Prescribed on {{ $rx->prescription_date->format('M d, Y') }} • Doctor: {{ $rx->doctor->name ?? 'Dr. Specialist' }}
                                                @if($rx->follow_up_date)
                                                    • Follow-up: <span class="font-medium text-blue-600">{{ $rx->follow_up_date->format('M d, Y') }}</span>
                                                @endif
                                            </p>
                                        </div>
                                    </div>

                                    <div class="flex items-center space-x-2 shrink-0">
                                        @can('prescriptions.print')
                                        <a href="{{ route('prescriptions.print', $rx) }}" target="_blank"
                                           class="px-2.5 py-1.5 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-semibold text-xs transition flex items-center space-x-1">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                                            </svg>
                                            <span>Print Rx</span>
                                        </a>
                                        @endcan
                                        @can('prescriptions.view')
                                        <a href="{{ route('prescriptions.show', $rx) }}" 
                                           class="px-2.5 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition">
                                            View
                                        </a>
                                        @endcan
                                        @can('prescriptions.edit')
                                        <a href="{{ route('prescriptions.edit', $rx) }}" 
                                           class="px-2.5 py-1.5 rounded-lg bg-slate-50 hover:bg-slate-100 text-slate-600 font-semibold text-xs border border-slate-200 transition">
                                            Edit
                                        </a>
                                        @endcan
                                    </div>
                                </div>

                                <!-- Medicine Regimen Summary -->
                                @if($rx->items->isNotEmpty())
                                    <div class="bg-slate-50 rounded-xl p-3 space-y-1 text-xs">
                                        <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Medication Regimen:</div>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                            @foreach($rx->items as $item)
                                                <div class="flex items-center justify-between bg-white px-3 py-1.5 rounded-lg border border-slate-200/80">
                                                    <div>
                                                        <span class="font-bold text-slate-900">{{ $item->medicine_name }}</span>
                                                        <span class="text-slate-500 text-[11px] ml-1">({{ $item->dosage }})</span>
                                                    </div>
                                                    <span class="text-[11px] font-mono text-blue-600 font-semibold">
                                                        {{ $item->frequency }} • {{ $item->duration }}
                                                    </span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @endif

        <!-- Tab 6: Invoices -->
        @if($activeTab === 'invoices')
            <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-xs space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider">Patient Invoices & Statements</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Itemized professional bills, diagnostic fees, and balance receivables.</p>
                    </div>
                    @can('invoices.create')
                    <a href="{{ route('invoices.create') }}?patient_id={{ $patient->id }}" 
                       class="px-3.5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-semibold shadow-xs transition flex items-center space-x-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        <span>New Invoice</span>
                    </a>
                    @endcan
                </div>

                @if($patient->invoices->isEmpty())
                    <div class="p-8 text-center text-slate-400 text-xs">
                        <svg class="w-10 h-10 mx-auto text-slate-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"></path>
                        </svg>
                        <p>No billing invoices generated for this patient yet.</p>
                        @can('invoices.create')
                        <a href="{{ route('invoices.create') }}?patient_id={{ $patient->id }}" class="mt-2 inline-block font-semibold text-blue-600 hover:underline">
                            + Generate First Invoice
                        </a>
                        @endcan
                    </div>
                @else
                    <div class="divide-y divide-slate-100">
                        @foreach($patient->invoices as $inv)
                            <div class="py-4 hover:bg-slate-50/60 rounded-xl px-3 transition space-y-3">
                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                                    <div class="flex items-start space-x-3">
                                        <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 font-bold flex flex-col items-center justify-center shrink-0">
                                            <span class="text-[10px] uppercase font-bold">{{ $inv->invoice_date->format('M') }}</span>
                                            <span class="text-xs font-bold leading-none">{{ $inv->invoice_date->format('d') }}</span>
                                        </div>
                                        <div>
                                            <div class="flex items-center space-x-2">
                                                @can('invoices.view')
                                                <a href="{{ route('invoices.show', $inv) }}" class="font-bold text-xs font-mono text-blue-600 hover:underline">
                                                    {{ $inv->invoice_number }}
                                                </a>
                                                @else
                                                    <span class="font-bold text-xs font-mono text-slate-900">{{ $inv->invoice_number }}</span>
                                                @endcan
                                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold border whitespace-nowrap {{ $inv->status_badge_class }}">
                                                    <span class="w-1.5 h-1.5 rounded-full shrink-0 {{ $inv->status === 'paid' ? 'bg-emerald-500' : ($inv->status === 'partially_paid' ? 'bg-amber-500' : ($inv->status === 'overdue' ? 'bg-rose-500' : ($inv->status === 'cancelled' ? 'bg-slate-400' : 'bg-blue-500'))) }}"></span>
                                                    <span>{{ $inv->status_label }}</span>
                                                </span>
                                            </div>
                                            <p class="text-xs text-slate-500 mt-0.5">
                                                {{ $inv->items->count() }} item(s) • Doctor: {{ $inv->doctor->name ?? 'General Clinic' }}
                                                @if($inv->due_date)
                                                    • Due: <span class="font-medium {{ $inv->due_date->isPast() && $inv->balance_due > 0 ? 'text-rose-600' : 'text-slate-600' }}">{{ $inv->due_date->format('M d, Y') }}</span>
                                                @endif
                                            </p>
                                        </div>
                                    </div>

                                    <div class="flex items-center space-x-4">
                                        <div class="text-right text-xs">
                                            <div class="font-bold text-slate-900 text-sm">₹{{ number_format($inv->grand_total, 2) }}</div>
                                            <div class="text-[11px] {{ $inv->balance_due > 0 ? 'text-rose-600 font-bold' : 'text-emerald-600 font-semibold' }}">
                                                {{ $inv->balance_due > 0 ? 'Due: ₹' . number_format($inv->balance_due, 2) : 'Settled' }}
                                            </div>
                                        </div>

                                        <div class="flex items-center space-x-1.5 shrink-0">
                                            @can('invoices.view')
                                            <a href="{{ route('invoices.print', $inv) }}" target="_blank" 
                                               class="px-2 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs rounded-lg transition" title="Print Invoice">
                                                🖨
                                            </a>
                                            <a href="{{ route('invoices.show', $inv) }}" 
                                               class="px-2.5 py-1 bg-blue-50 hover:bg-blue-100 text-blue-700 font-semibold text-xs rounded-lg transition">
                                                View
                                            </a>
                                            @endcan
                                            @can('invoices.edit')
                                            <a href="{{ route('invoices.edit', $inv) }}" 
                                               class="px-2 py-1 bg-slate-50 hover:bg-slate-100 text-slate-600 font-semibold text-xs border border-slate-200 rounded-lg transition">
                                                Edit
                                            </a>
                                            @endcan
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @endif

        <!-- Tab 7: Payments -->
        @if($activeTab === 'payments')
            <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-xs space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider">Payment Transaction History</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Chronological settlement receipts across Cash, Cards, UPI, and Bank transfers.</p>
                    </div>
                </div>

                @if($patient->payments->isEmpty())
                    <div class="p-8 text-center text-slate-400 text-xs">
                        <svg class="w-10 h-10 mx-auto text-slate-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                        <p>No settlement payment records logged for this patient yet.</p>
                    </div>
                @else
                    <div class="divide-y divide-slate-100">
                        @foreach($patient->payments as $pay)
                            <div class="py-3.5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 hover:bg-slate-50/60 rounded-xl px-3 transition">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 font-bold flex flex-col items-center justify-center shrink-0">
                                        <span class="text-xs font-bold leading-none">₹</span>
                                    </div>
                                    <div>
                                        <div class="flex items-center space-x-2">
                                            <span class="font-bold text-xs font-mono text-slate-900">{{ $pay->receipt_number }}</span>
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold border {{ $pay->method_badge_class }}">
                                                {{ $pay->payment_method }}
                                            </span>
                                            @if($pay->invoice)
                                                <a href="{{ route('invoices.show', $pay->invoice) }}" class="font-mono text-[11px] text-blue-600 hover:underline">
                                                    {{ $pay->invoice->invoice_number }}
                                                </a>
                                            @endif
                                        </div>
                                        <p class="text-xs text-slate-500 mt-0.5">
                                            {{ $pay->payment_date->format('M d, Y • h:i A') }}
                                            @if($pay->transaction_reference)
                                                • Ref: <span class="font-mono text-slate-600">{{ $pay->transaction_reference }}</span>
                                            @endif
                                        </p>
                                    </div>
                                </div>

                                <div class="flex items-center space-x-3">
                                    <span class="font-bold text-sm text-emerald-700">₹{{ number_format($pay->amount, 2) }}</span>
                                    @can('invoices.payment_management')
                                    <a href="{{ route('payments.receipt', $pay) }}" target="_blank" 
                                       class="px-2.5 py-1 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-semibold text-xs rounded-lg transition flex items-center space-x-1">
                                        <span>Receipt ↗</span>
                                    </a>
                                    @endcan
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @endif

        <!-- Tab 8: Notes -->
        @if($activeTab === 'notes')
            <div class="space-y-6">
                <!-- Add Note Form -->
                <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-xs">
                    <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider border-b border-slate-100 pb-3 mb-4">
                        Add Clinical or Administrative Note
                    </h3>
                    @can('patients.edit')
                    <form method="POST" action="{{ route('patients.notes.store', $patient) }}" class="space-y-3">
                        @csrf
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div class="sm:col-span-1">
                                <input type="text" name="title" placeholder="Note Title (e.g. Phone Followup, Diet Advice)"
                                       class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:bg-white focus:border-blue-500 outline-none">
                            </div>
                            <div class="sm:col-span-2">
                                <textarea name="note" rows="2" required placeholder="Write clinical observations, patient communications, or reminders..."
                                          class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:bg-white focus:border-blue-500 outline-none"></textarea>
                            </div>
                        </div>
                        <div class="flex justify-end">
                            <button type="submit" class="px-5 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold shadow-md shadow-blue-500/20 transition">
                                Add Note
                            </button>
                        </div>
                    </form>
                    @endcan
                </div>

                <!-- Existing Notes Stream -->
                <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-xs space-y-4">
                    <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider border-b border-slate-100 pb-3">
                        Patient Notes History ({{ $patient->notes->count() }})
                    </h3>

                    <div class="divide-y divide-slate-100">
                        @forelse($patient->notes as $note)
                            <div class="py-4 space-y-1.5">
                                <div class="flex items-center justify-between">
                                    <h4 class="text-xs font-bold text-slate-900">{{ $note->title ?: 'Clinical Note' }}</h4>
                                    <div class="flex items-center space-x-2">
                                        <span class="text-[10px] text-slate-400">
                                            By {{ $note->author->name ?? 'Staff' }} • {{ $note->created_at->format('d M Y, h:i A') }}
                                        </span>
                                        @can('patients.edit')
                                        <form action="{{ route('patients.notes.destroy', $note) }}" method="POST" onsubmit="return confirm('Remove note?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-slate-400 hover:text-rose-600 p-1">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                            </button>
                                        </form>
                                        @endcan
                                    </div>
                                </div>
                                <p class="text-xs text-slate-600 whitespace-pre-line leading-relaxed">{{ $note->note }}</p>
                            </div>
                        @empty
                            <p class="text-xs text-slate-400 py-6 text-center">No notes recorded yet for this patient.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        @endif

        <!-- Tab 9: Documents / Attachments -->
        @if($activeTab === 'documents')
            <div class="space-y-6">
                <!-- Upload Document Card -->
                <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-xs">
                    <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider border-b border-slate-100 pb-3 mb-4">
                        Upload Medical Attachment / Diagnostic Report
                    </h3>
                    @can('patients.edit')
                    <form method="POST" action="{{ route('patients.documents.store', $patient) }}" enctype="multipart/form-data" class="space-y-4">
                        @csrf
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Document Title *</label>
                                <input type="text" name="title" required placeholder="e.g. Blood Test CBC Report, Chest X-Ray"
                                       class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:bg-white focus:border-blue-500 outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Select File (PDF, JPG, PNG, DOCX - Max 10MB) *</label>
                                <input type="file" name="document" required accept=".pdf,.jpg,.jpeg,.png,.docx,.txt"
                                       class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                            </div>
                        </div>
                        <div class="flex justify-end pt-1">
                            <button type="submit" class="px-5 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold shadow-md shadow-blue-500/20 transition">
                                Upload Document
                            </button>
                        </div>
                    </form>
                    @endcan
                </div>

                <!-- Existing Documents List -->
                <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-xs space-y-4">
                    <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider border-b border-slate-100 pb-3">
                        Attached Records & Reports ({{ $patient->documents->count() }})
                    </h3>

                    <div class="divide-y divide-slate-100">
                        @forelse($patient->documents as $doc)
                            <div class="py-3.5 flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-xs">
                                        {{ strtoupper($doc->file_type ?? 'DOC') }}
                                    </div>
                                    <div>
                                        <p class="text-xs font-semibold text-slate-900">{{ $doc->title }}</p>
                                        <p class="text-[11px] text-slate-400">
                                            Size: {{ $doc->formatted_size }} • Uploaded by {{ $doc->uploader->name ?? 'Staff' }} on {{ $doc->created_at->format('d M Y') }}
                                        </p>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <a href="{{ route('patients.documents.download', $doc) }}" 
                                       class="px-3 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 rounded-lg text-xs font-semibold transition">
                                        Download
                                    </a>
                                    @can('patients.edit')
                                    <form action="{{ route('patients.documents.destroy', $doc) }}" method="POST" onsubmit="return confirm('Delete this document?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-2.5 py-1.5 text-rose-600 hover:bg-rose-50 rounded-lg text-xs font-medium transition">
                                            Delete
                                        </button>
                                    </form>
                                    @endcan
                                </div>
                            </div>
                        @empty
                            <p class="text-xs text-slate-400 py-6 text-center">No documents or medical reports uploaded yet.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        @endif

    </div>

</div>
@endsection

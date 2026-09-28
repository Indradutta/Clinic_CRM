@extends('layouts.app')

@section('title', 'Prescription ' . $prescription->prescription_number)

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Header Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2.5 flex-wrap mb-2">
                <span class="font-mono text-xs font-bold text-brand-700 bg-brand-50 px-3 py-1 rounded-lg border border-brand-100/60">
                    {{ $prescription->prescription_number }}
                </span>
                <span class="text-xs text-slate-500 font-medium">
                    Issued on {{ $prescription->prescription_date->format('d M Y') }}
                </span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-slate-900">
                Digital Prescription for {{ $prescription->patient->name }}
            </h1>
        </div>

        <div class="flex items-center gap-2.5 flex-wrap">
            @can('prescriptions.print')
            <a href="{{ route('prescriptions.print', $prescription) }}" target="_blank"
               class="btn-pill-primary">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                </svg>
                <span>Print Prescription</span>
            </a>
            @endcan
            <a href="{{ route('prescriptions.index') }}" 
               class="btn-pill-secondary">
                &larr; All Prescriptions
            </a>
        </div>
    </div>

    <!-- Digital Prescription Card -->
    <div class="glass-card-elevated rounded-2xl border border-white/80 shadow-[0_12px_40px_rgb(0,0,0,0.04)] p-6 sm:p-8 space-y-6">

        <!-- Practice & Doctor Header -->
        <div class="border-b border-slate-100 pb-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-lg font-bold text-slate-900">{{ $settings['clinic_name'] ?? 'MediFlow Polyclinic' }}</h2>
                <p class="text-xs text-slate-500 mt-0.5">{{ $settings['clinic_address'] ?? '42 Healthcare Avenue, Medical Enclave' }}</p>
                <p class="text-xs text-slate-500 mt-0.5">Phone: {{ $settings['clinic_phone'] ?? '+91 11 2345 6789' }} • Email: {{ $settings['clinic_email'] ?? 'contact@mediflowclinic.com' }}</p>
            </div>
            <div class="text-left sm:text-right">
                <h3 class="text-sm font-bold text-slate-900">{{ $prescription->doctor->name ?? $settings['doctor_name'] }}</h3>
                <p class="text-xs text-slate-600 mt-0.5">{{ $settings['doctor_qualification'] ?? 'MBBS, MD (General Medicine)' }}</p>
                <p class="text-xs font-mono text-brand-600 font-semibold mt-0.5">Reg: {{ $settings['doctor_registration_number'] ?? 'MCI-482910' }}</p>
            </div>
        </div>

        <!-- Patient Demographics Strip -->
        <div class="p-4 sm:p-5 bg-white/75 rounded-2xl border border-slate-200/70 grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs shadow-2xs">
            <div>
                <span class="text-slate-400 font-bold uppercase text-[10px] block">Patient Name</span>
                <span class="font-bold text-slate-900 mt-1 block">{{ $prescription->patient->name }}</span>
            </div>
            <div>
                <span class="text-slate-400 font-bold uppercase text-[10px] block">Patient ID</span>
                <span class="font-mono text-brand-600 font-bold mt-1 block">{{ $prescription->patient->patient_id }}</span>
            </div>
            <div>
                <span class="text-slate-400 font-bold uppercase text-[10px] block">Age / Gender</span>
                <span class="font-semibold text-slate-800 mt-1 block">{{ $prescription->patient->age ?? 'N/A' }} yrs / {{ $prescription->patient->gender }}</span>
            </div>
            <div>
                <span class="text-slate-400 font-bold uppercase text-[10px] block">Blood Group</span>
                <span class="font-bold text-rose-600 mt-1 block">{{ $prescription->patient->blood_group ?? '—' }}</span>
            </div>
        </div>

        <!-- Clinical Diagnosis -->
        @if($prescription->diagnosis)
            <div class="p-4 bg-brand-50/50 rounded-2xl border border-brand-100/70 shadow-2xs">
                <span class="text-[10px] font-bold uppercase tracking-wider text-brand-700 block">Diagnosis</span>
                <p class="text-sm font-bold text-slate-900 mt-1">{{ $prescription->diagnosis }}</p>
            </div>
        @endif

        <!-- Prescribed Medications Table -->
        <div class="space-y-3">
            <div class="flex items-center gap-2">
                <span class="text-2xl font-serif font-bold text-brand-600">℞</span>
                <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider">Medications Prescribed</h3>
            </div>

            <div class="overflow-x-auto rounded-2xl border border-slate-200/70 shadow-2xs bg-white/70">
                <table class="w-full text-left text-xs text-slate-700">
                    <thead class="bg-slate-50/70 text-[11px] uppercase font-bold text-slate-400 border-b border-slate-200/70">
                        <tr>
                            <th class="px-4 py-3.5">#</th>
                            <th class="px-4 py-3.5">Medicine Name</th>
                            <th class="px-3 py-3.5">Dosage</th>
                            <th class="px-3 py-3.5">Frequency</th>
                            <th class="px-3 py-3.5">Duration</th>
                            <th class="px-3 py-3.5">Route & Timing</th>
                            <th class="px-4 py-3.5">Instructions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($prescription->items as $idx => $item)
                            <tr class="hover:bg-white/90 transition">
                                <td class="px-4 py-3.5 font-semibold text-slate-400">{{ $idx + 1 }}</td>
                                <td class="px-4 py-3.5 font-bold text-slate-900">{{ $item->medicine_name }}</td>
                                <td class="px-3 py-3.5">{{ $item->dosage }}</td>
                                <td class="px-3 py-3.5 font-bold text-brand-600">{{ $item->frequency }}</td>
                                <td class="px-3 py-3.5">{{ $item->duration }}</td>
                                <td class="px-3 py-3.5">
                                    <span class="block font-medium">{{ $item->route }}</span>
                                    <span class="text-[11px] text-slate-400">{{ $item->timing }}</span>
                                </td>
                                <td class="px-4 py-3.5 text-slate-500 italic">{{ $item->instructions ?: '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Diagnostic Tests & Advice -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs pt-2">
            @if($prescription->tests)
                <div class="p-4 sm:p-5 bg-white/70 rounded-2xl border border-slate-200/70 shadow-2xs">
                    <span class="text-[10px] font-bold uppercase text-slate-400 block tracking-wider">Recommended Investigations / Tests</span>
                    @if(is_array($prescription->tests))
                        <p class="text-slate-800 mt-1.5 leading-relaxed font-medium">{{ implode(', ', $prescription->tests) }}</p>
                    @else
                        <p class="text-slate-800 mt-1.5 leading-relaxed font-medium">{{ $prescription->tests }}</p>
                    @endif
                </div>
            @endif

            @if($prescription->advice)
                <div class="p-4 sm:p-5 bg-white/70 rounded-2xl border border-slate-200/70 shadow-2xs">
                    <span class="text-[10px] font-bold uppercase text-slate-400 block tracking-wider">General Advice & Precautions</span>
                    <p class="text-slate-800 mt-1.5 leading-relaxed">{{ $prescription->advice }}</p>
                </div>
            @endif
        </div>

        <!-- Follow-up date & Disclaimer -->
        <div class="border-t border-slate-100 pt-4 flex flex-col sm:flex-row sm:items-center sm:justify-between text-xs text-slate-500 gap-2">
            @if($prescription->follow_up_date)
                <div class="font-bold text-brand-700">
                    🗓 Next Follow-Up: {{ $prescription->follow_up_date->format('l, d F Y') }}
                </div>
            @else
                <div>Follow-up as needed.</div>
            @endif
            <div class="text-[11px] text-slate-400 italic">
                {{ $settings['prescription_footer'] ?? 'Please take medicines strictly according to prescribed dosage.' }}
            </div>
        </div>

    </div>

</div>
@endsection

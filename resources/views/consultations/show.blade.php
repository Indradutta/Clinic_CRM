@extends('layouts.app')

@section('title', 'Consultation Record - ' . $consultation->patient->name)

@section('content')
<div class="max-w-5xl mx-auto space-y-8">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex flex-wrap items-center gap-2 mb-2">
                <span class="text-xs font-bold text-brand-700 bg-brand-50 px-3 py-1 rounded-lg border border-brand-200/80">
                    {{ $consultation->consultation_date->format('d M Y, h:i A') }}
                </span>
                @if($consultation->appointment)
                    <span class="text-xs font-mono font-semibold text-slate-500 bg-slate-100 px-3 py-1 rounded-lg">
                        {{ $consultation->appointment->appointment_id }}
                    </span>
                @endif
            </div>
            <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-slate-900">
                Consultation: {{ $consultation->patient->name }}
            </h1>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            @if(!$consultation->prescription)
                <a href="{{ route('prescriptions.create', ['consultation_id' => $consultation->id]) }}" 
                   class="btn-pill-primary inline-flex items-center gap-1.5 text-xs">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path>
                    </svg>
                    <span>Create Prescription</span>
                </a>
            @endif

            <a href="{{ route('patients.show', ['patient' => $consultation->patient_id, 'tab' => 'consultations']) }}" 
               class="btn-pill-secondary text-xs">
                &larr; Patient Consultations
            </a>
        </div>
    </div>

    <!-- Vitals Banner -->
    @if($consultation->vitals)
        <div class="glass-card rounded-2xl p-5 sm:p-6 space-y-3">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block">Recorded Patient Vitals</span>
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 text-xs">
                @foreach($consultation->vitals as $vKey => $vVal)
                    <div class="p-3.5 rounded-2xl bg-slate-50/80 border border-slate-100">
                        <span class="text-[10px] font-bold uppercase text-slate-400 block">{{ strtoupper(str_replace('_', ' ', $vKey)) }}</span>
                        <span class="text-base font-extrabold text-slate-800 mt-1 block">{{ $vVal }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Consultation Findings Card -->
    <div class="glass-card-elevated rounded-2xl p-6 sm:p-8 space-y-6">
        
        <!-- Diagnosis Banner -->
        <div class="p-5 bg-brand-50/70 rounded-2xl border border-brand-100">
            <span class="text-[11px] font-bold uppercase tracking-wider text-brand-700 block">Provisional / Confirmed Diagnosis</span>
            <h2 class="text-xl font-bold text-slate-900 mt-1">{{ $consultation->diagnosis }}</h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-xs">
            <div>
                <span class="text-slate-400 font-bold uppercase text-[10px] tracking-wider block">Presenting Symptoms & Complaints</span>
                <p class="text-slate-800 mt-2 p-4 glass-card rounded-2xl leading-relaxed font-medium">
                    {{ $consultation->symptoms }}
                </p>
            </div>

            <div>
                <span class="text-slate-400 font-bold uppercase text-[10px] tracking-wider block">Attending Clinician</span>
                <div class="text-slate-800 mt-2 p-4 glass-card rounded-2xl flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-brand-100 text-brand-700 flex items-center justify-center font-bold text-xs">
                        Dr
                    </div>
                    <div>
                        <p class="font-bold text-slate-900 text-sm">{{ $consultation->doctor->name ?? 'Dr. Specialist' }}</p>
                        <p class="text-[11px] text-slate-400">Consulting Physician</p>
                    </div>
                </div>
            </div>

            @if($consultation->medical_notes)
                <div class="md:col-span-2">
                    <span class="text-slate-400 font-bold uppercase text-[10px] tracking-wider block">Physical Examination Findings</span>
                    <p class="text-slate-800 mt-2 p-4 glass-card rounded-2xl leading-relaxed">
                        {{ $consultation->medical_notes }}
                    </p>
                </div>
            @endif

            @if($consultation->treatment)
                <div class="md:col-span-2">
                    <span class="text-slate-400 font-bold uppercase text-[10px] tracking-wider block">Treatment Plan & Recommendations</span>
                    <p class="text-slate-800 mt-2 p-4 glass-card rounded-2xl leading-relaxed">
                        {{ $consultation->treatment }}
                    </p>
                </div>
            @endif
        </div>
    </div>

    <!-- Linked Prescription (if created) -->
    @if($consultation->prescription)
        <div class="glass-card rounded-2xl p-6 sm:p-8 space-y-5">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3.5">
                <div>
                    <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Digital Prescription Issued</h3>
                    <span class="text-xs text-brand-600 font-mono font-bold">{{ $consultation->prescription->prescription_number }}</span>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('prescriptions.show', $consultation->prescription) }}" 
                       class="btn-pill-secondary text-xs py-1.5 px-3">
                        View Rx
                    </a>
                    <a href="{{ route('prescriptions.print', $consultation->prescription) }}" target="_blank"
                       class="btn-pill-primary text-xs py-1.5 px-3.5 inline-flex items-center gap-1">
                        <span>Print Rx</span> 🖨
                    </a>
                </div>
            </div>

            <div class="divide-y divide-slate-100 text-xs">
                @foreach($consultation->prescription->items as $item)
                    <div class="py-3 flex items-center justify-between">
                        <div>
                            <span class="font-bold text-slate-900">{{ $item->medicine_name }}</span>
                            <span class="text-slate-400 ml-2">({{ $item->dosage }})</span>
                        </div>
                        <div class="text-slate-600 text-right">
                            <span class="font-medium">{{ $item->frequency }} • {{ $item->duration }}</span>
                            <span class="text-slate-400 block text-[11px]">{{ $item->timing }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

</div>
@endsection
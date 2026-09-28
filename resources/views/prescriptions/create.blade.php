@extends('layouts.app')

@section('title', 'Author Digital Prescription')

@section('content')
<div class="max-w-4xl mx-auto space-y-6" x-data="{
    medicines: [
        { medicine_name: '', dosage: '1 tablet', frequency: '1-0-1', duration: '5 days', route: 'Oral', timing: 'After food', instructions: '' }
    ],
    addMedicine() {
        this.medicines.push({ medicine_name: '', dosage: '1 tablet', frequency: '1-0-1', duration: '5 days', route: 'Oral', timing: 'After food', instructions: '' });
    },
    removeMedicine(index) {
        if (this.medicines.length > 1) {
            this.medicines.splice(index, 1);
        }
    }
}">

    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-lg bg-brand-50 border border-brand-100/60 text-brand-700 text-xs font-semibold mb-2">
                <span class="w-1.5 h-1.5 rounded-full bg-brand-500"></span>
                Prescription Authoring
            </div>
            <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-slate-900">Author New Digital Prescription</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">Structured medication instructions, laboratory tests, and clinical advice.</p>
        </div>
        <a href="{{ route('prescriptions.index') }}" class="btn-pill-secondary">
            &larr; Back to Prescriptions
        </a>
    </div>

    <!-- Pre-selected Patient Banner if arriving from consultation -->
    @if($selectedPatient)
        <div class="glass-card rounded-2xl p-5 border border-brand-200/50 bg-brand-50/40 flex items-center justify-between backdrop-blur-md">
            <div class="flex items-center gap-3.5">
                <div class="w-11 h-11 rounded-full bg-brand-500 text-white flex items-center justify-center font-bold text-sm shadow-xs">
                    {{ strtoupper(substr($selectedPatient->name, 0, 2)) }}
                </div>
                <div>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-brand-700 block">Patient</span>
                    <p class="text-sm font-bold text-slate-900">{{ $selectedPatient->name }} <span class="font-mono text-xs font-normal text-slate-500">({{ $selectedPatient->patient_id }})</span></p>
                    <p class="text-xs text-slate-500 mt-0.5">Gender: {{ $selectedPatient->gender }} • Age: {{ $selectedPatient->age ?? 'N/A' }} yrs • Blood: {{ $selectedPatient->blood_group ?? '—' }}</p>
                </div>
            </div>
            @if(!empty($selectedPatient->allergies))
                <div class="px-3.5 py-1.5 bg-rose-50 text-rose-800 rounded-lg text-xs font-bold border border-rose-200 shadow-2xs">
                    ⚠️ Allergy: {{ $selectedPatient->allergies }}
                </div>
            @endif
        </div>
    @endif

    <form method="POST" action="{{ route('prescriptions.store') }}" class="space-y-6" novalidate>
        @csrf
        @if($consultation)
            <input type="hidden" name="consultation_id" value="{{ $consultation->id }}">
        @endif
        @if($appointment)
            <input type="hidden" name="appointment_id" value="{{ $appointment->id }}">
        @endif

        <!-- 1. Patient & Doctor Selection -->
        <div class="glass-card-elevated rounded-2xl p-6 sm:p-8 border border-white/80 shadow-[0_12px_40px_rgb(0,0,0,0.04)] space-y-6">
            <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider border-b border-slate-100 pb-3.5 flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-brand-500"></span>
                <span>1. Patient & Doctor Information</span>
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Patient *</label>
                    <select name="patient_id" required class="apple-input">
                        @if($selectedPatient)
                            <option value="{{ $selectedPatient->id }}" selected>{{ $selectedPatient->name }} ({{ $selectedPatient->patient_id }})</option>
                        @else
                            <option value="">-- Choose Patient --</option>
                            @foreach($patients as $p)
                                <option value="{{ $p->id }}" {{ old('patient_id') == $p->id ? 'selected' : '' }}>{{ $p->name }} ({{ $p->patient_id }})</option>
                            @endforeach
                        @endif
                    </select>
                    @error('patient_id') <p class="text-[11px] text-rose-500 mt-1 font-medium">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Prescribing Doctor *</label>
                    <select name="doctor_id" required class="apple-input">
                        @foreach($doctors as $doc)
                            <option value="{{ $doc->id }}" {{ old('doctor_id', $consultation->doctor_id ?? auth()->id()) == $doc->id ? 'selected' : '' }}>
                                {{ $doc->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('doctor_id') <p class="text-[11px] text-rose-500 mt-1 font-medium">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Prescription Date *</label>
                    <input type="date" name="prescription_date" required 
                           value="{{ old('prescription_date', now()->toDateString()) }}"
                           class="apple-input">
                    @error('prescription_date') <p class="text-[11px] text-rose-500 mt-1 font-medium">{{ $message }}</p> @enderror
                </div>
            </div>

            <!-- Diagnosis & Symptoms -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 pt-1">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Diagnosis</label>
                    <input type="text" name="diagnosis" value="{{ old('diagnosis', $consultation->diagnosis ?? '') }}" placeholder="Clinical diagnosis..."
                           class="apple-input">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Symptoms / Notes</label>
                    <input type="text" name="symptoms" value="{{ old('symptoms', $consultation->symptoms ?? '') }}" placeholder="Chief complaints..."
                           class="apple-input">
                </div>
            </div>
        </div>

        <!-- 2. Structured Medicine Line Items -->
        <div class="glass-card-elevated rounded-2xl p-6 sm:p-8 border border-white/80 shadow-[0_12px_40px_rgb(0,0,0,0.04)] space-y-6">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3.5">
                <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                    <span class="text-brand-600 font-serif font-bold text-lg">℞</span>
                    <span>2. Prescribed Medications & Dosage</span>
                </h2>
                <button type="button" @click="addMedicine()" 
                        class="btn-pill-secondary !text-brand-600 hover:!text-brand-700 !py-1.5 !px-3.5">
                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path>
                    </svg>
                    <span>Add Medicine</span>
                </button>
            </div>

            <!-- Medicines Repeater List -->
            <div class="space-y-4">
                <template x-for="(med, index) in medicines" :key="index">
                    <div class="p-4 sm:p-5 bg-white/75 border border-slate-200/70 rounded-2xl space-y-3.5 shadow-2xs">
                        <div class="flex items-center justify-between">
                            <span class="text-[11px] font-bold text-brand-700 bg-brand-50 px-2.5 py-0.5 rounded-md border border-brand-100/60 uppercase tracking-wider" x-text="'Medicine #' + (index + 1)"></span>
                            <button type="button" @click="removeMedicine(index)" 
                                    class="text-rose-500 hover:text-rose-700 p-1 text-xs font-bold transition"
                                    x-show="medicines.length > 1" title="Remove medicine">
                                ✕ Remove
                            </button>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 lg:grid-cols-6 gap-3">
                            <!-- Medicine Name -->
                            <div class="sm:col-span-2 lg:col-span-2">
                                <label class="block text-[10px] uppercase font-bold text-slate-400 mb-1">Medicine Name *</label>
                                <input type="text" :name="'items[' + index + '][medicine_name]'" x-model="med.medicine_name" required placeholder="e.g. Paracetamol 500 mg"
                                       class="apple-input !py-2">
                            </div>

                            <!-- Dosage -->
                            <div>
                                <label class="block text-[10px] uppercase font-bold text-slate-400 mb-1">Dosage *</label>
                                <input type="text" :name="'items[' + index + '][dosage]'" x-model="med.dosage" required placeholder="e.g. 1 tab / 5ml"
                                       class="apple-input !py-2">
                            </div>

                            <!-- Frequency -->
                            <div>
                                <label class="block text-[10px] uppercase font-bold text-slate-400 mb-1">Frequency *</label>
                                <input type="text" :name="'items[' + index + '][frequency]'" x-model="med.frequency" required placeholder="e.g. 1-0-1 / 3x daily"
                                       class="apple-input !py-2">
                            </div>

                            <!-- Duration -->
                            <div>
                                <label class="block text-[10px] uppercase font-bold text-slate-400 mb-1">Duration *</label>
                                <input type="text" :name="'items[' + index + '][duration]'" x-model="med.duration" required placeholder="e.g. 5 days / 1 mo"
                                       class="apple-input !py-2">
                            </div>

                            <!-- Route -->
                            <div>
                                <label class="block text-[10px] uppercase font-bold text-slate-400 mb-1">Route</label>
                                <select :name="'items[' + index + '][route]'" x-model="med.route"
                                        class="apple-input !py-2">
                                    <option value="Oral">Oral</option>
                                    <option value="Inhalation">Inhalation</option>
                                    <option value="Topical">Topical</option>
                                    <option value="Sublingual">Sublingual</option>
                                    <option value="Eye Drops">Eye Drops</option>
                                    <option value="Injection">Injection</option>
                                </select>
                            </div>
                        </div>

                        <!-- Timing & Instructions Row -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
                            <div>
                                <label class="block text-[10px] uppercase font-bold text-slate-400 mb-1">Timing Relation</label>
                                <select :name="'items[' + index + '][timing]'" x-model="med.timing"
                                        class="apple-input !py-2">
                                    <option value="After food">After food</option>
                                    <option value="Before food">Before food</option>
                                    <option value="With food">With food</option>
                                    <option value="At bedtime">At bedtime</option>
                                    <option value="Empty stomach">Empty stomach</option>
                                    <option value="As needed">As needed (SOS)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[10px] uppercase font-bold text-slate-400 mb-1">Specific Instructions</label>
                                <input type="text" :name="'items[' + index + '][instructions]'" x-model="med.instructions" placeholder="e.g. Take with lukewarm water..."
                                       class="apple-input !py-2">
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- 3. Diagnostic Tests & Advice -->
        <div class="glass-card-elevated rounded-2xl p-6 sm:p-8 border border-white/80 shadow-[0_12px_40px_rgb(0,0,0,0.04)] space-y-6">
            <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider border-b border-slate-100 pb-3.5 flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-brand-500"></span>
                <span>3. Diagnostic Tests, Advice & Follow-Up</span>
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Recommended Lab / Diagnostic Tests
                    </label>
                    <textarea name="tests" rows="3" placeholder="e.g. Complete Blood Count (CBC), Fasting Blood Sugar, Lipid Profile, Chest X-ray..."
                              class="apple-input !rounded-2xl">{{ old('tests') }}</textarea>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        General Advice & Dietary Precautions
                    </label>
                    <textarea name="advice" rows="3" placeholder="e.g. Low sodium diet, brisk walking 30 mins daily, avoid cold foods..."
                              class="apple-input !rounded-2xl">{{ old('advice', $defaultInstructions) }}</textarea>
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Follow-Up Consultation Date
                    </label>
                    <input type="date" name="follow_up_date" value="{{ old('follow_up_date', now()->addDays(7)->toDateString()) }}"
                           class="apple-input w-full sm:w-64">
                </div>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('prescriptions.index') }}" class="btn-pill-secondary">
                Cancel
            </a>
            <button type="submit" class="btn-pill-primary">
                Generate & Save Prescription
            </button>
        </div>
    </form>

</div>
@endsection

@extends('layouts.app')

@section('title', 'Edit Patient - ' . $patient->name)

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-lg bg-brand-50 border border-brand-100/60 text-brand-700 text-xs font-semibold mb-2">
                <span class="w-1.5 h-1.5 rounded-full bg-brand-500"></span>
                Edit Profile
            </div>
            <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-slate-900">Edit Patient Record</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">Update demographic profile and health record for {{ $patient->name }}.</p>
        </div>
        <a href="{{ route('patients.show', $patient) }}" class="btn-pill-secondary">
            &larr; View Patient Profile
        </a>
    </div>

    <form method="POST" action="{{ route('patients.update', $patient) }}" class="space-y-6" novalidate>
        @csrf
        @method('PUT')

        <!-- 1. Basic Information -->
        <div class="glass-card-elevated rounded-2xl p-6 sm:p-8 border border-white/80 shadow-[0_12px_40px_rgb(0,0,0,0.04)] space-y-6">
            <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider border-b border-slate-100 pb-3.5 flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-brand-500"></span>
                <span>1. Basic Demographic Information</span>
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Full Name *</label>
                    <input type="text" name="name" value="{{ old('name', $patient->name) }}" required
                           class="apple-input">
                    @error('name') <p class="text-[11px] text-rose-500 mt-1 font-medium">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Date of Birth</label>
                    <input type="date" name="dob" value="{{ old('dob', $patient->dob ? $patient->dob->format('Y-m-d') : '') }}" max="{{ now()->toDateString() }}"
                           class="apple-input">
                    @error('dob') <p class="text-[11px] text-rose-500 mt-1 font-medium">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Age (Years)</label>
                    <input type="number" name="age" value="{{ old('age', $patient->age) }}" min="0" max="130"
                           class="apple-input">
                    @error('age') <p class="text-[11px] text-rose-500 mt-1 font-medium">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Gender *</label>
                    <select name="gender" required class="apple-input">
                        <option value="Male" {{ old('gender', $patient->gender) === 'Male' ? 'selected' : '' }}>Male</option>
                        <option value="Female" {{ old('gender', $patient->gender) === 'Female' ? 'selected' : '' }}>Female</option>
                        <option value="Other" {{ old('gender', $patient->gender) === 'Other' ? 'selected' : '' }}>Other</option>
                    </select>
                    @error('gender') <p class="text-[11px] text-rose-500 mt-1 font-medium">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Phone Number *</label>
                    <input type="tel" name="phone" value="{{ old('phone', $patient->phone) }}" required inputmode="numeric" minlength="10" maxlength="10" pattern="[6-9][0-9]{9}" autocomplete="tel-national"
                           class="apple-input">
                    @error('phone') <p class="text-[11px] text-rose-500 mt-1 font-medium">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Email Address</label>
                    <input type="email" name="email" value="{{ old('email', $patient->email) }}"
                           class="apple-input">
                    @error('email') <p class="text-[11px] text-rose-500 mt-1 font-medium">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Blood Group</label>
                    <select name="blood_group" class="apple-input">
                        <option value="">Unknown</option>
                        @foreach(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'] as $bg)
                            <option value="{{ $bg }}" {{ old('blood_group', $patient->blood_group) === $bg ? 'selected' : '' }}>{{ $bg }}</option>
                        @endforeach
                    </select>
                    @error('blood_group') <p class="text-[11px] text-rose-500 mt-1 font-medium">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Marital Status</label>
                    <select name="marital_status" class="apple-input">
                        <option value="">Select Status</option>
                        @foreach(['Single', 'Married', 'Divorced', 'Widowed'] as $ms)
                            <option value="{{ $ms }}" {{ old('marital_status', $patient->marital_status) === $ms ? 'selected' : '' }}>{{ $ms }}</option>
                        @endforeach
                    </select>
                    @error('marital_status') <p class="text-[11px] text-rose-500 mt-1 font-medium">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Preferred Language</label>
                    <input type="text" name="preferred_language" value="{{ old('preferred_language', $patient->preferred_language) }}" class="apple-input">
                    @error('preferred_language') <p class="text-[11px] text-rose-500 mt-1 font-medium">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Height (cm)</label>
                    <input type="number" name="height_cm" value="{{ old('height_cm', $patient->height_cm) }}" min="0" max="300" step="0.1" class="apple-input">
                    @error('height_cm') <p class="text-[11px] text-rose-500 mt-1 font-medium">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Weight (kg)</label>
                    <input type="number" name="weight_kg" value="{{ old('weight_kg', $patient->weight_kg) }}" min="0" max="700" step="0.1" class="apple-input">
                    @error('weight_kg') <p class="text-[11px] text-rose-500 mt-1 font-medium">{{ $message }}</p> @enderror
                </div>

                <div class="sm:col-span-2 lg:col-span-3">
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Residential Address</label>
                    <textarea name="address" rows="2"
                              class="apple-input !rounded-2xl">{{ old('address', $patient->address) }}</textarea>
                    @error('address') <p class="text-[11px] text-rose-500 mt-1 font-medium">{{ $message }}</p> @enderror
                </div>
                <div class="sm:col-span-2 lg:col-span-3">
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Main Health Concern</label>
                    <textarea name="primary_concern" rows="2" class="apple-input !rounded-2xl">{{ old('primary_concern', $patient->primary_concern) }}</textarea>
                    @error('primary_concern') <p class="text-[11px] text-rose-500 mt-1 font-medium">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Emergency Contact Name</label>
                    <input type="text" name="emergency_contact_name" value="{{ old('emergency_contact_name', $patient->emergency_contact_name) }}"
                           class="apple-input">
                    @error('emergency_contact_name') <p class="text-[11px] text-rose-500 mt-1 font-medium">{{ $message }}</p> @enderror
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Emergency Contact Phone</label>
                    <input type="tel" name="emergency_contact_phone" value="{{ old('emergency_contact_phone', $patient->emergency_contact_phone) }}" inputmode="numeric" minlength="10" maxlength="10" pattern="[6-9][0-9]{9}" autocomplete="tel-national"
                           class="apple-input">
                    @error('emergency_contact_phone') <p class="text-[11px] text-rose-500 mt-1 font-medium">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <!-- 2. Medical Information -->
        <div class="glass-card-elevated rounded-2xl p-6 sm:p-8 border border-white/80 shadow-[0_12px_40px_rgb(0,0,0,0.04)] space-y-6">
            <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider border-b border-slate-100 pb-3.5 flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                <span>2. Medical & Clinical Background</span>
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-rose-700 uppercase tracking-wider mb-1.5">
                        ⚠️ Known Allergies (Drug / Food / Environmental)
                    </label>
                    <textarea name="allergies" rows="2"
                              class="apple-input !bg-rose-50/40 !border-rose-200 !rounded-2xl focus:!border-rose-400">{{ old('allergies', $patient->allergies) }}</textarea>
                    @error('allergies') <p class="text-[11px] text-rose-500 mt-1 font-medium">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Chronic Medical Conditions</label>
                    <textarea name="chronic_conditions" rows="2"
                              class="apple-input !rounded-2xl">{{ old('chronic_conditions', $patient->chronic_conditions) }}</textarea>
                    @error('chronic_conditions') <p class="text-[11px] text-rose-500 mt-1 font-medium">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Current Ongoing Medications</label>
                    <textarea name="current_medications" rows="2"
                              class="apple-input !rounded-2xl">{{ old('current_medications', $patient->current_medications) }}</textarea>
                    @error('current_medications') <p class="text-[11px] text-rose-500 mt-1 font-medium">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Past Surgeries / Procedures</label>
                    <textarea name="past_surgeries" rows="2"
                              class="apple-input !rounded-2xl">{{ old('past_surgeries', $patient->past_surgeries) }}</textarea>
                    @error('past_surgeries') <p class="text-[11px] text-rose-500 mt-1 font-medium">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Family Medical History</label>
                    <textarea name="family_history" rows="2"
                              class="apple-input !rounded-2xl">{{ old('family_history', $patient->family_history) }}</textarea>
                    @error('family_history') <p class="text-[11px] text-rose-500 mt-1 font-medium">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Smoking Habits</label>
                    <select name="smoking_habits" class="apple-input">
                        <option value="">Select Option</option>
                        @foreach(['Non-smoker', 'Former smoker', 'Occasional smoker', 'Regular smoker'] as $opt)
                            <option value="{{ $opt }}" {{ old('smoking_habits', $patient->smoking_habits) === $opt ? 'selected' : '' }}>{{ $opt }}</option>
                        @endforeach
                    </select>
                    @error('smoking_habits') <p class="text-[11px] text-rose-500 mt-1 font-medium">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Alcohol Consumption</label>
                    <select name="alcohol_consumption" class="apple-input">
                        <option value="">Select Option</option>
                        @foreach(['Non-drinker', 'Occasional', 'Social', 'Moderate', 'Heavy'] as $opt)
                            <option value="{{ $opt }}" {{ old('alcohol_consumption', $patient->alcohol_consumption) === $opt ? 'selected' : '' }}>{{ $opt }}</option>
                        @endforeach
                    </select>
                    @error('alcohol_consumption') <p class="text-[11px] text-rose-500 mt-1 font-medium">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('patients.show', $patient) }}" class="btn-pill-secondary">
                Cancel
            </a>
            <button type="submit" class="btn-pill-primary">
                Update Patient Record
            </button>
        </div>
    </form>

</div>
@endsection

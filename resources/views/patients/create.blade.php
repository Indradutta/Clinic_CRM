@extends('layouts.app')

@section('title', 'Register New Patient')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-lg bg-brand-50 border border-brand-100/60 text-brand-700 text-xs font-semibold mb-2">
                <span class="w-1.5 h-1.5 rounded-full bg-brand-500"></span>
                Patient Intake
            </div>
            <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-slate-900">Register New Patient</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">Enter patient demographic details and clinical medical history.</p>
        </div>
        <a href="{{ route('patients.index') }}" class="btn-pill-secondary">
            &larr; Back to Directory
        </a>
    </div>

    <form method="POST" action="{{ route('patients.store') }}" class="space-y-6" novalidate>
        @csrf

        <!-- 1. Basic Information -->
        <div class="glass-card-elevated rounded-2xl p-6 sm:p-8 border border-white/80 shadow-[0_12px_40px_rgb(0,0,0,0.04)] space-y-6">
            <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider border-b border-slate-100 pb-3.5 flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-brand-500"></span>
                <span>1. Basic Demographic Information</span>
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                <!-- 1. Full Name -->
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Full Name *</label>
                    <input type="text" name="name" value="{{ old('name') }}" required placeholder="e.g. Robert Miller"
                           class="apple-input">
                    @error('name') <p class="text-[11px] text-rose-500 mt-1 font-medium">{{ $message }}</p> @enderror
                </div>

                <!-- 2. Date of Birth -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Date of Birth</label>
                    <input type="date" name="dob" value="{{ old('dob') }}" max="{{ now()->toDateString() }}"
                           class="apple-input">
                    @error('dob') <p class="text-[11px] text-rose-500 mt-1 font-medium">{{ $message }}</p> @enderror
                </div>

                <!-- 3. Age -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Age (Years)</label>
                    <input type="number" name="age" value="{{ old('age') }}" min="0" max="130" placeholder="e.g. 45"
                           class="apple-input">
                    @error('age') <p class="text-[11px] text-rose-500 mt-1 font-medium">{{ $message }}</p> @enderror
                </div>

                <!-- 4. Gender -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Gender *</label>
                    <select name="gender" required class="apple-input">
                        <option value="">Select Gender</option>
                        <option value="Male" {{ old('gender') === 'Male' ? 'selected' : '' }}>Male</option>
                        <option value="Female" {{ old('gender') === 'Female' ? 'selected' : '' }}>Female</option>
                        <option value="Other" {{ old('gender') === 'Other' ? 'selected' : '' }}>Other</option>
                    </select>
                    @error('gender') <p class="text-[11px] text-rose-500 mt-1 font-medium">{{ $message }}</p> @enderror
                </div>

                <!-- 5. Phone Number -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Phone Number *</label>
                    <input type="tel" name="phone" value="{{ old('phone') }}" required inputmode="numeric" minlength="10" maxlength="10" pattern="[6-9][0-9]{9}" placeholder="98765 43210" autocomplete="tel-national"
                           class="apple-input">
                    @error('phone') <p class="text-[11px] text-rose-500 mt-1 font-medium">{{ $message }}</p> @enderror
                </div>

                <!-- 6. Email -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Email Address</label>
                    <input type="email" name="email" value="{{ old('email') }}" placeholder="patient@example.com"
                           class="apple-input">
                    @error('email') <p class="text-[11px] text-rose-500 mt-1 font-medium">{{ $message }}</p> @enderror
                </div>

                <!-- 10. Blood Group -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Blood Group</label>
                    <select name="blood_group" class="apple-input">
                        <option value="">Unknown / Not Tested</option>
                        <option value="A+" {{ old('blood_group') === 'A+' ? 'selected' : '' }}>A+</option>
                        <option value="A-" {{ old('blood_group') === 'A-' ? 'selected' : '' }}>A-</option>
                        <option value="B+" {{ old('blood_group') === 'B+' ? 'selected' : '' }}>B+</option>
                        <option value="B-" {{ old('blood_group') === 'B-' ? 'selected' : '' }}>B-</option>
                        <option value="AB+" {{ old('blood_group') === 'AB+' ? 'selected' : '' }}>AB+</option>
                        <option value="AB-" {{ old('blood_group') === 'AB-' ? 'selected' : '' }}>AB-</option>
                        <option value="O+" {{ old('blood_group') === 'O+' ? 'selected' : '' }}>O+</option>
                        <option value="O-" {{ old('blood_group') === 'O-' ? 'selected' : '' }}>O-</option>
                    </select>
                    @error('blood_group') <p class="text-[11px] text-rose-500 mt-1 font-medium">{{ $message }}</p> @enderror
                </div>

                <!-- 11. Marital Status -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Marital Status</label>
                    <select name="marital_status" class="apple-input">
                        <option value="">Select Status</option>
                        <option value="Single" {{ old('marital_status') === 'Single' ? 'selected' : '' }}>Single</option>
                        <option value="Married" {{ old('marital_status') === 'Married' ? 'selected' : '' }}>Married</option>
                        <option value="Divorced" {{ old('marital_status') === 'Divorced' ? 'selected' : '' }}>Divorced</option>
                        <option value="Widowed" {{ old('marital_status') === 'Widowed' ? 'selected' : '' }}>Widowed</option>
                    </select>
                    @error('marital_status') <p class="text-[11px] text-rose-500 mt-1 font-medium">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Preferred Language</label>
                    <input type="text" name="preferred_language" value="{{ old('preferred_language') }}" class="apple-input" placeholder="e.g. English">
                    @error('preferred_language') <p class="text-[11px] text-rose-500 mt-1 font-medium">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Height (cm)</label>
                    <input type="number" name="height_cm" value="{{ old('height_cm') }}" min="0" max="300" step="0.1" class="apple-input">
                    @error('height_cm') <p class="text-[11px] text-rose-500 mt-1 font-medium">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Weight (kg)</label>
                    <input type="number" name="weight_kg" value="{{ old('weight_kg') }}" min="0" max="700" step="0.1" class="apple-input">
                    @error('weight_kg') <p class="text-[11px] text-rose-500 mt-1 font-medium">{{ $message }}</p> @enderror
                </div>

                <!-- 7. Address -->
                <div class="sm:col-span-2 lg:col-span-3">
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Residential Address</label>
                    <textarea name="address" rows="2" placeholder="Street, Apt/Suite, City, State, Pincode"
                              class="apple-input !rounded-2xl">{{ old('address') }}</textarea>
                    @error('address') <p class="text-[11px] text-rose-500 mt-1 font-medium">{{ $message }}</p> @enderror
                </div>
                <div class="sm:col-span-2 lg:col-span-3">
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Main Health Concern</label>
                    <textarea name="primary_concern" rows="2" placeholder="What brings the patient to the clinic?" class="apple-input !rounded-2xl">{{ old('primary_concern') }}</textarea>
                    @error('primary_concern') <p class="text-[11px] text-rose-500 mt-1 font-medium">{{ $message }}</p> @enderror
                </div>

                <!-- 8. Emergency Contact Name -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Emergency Contact Name</label>
                    <input type="text" name="emergency_contact_name" value="{{ old('emergency_contact_name') }}" placeholder="e.g. Spouse / Relative"
                           class="apple-input">
                    @error('emergency_contact_name') <p class="text-[11px] text-rose-500 mt-1 font-medium">{{ $message }}</p> @enderror
                </div>

                <!-- 9. Emergency Contact Phone -->
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Emergency Contact Phone</label>
                    <input type="tel" name="emergency_contact_phone" value="{{ old('emergency_contact_phone') }}" inputmode="numeric" minlength="10" maxlength="10" pattern="[6-9][0-9]{9}" placeholder="98765 00000" autocomplete="tel-national"
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
                <!-- 1. Known Allergies -->
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-rose-700 uppercase tracking-wider mb-1.5">
                        ⚠️ Known Allergies (Drug / Food / Environmental)
                    </label>
                    <textarea name="allergies" rows="2" placeholder="e.g. Penicillin (rash), Sulfa drugs, Peanuts, Latex"
                              class="apple-input !bg-rose-50/40 !border-rose-200 !rounded-2xl focus:!border-rose-400">{{ old('allergies') }}</textarea>
                    @error('allergies') <p class="text-[11px] text-rose-500 mt-1 font-medium">{{ $message }}</p> @enderror
                </div>

                <!-- 2. Chronic Conditions -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Chronic Medical Conditions</label>
                    <textarea name="chronic_conditions" rows="2" placeholder="e.g. Hypertension, Type 2 Diabetes, Asthma"
                              class="apple-input !rounded-2xl">{{ old('chronic_conditions') }}</textarea>
                    @error('chronic_conditions') <p class="text-[11px] text-rose-500 mt-1 font-medium">{{ $message }}</p> @enderror
                </div>

                <!-- 3. Current Medications -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Current Ongoing Medications</label>
                    <textarea name="current_medications" rows="2" placeholder="e.g. Metformin 500mg, Telmisartan 40mg"
                              class="apple-input !rounded-2xl">{{ old('current_medications') }}</textarea>
                    @error('current_medications') <p class="text-[11px] text-rose-500 mt-1 font-medium">{{ $message }}</p> @enderror
                </div>

                <!-- 4. Past Surgeries -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Past Surgeries / Procedures</label>
                    <textarea name="past_surgeries" rows="2" placeholder="e.g. Appendectomy (2018), Knee Arthroscopy (2022)"
                              class="apple-input !rounded-2xl">{{ old('past_surgeries') }}</textarea>
                    @error('past_surgeries') <p class="text-[11px] text-rose-500 mt-1 font-medium">{{ $message }}</p> @enderror
                </div>

                <!-- 5. Family Medical History -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Family Medical History</label>
                    <textarea name="family_history" rows="2" placeholder="e.g. Father had coronary artery disease at 55"
                              class="apple-input !rounded-2xl">{{ old('family_history') }}</textarea>
                    @error('family_history') <p class="text-[11px] text-rose-500 mt-1 font-medium">{{ $message }}</p> @enderror
                </div>

                <!-- 6. Smoking Habits -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Smoking Habits</label>
                    <select name="smoking_habits" class="apple-input">
                        <option value="">Select Option</option>
                        <option value="Non-smoker" {{ old('smoking_habits') === 'Non-smoker' ? 'selected' : '' }}>Non-smoker</option>
                        <option value="Former smoker" {{ old('smoking_habits') === 'Former smoker' ? 'selected' : '' }}>Former smoker</option>
                        <option value="Occasional smoker" {{ old('smoking_habits') === 'Occasional smoker' ? 'selected' : '' }}>Occasional smoker</option>
                        <option value="Regular smoker" {{ old('smoking_habits') === 'Regular smoker' ? 'selected' : '' }}>Regular smoker</option>
                    </select>
                    @error('smoking_habits') <p class="text-[11px] text-rose-500 mt-1 font-medium">{{ $message }}</p> @enderror
                </div>

                <!-- 7. Alcohol Consumption -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Alcohol Consumption</label>
                    <select name="alcohol_consumption" class="apple-input">
                        <option value="">Select Option</option>
                        <option value="Non-drinker" {{ old('alcohol_consumption') === 'Non-drinker' ? 'selected' : '' }}>Non-drinker</option>
                        <option value="Occasional" {{ old('alcohol_consumption') === 'Occasional' ? 'selected' : '' }}>Occasional</option>
                        <option value="Social" {{ old('alcohol_consumption') === 'Social' ? 'selected' : '' }}>Social</option>
                        <option value="Moderate" {{ old('alcohol_consumption') === 'Moderate' ? 'selected' : '' }}>Moderate</option>
                        <option value="Heavy" {{ old('alcohol_consumption') === 'Heavy' ? 'selected' : '' }}>Heavy</option>
                    </select>
                    @error('alcohol_consumption') <p class="text-[11px] text-rose-500 mt-1 font-medium">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('patients.index') }}" class="btn-pill-secondary">
                Cancel
            </a>
            <button type="submit" class="btn-pill-primary">
                Save & Open Patient Profile
            </button>
        </div>
    </form>

</div>
@endsection

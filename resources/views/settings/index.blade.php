@extends('layouts.app')

@section('title', 'Practice Settings')

@section('content')
<div class="space-y-8">
    
    <!-- Page Header -->
    <div>
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-lg bg-brand-50/80 border border-brand-100 text-brand-700 text-xs font-semibold mb-2">
            <span>System Preferences</span>
        </div>
        <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-slate-900">Practice Configuration & Settings</h1>
        <p class="text-xs sm:text-sm text-slate-500 mt-1">Customize clinician profile, clinic branding, scheduling constraints, templates, and security.</p>
    </div>

    <!-- Main Settings Container with Left Tab Navigation & Right Form Content -->
    <div class="flex flex-col lg:flex-row gap-6 sm:gap-8 items-start">
        
        <!-- Tab Navigation Sidebar (Apple-inspired list) -->
        <div class="w-full lg:w-72 glass-card rounded-2xl p-3 space-y-1.5 shrink-0">
            <a href="{{ route('settings.index', ['tab' => 'doctor']) }}" 
               class="flex items-center px-4 py-3 rounded-2xl text-xs font-bold transition {{ $activeTab === 'doctor' ? 'bg-brand-500 text-white shadow-md shadow-brand-500/25' : 'text-slate-600 hover:bg-slate-100/70 hover:text-slate-900' }}">
                <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
                Doctor Profile
            </a>

            <a href="{{ route('settings.index', ['tab' => 'clinic']) }}" 
               class="flex items-center px-4 py-3 rounded-2xl text-xs font-bold transition {{ $activeTab === 'clinic' ? 'bg-brand-500 text-white shadow-md shadow-brand-500/25' : 'text-slate-600 hover:bg-slate-100/70 hover:text-slate-900' }}">
                <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                </svg>
                Clinic Settings
            </a>

            <a href="{{ route('settings.index', ['tab' => 'appointment']) }}" 
               class="flex items-center px-4 py-3 rounded-2xl text-xs font-bold transition {{ $activeTab === 'appointment' ? 'bg-brand-500 text-white shadow-md shadow-brand-500/25' : 'text-slate-600 hover:bg-slate-100/70 hover:text-slate-900' }}">
                <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                Appointment Settings
            </a>

            <a href="{{ route('settings.index', ['tab' => 'prescription']) }}" 
               class="flex items-center px-4 py-3 rounded-2xl text-xs font-bold transition {{ $activeTab === 'prescription' ? 'bg-brand-500 text-white shadow-md shadow-brand-500/25' : 'text-slate-600 hover:bg-slate-100/70 hover:text-slate-900' }}">
                <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Prescription Settings
            </a>

            <a href="{{ route('settings.index', ['tab' => 'invoice']) }}" 
               class="flex items-center px-4 py-3 rounded-2xl text-xs font-bold transition {{ $activeTab === 'invoice' ? 'bg-brand-500 text-white shadow-md shadow-brand-500/25' : 'text-slate-600 hover:bg-slate-100/70 hover:text-slate-900' }}">
                <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"></path>
                </svg>
                Invoice Settings
            </a>

            <a href="{{ route('settings.index', ['tab' => 'notification']) }}" 
               class="flex items-center px-4 py-3 rounded-2xl text-xs font-bold transition {{ $activeTab === 'notification' ? 'bg-brand-500 text-white shadow-md shadow-brand-500/25' : 'text-slate-600 hover:bg-slate-100/70 hover:text-slate-900' }}">
                <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                </svg>
                Notification Settings
            </a>

            <a href="{{ route('settings.index', ['tab' => 'security']) }}" 
               class="flex items-center px-4 py-3 rounded-2xl text-xs font-bold transition {{ $activeTab === 'security' ? 'bg-brand-500 text-white shadow-md shadow-brand-500/25' : 'text-slate-600 hover:bg-slate-100/70 hover:text-slate-900' }}">
                <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                </svg>
                Security & Sessions
            </a>
        </div>

        <!-- Right Content Area -->
        <div class="flex-1 w-full">
            
            <!-- 1. Doctor Profile Tab -->
            @if($activeTab === 'doctor')
                <div class="glass-card rounded-2xl p-6 sm:p-8 space-y-6">
                    <div class="border-b border-slate-100 pb-4">
                        <h2 class="text-base font-bold text-slate-900">Doctor Profile Information</h2>
                        <p class="text-xs text-slate-500 mt-1">Primary physician credentials appearing on prescriptions and official medical records.</p>
                    </div>

                    <form method="POST" action="{{ route('settings.update', ['group' => 'doctor']) }}" enctype="multipart/form-data" class="space-y-6" novalidate>
                        @csrf
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-5">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Doctor Name *</label>
                                <input type="text" name="doctor_name" value="{{ $settings['doctor_name'] ?? '' }}" required
                                       class="apple-input w-full">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Registration Number *</label>
                                <input type="text" name="doctor_registration_number" value="{{ $settings['doctor_registration_number'] ?? '' }}" required
                                       class="apple-input w-full">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Qualification *</label>
                                <input type="text" name="doctor_qualification" value="{{ $settings['doctor_qualification'] ?? '' }}" required
                                       class="apple-input w-full">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Specialization *</label>
                                <input type="text" name="doctor_specialization" value="{{ $settings['doctor_specialization'] ?? '' }}" required
                                       class="apple-input w-full">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Phone Number *</label>
                                <input type="tel" name="doctor_phone" value="{{ $settings['doctor_phone'] ?? '' }}" required inputmode="numeric" minlength="10" maxlength="10" pattern="[6-9][0-9]{9}" placeholder="98765 43210"
                                       class="apple-input w-full">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Email Address *</label>
                                <input type="email" name="doctor_email" value="{{ $settings['doctor_email'] ?? '' }}" required
                                       class="apple-input w-full">
                            </div>

                            <div class="sm:col-span-2">
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Clinic Name</label>
                                <input type="text" name="doctor_clinic_name" value="{{ $settings['doctor_clinic_name'] ?? '' }}"
                                       class="apple-input w-full">
                            </div>

                            <div class="sm:col-span-2">
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Clinic Address</label>
                                <textarea name="doctor_clinic_address" rows="2"
                                          class="apple-input w-full leading-relaxed">{{ $settings['doctor_clinic_address'] ?? '' }}</textarea>
                            </div>

                            <div class="sm:col-span-2">
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Profile Photo</label>
                                <input type="file" name="doctor_profile_photo" accept="image/*"
                                       class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100">
                                @if(!empty($settings['doctor_profile_photo']))
                                    <div class="mt-3 flex items-center gap-3">
                                        <img src="{{ $settings['doctor_profile_photo'] }}" alt="Doctor Photo" class="w-12 h-12 rounded-2xl object-cover border border-slate-200 shadow-sm">
                                        <span class="text-[11px] text-slate-400 font-medium">Current profile photo</span>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="flex justify-end pt-4">
                            @can('settings.edit')
                            <button type="submit" class="btn-pill-primary px-7">
                                Save Doctor Profile
                            </button>
                            @endcan
                        </div>
                    </form>
                </div>
            @endif

            <!-- 2. Clinic Settings Tab -->
            @if($activeTab === 'clinic')
                <div class="glass-card rounded-2xl p-6 sm:p-8 space-y-6">
                    <div class="border-b border-slate-100 pb-4">
                        <h2 class="text-base font-bold text-slate-900">Clinic & Practice Settings</h2>
                        <p class="text-xs text-slate-500 mt-1">Configure facility information, working hours, and operational currency.</p>
                    </div>

                    <form method="POST" action="{{ route('settings.update', ['group' => 'clinic']) }}" enctype="multipart/form-data" class="space-y-6" novalidate>
                        @csrf
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-5">
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Clinic Name *</label>
                                <input type="text" name="clinic_name" value="{{ $settings['clinic_name'] ?? '' }}" required
                                       class="apple-input w-full">
                            </div>

                            <div class="sm:col-span-2">
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Physical Address *</label>
                                <textarea name="clinic_address" rows="2" required
                                          class="apple-input w-full leading-relaxed">{{ $settings['clinic_address'] ?? '' }}</textarea>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Clinic Contact Phone *</label>
                                <input type="tel" name="clinic_phone" value="{{ $settings['clinic_phone'] ?? '' }}" required inputmode="numeric" minlength="10" maxlength="10" pattern="[6-9][0-9]{9}" placeholder="98765 43210"
                                       class="apple-input w-full">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Clinic Contact Email *</label>
                                <input type="email" name="clinic_email" value="{{ $settings['clinic_email'] ?? '' }}" required
                                       class="apple-input w-full">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Official Website</label>
                                <input type="url" name="clinic_website" value="{{ $settings['clinic_website'] ?? '' }}"
                                       class="apple-input w-full">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Working Hours Text</label>
                                <input type="text" name="clinic_working_hours" value="{{ $settings['clinic_working_hours'] ?? '' }}"
                                       class="apple-input w-full">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Standard Consultation Fee *</label>
                                <input type="number" step="0.01" name="clinic_consultation_fee" value="{{ $settings['clinic_consultation_fee'] ?? '500' }}" required
                                       class="apple-input w-full">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Currency Symbol *</label>
                                <input type="text" name="clinic_currency" value="{{ $settings['clinic_currency'] ?? '₹' }}" required
                                       class="apple-input w-full">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Time Zone *</label>
                                <input type="text" name="clinic_time_zone" value="{{ $settings['clinic_time_zone'] ?? 'Asia/Kolkata' }}" required
                                       class="apple-input w-full">
                            </div>


                        </div>

                        <div class="flex justify-end pt-4">
                            @can('settings.edit')
                            <button type="submit" class="btn-pill-primary px-7">
                                Save Clinic Settings
                            </button>
                            @endcan
                        </div>
                    </form>
                </div>
            @endif

            <!-- 3. Appointment Settings Tab -->
            @if($activeTab === 'appointment')
                <div class="glass-card rounded-2xl p-6 sm:p-8 space-y-6">
                    <div class="border-b border-slate-100 pb-4">
                        <h2 class="text-base font-bold text-slate-900">Appointment Scheduling Constraints</h2>
                        <p class="text-xs text-slate-500 mt-1">Control slot durations, operating days, daily limits, and cancellation policies.</p>
                    </div>

                    <form method="POST" action="{{ route('settings.update', ['group' => 'appointment']) }}" class="space-y-6" novalidate>
                        @csrf
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-5">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Appointment Duration (Minutes) *</label>
                                <select name="appointment_duration" class="apple-input w-full">
                                    <option value="10" {{ ($settings['appointment_duration'] ?? '15') == '10' ? 'selected' : '' }}>10 Minutes</option>
                                    <option value="15" {{ ($settings['appointment_duration'] ?? '15') == '15' ? 'selected' : '' }}>15 Minutes (Default)</option>
                                    <option value="20" {{ ($settings['appointment_duration'] ?? '15') == '20' ? 'selected' : '' }}>20 Minutes</option>
                                    <option value="30" {{ ($settings['appointment_duration'] ?? '15') == '30' ? 'selected' : '' }}>30 Minutes</option>
                                    <option value="45" {{ ($settings['appointment_duration'] ?? '15') == '45' ? 'selected' : '' }}>45 Minutes</option>
                                    <option value="60" {{ ($settings['appointment_duration'] ?? '15') == '60' ? 'selected' : '' }}>60 Minutes</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Maximum Appointments Per Day *</label>
                                <input type="number" name="appointment_max_per_day" value="{{ $settings['appointment_max_per_day'] ?? '30' }}" required
                                       class="apple-input w-full">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Working Hours Start *</label>
                                <input type="time" name="appointment_working_hours_start" value="{{ $settings['appointment_working_hours_start'] ?? '09:00' }}" required
                                       class="apple-input w-full">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Working Hours End *</label>
                                <input type="time" name="appointment_working_hours_end" value="{{ $settings['appointment_working_hours_end'] ?? '19:00' }}" required
                                       class="apple-input w-full">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Break Time Start</label>
                                <input type="time" name="appointment_break_time_start" value="{{ $settings['appointment_break_time_start'] ?? '13:00' }}"
                                       class="apple-input w-full">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Break Time End</label>
                                <input type="time" name="appointment_break_time_end" value="{{ $settings['appointment_break_time_end'] ?? '14:00' }}"
                                       class="apple-input w-full">
                            </div>

                            <!-- Working Days -->
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Operational Working Days *</label>
                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
                                    @php
                                        $allDays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                                        $selectedDays = $settings['appointment_working_days'] ?? [];
                                    @endphp
                                    @foreach($allDays as $day)
                                        <label class="flex items-center gap-2.5 p-2.5 rounded-2xl glass-card text-xs font-medium text-slate-700 cursor-pointer hover:border-brand-300 transition">
                                            <input type="checkbox" name="appointment_working_days[]" value="{{ $day }}" 
                                                   class="w-4 h-4 text-brand-600 rounded-lg border-slate-300 focus:ring-brand-500"
                                                   {{ in_array($day, $selectedDays) ? 'checked' : '' }}>
                                            <span>{{ $day }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Cancellation Rules -->
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Cancellation Rules & Policy</label>
                                <textarea name="appointment_cancellation_rules" rows="2"
                                          class="apple-input w-full leading-relaxed">{{ $settings['appointment_cancellation_rules'] ?? '' }}</textarea>
                            </div>

                            <!-- Appointment Reminders -->
                            <div class="sm:col-span-2">
                                <label class="flex items-center gap-3 cursor-pointer">
                                    <input type="checkbox" name="appointment_reminders" value="1" 
                                           class="w-4 h-4 text-brand-600 rounded-lg border-slate-300 focus:ring-brand-500"
                                           {{ ($settings['appointment_reminders'] ?? '1') == '1' ? 'checked' : '' }}>
                                    <span class="text-xs font-bold text-slate-800">Enable Automated Appointment Reminders</span>
                                </label>
                            </div>
                        </div>

                        <div class="flex justify-end pt-4">
                            @can('settings.edit')
                            <button type="submit" class="btn-pill-primary px-7">
                                Save Appointment Settings
                            </button>
                            @endcan
                        </div>
                    </form>
                </div>
            @endif

            <!-- 4. Prescription Settings Tab -->
            @if($activeTab === 'prescription')
                <div class="glass-card rounded-2xl p-6 sm:p-8 space-y-6">
                    <div class="border-b border-slate-100 pb-4">
                        <h2 class="text-base font-bold text-slate-900">Digital Prescription Configuration</h2>
                        <p class="text-xs text-slate-500 mt-1">Customize prescription header layout, doctor credentials, signature, and default patient advice.</p>
                    </div>

                    <form method="POST" action="{{ route('settings.update', ['group' => 'prescription']) }}" enctype="multipart/form-data" class="space-y-6" novalidate>
                        @csrf
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-5">
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Prescription Header Title *</label>
                                <input type="text" name="prescription_header" value="{{ $settings['prescription_header'] ?? '' }}" required
                                       class="apple-input w-full">
                            </div>

                            <div class="sm:col-span-2">
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Doctor Information Block *</label>
                                <input type="text" name="prescription_doctor_info" value="{{ $settings['prescription_doctor_info'] ?? '' }}" required
                                       class="apple-input w-full">
                            </div>

                            <div class="sm:col-span-2">
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Prescription Footer Note</label>
                                <textarea name="prescription_footer" rows="2"
                                          class="apple-input w-full leading-relaxed">{{ $settings['prescription_footer'] ?? '' }}</textarea>
                            </div>

                            <div class="sm:col-span-2">
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Default Medication Instructions</label>
                                <textarea name="prescription_default_instructions" rows="2"
                                          class="apple-input w-full leading-relaxed">{{ $settings['prescription_default_instructions'] ?? '' }}</textarea>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Prescription Logo (Optional)</label>
                                <input type="file" name="prescription_clinic_logo" accept="image/*"
                                       class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100">
                                @if(!empty($settings['prescription_clinic_logo']))
                                    <div class="mt-3 flex items-center gap-3">
                                        <img src="{{ $settings['prescription_clinic_logo'] }}" alt="Logo" class="h-8 object-contain">
                                    </div>
                                @endif
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Doctor Digital Signature</label>
                                <input type="file" name="prescription_signature" accept="image/*"
                                       class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100">
                                @if(!empty($settings['prescription_signature']))
                                    <div class="mt-3 flex items-center gap-3">
                                        <img src="{{ $settings['prescription_signature'] }}" alt="Signature" class="h-8 object-contain">
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="flex justify-end pt-4">
                            @can('settings.edit')
                            <button type="submit" class="btn-pill-primary px-7">
                                Save Prescription Settings
                            </button>
                            @endcan
                        </div>
                    </form>
                </div>
            @endif

            <!-- 5. Invoice Settings Tab -->
            @if($activeTab === 'invoice')
                <div class="glass-card rounded-2xl p-6 sm:p-8 space-y-6">
                    <div class="border-b border-slate-100 pb-4">
                        <h2 class="text-base font-bold text-slate-900">Billing & Invoice Configuration</h2>
                        <p class="text-xs text-slate-500 mt-1">Configure numbering sequences, tax parameters, business details, and payment terms.</p>
                    </div>

                    <form method="POST" action="{{ route('settings.update', ['group' => 'invoice']) }}" class="space-y-6" novalidate>
                        @csrf
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-5">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Invoice Prefix *</label>
                                <input type="text" name="invoice_prefix" value="{{ $settings['invoice_prefix'] ?? 'INV-' }}" required
                                       class="apple-input w-full">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Invoice Numbering Start *</label>
                                <input type="number" name="invoice_numbering" value="{{ $settings['invoice_numbering'] ?? '1001' }}" required
                                       class="apple-input w-full">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Default Tax Rate (%)</label>
                                <input type="number" step="0.01" name="invoice_tax_settings" value="{{ $settings['invoice_tax_settings'] ?? '18' }}"
                                       class="apple-input w-full">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Business Registration / Tax ID</label>
                                <input type="text" name="invoice_business_information" value="{{ $settings['invoice_business_information'] ?? '' }}"
                                       class="apple-input w-full">
                            </div>

                            <div class="sm:col-span-2">
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Payment Terms</label>
                                <textarea name="invoice_payment_terms" rows="2"
                                          class="apple-input w-full leading-relaxed">{{ $settings['invoice_payment_terms'] ?? '' }}</textarea>
                            </div>

                            <div class="sm:col-span-2">
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Invoice Footer Note</label>
                                <textarea name="invoice_footer" rows="2"
                                          class="apple-input w-full leading-relaxed">{{ $settings['invoice_footer'] ?? '' }}</textarea>
                            </div>
                        </div>

                        <div class="flex justify-end pt-4">
                            @can('settings.edit')
                            <button type="submit" class="btn-pill-primary px-7">
                                Save Invoice Settings
                            </button>
                            @endcan
                        </div>
                    </form>
                </div>
            @endif

            <!-- 6. Notification Settings Tab -->
            @if($activeTab === 'notification')
                <div class="glass-card rounded-2xl p-6 sm:p-8 space-y-6">
                    <div class="border-b border-slate-100 pb-4">
                        <h2 class="text-base font-bold text-slate-900">Automated Notification Triggers</h2>
                        <p class="text-xs text-slate-500 mt-1">Toggle alert events for appointments, invoices, and payments.</p>
                    </div>

                    <form method="POST" action="{{ route('settings.update', ['group' => 'notification']) }}" class="space-y-4" novalidate>
                        @csrf
                        <div class="divide-y divide-slate-100">
                            
                            <label class="flex items-center justify-between py-4 cursor-pointer">
                                <div>
                                    <span class="text-xs font-bold text-slate-800 block">New Appointments Alert</span>
                                    <span class="text-[11px] text-slate-400">Trigger notification whenever a new appointment booking is created.</span>
                                </div>
                                <input type="checkbox" name="notify_new_appointment" value="1" 
                                       class="w-4 h-4 text-brand-600 rounded-lg border-slate-300 focus:ring-brand-500"
                                       {{ ($settings['notify_new_appointment'] ?? '1') == '1' ? 'checked' : '' }}>
                            </label>

                            <label class="flex items-center justify-between py-4 cursor-pointer">
                                <div>
                                    <span class="text-xs font-bold text-slate-800 block">Appointment Confirmation Alert</span>
                                    <span class="text-[11px] text-slate-400">Notify doctor and patient when appointment status is confirmed.</span>
                                </div>
                                <input type="checkbox" name="notify_appointment_confirmation" value="1" 
                                       class="w-4 h-4 text-brand-600 rounded-lg border-slate-300 focus:ring-brand-500"
                                       {{ ($settings['notify_appointment_confirmation'] ?? '1') == '1' ? 'checked' : '' }}>
                            </label>

                            <label class="flex items-center justify-between py-4 cursor-pointer">
                                <div>
                                    <span class="text-xs font-bold text-slate-800 block">Appointment Cancellation Alert</span>
                                    <span class="text-[11px] text-slate-400">Send notification if an appointment slot is cancelled.</span>
                                </div>
                                <input type="checkbox" name="notify_appointment_cancellation" value="1" 
                                       class="w-4 h-4 text-brand-600 rounded-lg border-slate-300 focus:ring-brand-500"
                                       {{ ($settings['notify_appointment_cancellation'] ?? '1') == '1' ? 'checked' : '' }}>
                            </label>

                            <label class="flex items-center justify-between py-4 cursor-pointer">
                                <div>
                                    <span class="text-xs font-bold text-slate-800 block">Appointment Reminders</span>
                                    <span class="text-[11px] text-slate-400">Dispatch reminder alerts prior to upcoming scheduled visits.</span>
                                </div>
                                <input type="checkbox" name="notify_appointment_reminders" value="1" 
                                       class="w-4 h-4 text-brand-600 rounded-lg border-slate-300 focus:ring-brand-500"
                                       {{ ($settings['notify_appointment_reminders'] ?? '1') == '1' ? 'checked' : '' }}>
                            </label>

                            <label class="flex items-center justify-between py-4 cursor-pointer">
                                <div>
                                    <span class="text-xs font-bold text-slate-800 block">Payment Received Alert</span>
                                    <span class="text-[11px] text-slate-400">Notify accountant and clinic management when patient payment is recorded.</span>
                                </div>
                                <input type="checkbox" name="notify_payment_received" value="1" 
                                       class="w-4 h-4 text-brand-600 rounded-lg border-slate-300 focus:ring-brand-500"
                                       {{ ($settings['notify_payment_received'] ?? '1') == '1' ? 'checked' : '' }}>
                            </label>

                            <label class="flex items-center justify-between py-4 cursor-pointer">
                                <div>
                                    <span class="text-xs font-bold text-slate-800 block">Invoice Due Alert</span>
                                    <span class="text-[11px] text-slate-400">Generate alert for outstanding unpaid patient invoices.</span>
                                </div>
                                <input type="checkbox" name="notify_invoice_due" value="1" 
                                       class="w-4 h-4 text-brand-600 rounded-lg border-slate-300 focus:ring-brand-500"
                                       {{ ($settings['notify_invoice_due'] ?? '1') == '1' ? 'checked' : '' }}>
                            </label>

                        </div>

                        <div class="flex justify-end pt-4">
                            @can('settings.edit')
                            <button type="submit" class="btn-pill-primary px-7">
                                Save Notification Preferences
                            </button>
                            @endcan
                        </div>
                    </form>
                </div>
            @endif

            <!-- 7. Security Settings Tab -->
            @if($activeTab === 'security')
                <div class="space-y-6 sm:space-y-8">
                    
                    <!-- Change Password -->
                    <div class="glass-card rounded-2xl p-6 sm:p-8 space-y-6">
                        <div class="border-b border-slate-100 pb-4">
                            <h2 class="text-base font-bold text-slate-900">Change Account Password</h2>
                            <p class="text-xs text-slate-500 mt-1">Ensure your practice management account uses a strong, secure passphrase.</p>
                        </div>

                        @can('settings.edit')
                        <form method="POST" action="{{ route('settings.password') }}" class="space-y-4 max-w-md" novalidate>
                            @csrf
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Current Password *</label>
                                <input type="password" name="current_password" required placeholder="••••••••"
                                       class="apple-input w-full">
                                @error('current_password') <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">New Password *</label>
                                <input type="password" name="password" required minlength="10" autocomplete="new-password" placeholder="10+ characters, upper/lowercase, number, symbol"
                                       class="apple-input w-full">
                                @error('password') <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Confirm New Password *</label>
                                <input type="password" name="password_confirmation" required placeholder="••••••••"
                                       class="apple-input w-full">
                            </div>

                            <div class="pt-3">
                                <button type="submit" class="btn-pill-primary px-7">
                                    Update Password
                                </button>
                            </div>
                        </form>
                        @endcan
                    </div>

                    <!-- Active Login Sessions -->
                    <div class="glass-card rounded-2xl p-6 sm:p-8 space-y-6">
                        <div class="border-b border-slate-100 pb-4 flex items-center justify-between">
                            <div>
                                <h2 class="text-base font-bold text-slate-900">Active Login Sessions</h2>
                                <p class="text-xs text-slate-500 mt-1">Manage and revoke your active sign-in sessions across devices.</p>
                            </div>
                            <span class="text-[11px] font-bold text-slate-500 bg-slate-100/80 px-3 py-1 rounded-md">
                                {{ count($sessions) }} Session{{ count($sessions) === 1 ? '' : 's' }}
                            </span>
                        </div>

                        <div class="divide-y divide-slate-100">
                            @forelse($sessions as $sessionRecord)
                                <div class="py-4 flex items-center justify-between">
                                    <div class="flex items-center gap-3.5">
                                        <div class="w-10 h-10 rounded-2xl bg-slate-100 text-slate-600 flex items-center justify-center shrink-0">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                            </svg>
                                        </div>
                                        <div>
                                            <div class="flex items-center gap-2">
                                                <p class="text-xs font-bold text-slate-900 truncate max-w-xs">
                                                    {{ $sessionRecord->ip_address ?? '127.0.0.1' }}
                                                </p>
                                                @if($sessionRecord->id === session()->getId())
                                                    <span class="px-2.5 py-0.5 rounded-md text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                        This Device
                                                    </span>
                                                @endif
                                            </div>
                                            <p class="text-[11px] text-slate-400 mt-0.5 truncate max-w-sm">
                                                {{ $sessionRecord->user_agent ?? 'Browser Session' }}
                                            </p>
                                            <span class="text-[10px] text-slate-400 block mt-0.5 font-medium">
                                                Last active: {{ \Carbon\Carbon::createFromTimestamp($sessionRecord->last_activity)->diffForHumans() }}
                                            </span>
                                        </div>
                                    </div>

                                    @if($sessionRecord->id !== session()->getId())
                                        @can('settings.edit')
                                        <form action="{{ route('settings.session.destroy', $sessionRecord->id) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-xs text-rose-600 hover:text-rose-700 bg-rose-50 hover:bg-rose-100 px-3.5 py-1.5 rounded-lg font-bold transition">
                                                Revoke
                                            </button>
                                        </form>
                                        @endcan
                                    @endif
                                </div>
                            @empty
                                <p class="text-xs text-slate-400 py-6 text-center">No active sessions found.</p>
                            @endforelse
                        </div>
                    </div>

                </div>
            @endif

        </div>
    </div>

</div>
@endsection




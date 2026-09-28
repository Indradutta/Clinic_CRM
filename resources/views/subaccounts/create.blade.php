@extends('layouts.app')

@section('title', 'Create Staff Subaccount')

@section('content')
<div class="max-w-4xl mx-auto space-y-8">
    
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-lg bg-brand-50/80 border border-brand-100 text-brand-700 text-xs font-semibold mb-2">
                <span>Team Access</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-slate-900">Create Staff Subaccount</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">Register a new staff member and configure granular module permissions.</p>
        </div>
        <a href="{{ route('subaccounts.index') }}" class="btn-pill-secondary self-start sm:self-auto inline-flex items-center gap-2">
            &larr; <span>Back to Directory</span>
        </a>
    </div>

    <!-- Form Container -->
    <form method="POST" action="{{ route('subaccounts.store') }}" class="space-y-8" novalidate>
        @csrf

        <!-- 1. Account Credentials & Information -->
        <div class="glass-card-elevated rounded-2xl p-6 sm:p-8 space-y-6">
            <div class="flex items-center gap-3 border-b border-slate-100 pb-4">
                <div class="w-9 h-9 rounded-xl bg-brand-50 text-brand-600 flex items-center justify-center font-bold">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                </div>
                <div>
                    <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider">1. Staff Member Information</h2>
                    <p class="text-xs text-slate-400">Credentials and primary practice role</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-5">
                <!-- Name -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Full Name *</label>
                    <input type="text" name="name" value="{{ old('name') }}" required placeholder="e.g. Dr. Jane Smith or Mark Johnson"
                           class="apple-input w-full">
                    @error('name') <p class="text-[11px] text-rose-500 mt-1 font-semibold">{{ $message }}</p> @enderror
                </div>

                <!-- Username -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Username *</label>
                    <input type="text" name="username" value="{{ old('username') }}" required placeholder="e.g. janesmith"
                           class="apple-input w-full font-mono">
                    @error('username') <p class="text-[11px] text-rose-500 mt-1 font-semibold">{{ $message }}</p> @enderror
                </div>

                <!-- Email -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Email Address *</label>
                    <input type="email" name="email" value="{{ old('email') }}" required placeholder="e.g. staff@clinic.com"
                           class="apple-input w-full">
                    @error('email') <p class="text-[11px] text-rose-500 mt-1 font-semibold">{{ $message }}</p> @enderror
                </div>

                <!-- Phone -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Phone Number</label>
                    <input type="tel" name="phone" value="{{ old('phone') }}" inputmode="numeric" minlength="10" maxlength="10" pattern="[6-9][0-9]{9}" placeholder="98765 43210"
                           class="apple-input w-full font-mono">
                    @error('phone') <p class="text-[11px] text-rose-500 mt-1 font-semibold">{{ $message }}</p> @enderror
                </div>

                <!-- Password -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Temporary Password *</label>
                    <input type="password" name="password" required minlength="10" autocomplete="new-password" placeholder="10+ characters, upper/lowercase, number, symbol"
                           class="apple-input w-full">
                    @error('password') <p class="text-[11px] text-rose-500 mt-1 font-semibold">{{ $message }}</p> @enderror
                </div>

                <!-- Role -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Staff Role *</label>
                    <select name="role" required class="apple-input w-full">
                        <option value="receptionist" {{ old('role') === 'receptionist' ? 'selected' : '' }}>Receptionist</option>
                        <option value="nurse" {{ old('role') === 'nurse' ? 'selected' : '' }}>Nurse</option>
                        <option value="assistant" {{ old('role') === 'assistant' ? 'selected' : '' }}>Assistant</option>
                        <option value="clinic_manager" {{ old('role') === 'clinic_manager' ? 'selected' : '' }}>Clinic Manager</option>
                        <option value="accountant" {{ old('role') === 'accountant' ? 'selected' : '' }}>Accountant</option>
                        <option value="other_staff" {{ old('role') === 'other_staff' ? 'selected' : '' }}>Other Staff</option>
                        <option value="admin_doctor" {{ old('role') === 'admin_doctor' ? 'selected' : '' }}>Admin / Doctor</option>
                        <option value="doctor" {{ old('role') === 'doctor' ? 'selected' : '' }}>Doctor</option>
                    </select>
                    @error('role') <p class="text-[11px] text-rose-500 mt-1 font-semibold">{{ $message }}</p> @enderror
                </div>

                <!-- Status -->
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Account Status *</label>
                    <div class="flex items-center gap-6 mt-1">
                        <label class="inline-flex items-center gap-2.5 text-xs font-bold text-slate-700 cursor-pointer">
                            <input type="radio" name="status" value="active" {{ old('status', 'active') === 'active' ? 'checked' : '' }} class="text-brand-600 focus:ring-brand-500 w-4 h-4">
                            <span>Active (Allowed to sign in)</span>
                        </label>
                        <label class="inline-flex items-center gap-2.5 text-xs font-bold text-slate-700 cursor-pointer">
                            <input type="radio" name="status" value="inactive" {{ old('status') === 'inactive' ? 'checked' : '' }} class="text-brand-600 focus:ring-brand-500 w-4 h-4">
                            <span>Inactive (Suspended access)</span>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. Configurable Permissions Matrix -->
        <div class="glass-card-elevated rounded-2xl p-6 sm:p-8 space-y-6">
            <div class="border-b border-slate-100 pb-4 flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                        <svg class="w-4 h-4 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                        </svg>
                        <span>Configurable Permissions Matrix</span>
                    </h2>
                    <p class="text-xs text-slate-400 mt-1">Sensitive permissions (destructive actions & practice settings) are restricted by default.</p>
                </div>
            </div>

            <!-- Matrix grouped by module -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-1">
                @foreach($permissions as $module => $modulePermissions)
                    <div class="p-5 rounded-2xl glass-card space-y-3">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-2.5">
                            <span class="text-xs font-bold text-slate-900 uppercase tracking-wider">{{ $module }}</span>
                            <span class="text-[10px] text-slate-400 font-semibold">{{ count($modulePermissions) }} actions</span>
                        </div>
                        <div class="space-y-2.5">
                            @foreach($modulePermissions as $perm)
                                <label class="flex items-start gap-2.5 text-xs text-slate-700 cursor-pointer select-none">
                                    <input type="checkbox" 
                                           name="permissions[]" 
                                           value="{{ $perm->id }}" 
                                           class="w-4 h-4 text-brand-600 rounded-lg border-slate-300 focus:ring-brand-500 mt-0.5"
                                           {{ in_array($perm->id, old('permissions', [])) ? 'checked' : '' }}>
                                    <div class="flex-1">
                                        <span class="font-semibold {{ $perm->is_sensitive ? 'text-amber-800' : 'text-slate-800' }}">{{ $perm->label }}</span>
                                        @if($perm->is_sensitive)
                                            <span class="inline-block px-2 py-0.5 rounded-md text-[9px] font-bold bg-amber-100 text-amber-800 ml-1">Sensitive</span>
                                        @endif
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Submit Button -->
        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('subaccounts.index') }}" class="btn-pill-secondary">
                Cancel
            </a>
            <button type="submit" class="btn-pill-primary px-8">
                Create Subaccount
            </button>
        </div>
    </form>

</div>
@endsection


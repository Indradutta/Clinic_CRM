@extends('layouts.app')

@section('title', 'Patient Directory')

@section('content')
<div class="space-y-6">
    
    <!-- Page Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-lg bg-brand-50 border border-brand-100/60 text-brand-700 text-xs font-semibold mb-2">
                <span class="w-1.5 h-1.5 rounded-full bg-brand-500"></span>
                Patient Registry
            </div>
            <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-slate-900">Patient Directory</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">Central repository for all registered clinic patients and clinical profiles.</p>
        </div>
        @can('patients.create')
        <a href="{{ route('patients.create') }}" 
           class="btn-pill-primary">
            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path>
            </svg>
            <span>Register New Patient</span>
        </a>
        @endcan
    </div>

    <!-- Search & Filters Container -->
    <div class="glass-card-elevated rounded-2xl p-5 sm:p-6 border border-white/80 shadow-[0_8px_30px_rgb(0,0,0,0.04)] space-y-4">
        <form method="GET" action="{{ route('patients.index') }}" class="space-y-3.5">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5">
                
                <!-- Search by: Patient ID, Name, Phone, Email -->
                <div class="lg:col-span-2">
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Search Patients</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                        <input type="text" 
                               name="search" 
                               value="{{ request('search') }}" 
                               placeholder="Search Patient ID (e.g. PAT-00101), Name, Phone, Email..." 
                               class="apple-input !pl-10 !py-2.5">
                    </div>
                </div>

                <!-- Filter by: Gender -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Gender</label>
                    <select name="gender" class="apple-input !py-2.5">
                        <option value="">All Genders</option>
                        <option value="Male" {{ request('gender') === 'Male' ? 'selected' : '' }}>Male</option>
                        <option value="Female" {{ request('gender') === 'Female' ? 'selected' : '' }}>Female</option>
                        <option value="Other" {{ request('gender') === 'Other' ? 'selected' : '' }}>Other</option>
                    </select>
                </div>

                <!-- Filter by: Blood Group -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Blood Group</label>
                    <select name="blood_group" class="apple-input !py-2.5">
                        <option value="">All Blood Groups</option>
                        <option value="A+" {{ request('blood_group') === 'A+' ? 'selected' : '' }}>A+</option>
                        <option value="A-" {{ request('blood_group') === 'A-' ? 'selected' : '' }}>A-</option>
                        <option value="B+" {{ request('blood_group') === 'B+' ? 'selected' : '' }}>B+</option>
                        <option value="B-" {{ request('blood_group') === 'B-' ? 'selected' : '' }}>B-</option>
                        <option value="AB+" {{ request('blood_group') === 'AB+' ? 'selected' : '' }}>AB+</option>
                        <option value="AB-" {{ request('blood_group') === 'AB-' ? 'selected' : '' }}>AB-</option>
                        <option value="O+" {{ request('blood_group') === 'O+' ? 'selected' : '' }}>O+</option>
                        <option value="O-" {{ request('blood_group') === 'O-' ? 'selected' : '' }}>O-</option>
                    </select>
                </div>
            </div>

            <!-- Additional Filters Row: Age range & Registration Date -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5 pt-1">
                <div>
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Min Age</label>
                    <input type="number" name="min_age" value="{{ request('min_age') }}" placeholder="Min Age (e.g. 18)"
                           class="apple-input !py-2.5">
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Max Age</label>
                    <input type="number" name="max_age" value="{{ request('max_age') }}" placeholder="Max Age (e.g. 65)"
                           class="apple-input !py-2.5">
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Registered From</label>
                    <input type="date" name="from_date" value="{{ request('from_date') }}"
                           class="apple-input !py-2.5">
                </div>

                <div class="flex items-end gap-2">
                    <div class="flex-1">
                        <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Registered To</label>
                        <input type="date" name="to_date" value="{{ request('to_date') }}"
                               class="apple-input !py-2.5">
                    </div>
                    <button type="submit" class="btn-pill-primary !py-2.5 !px-4">
                        Apply
                    </button>
                    <a href="{{ route('patients.index') }}" class="btn-pill-secondary !py-2.5 !px-3.5">
                        Reset
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- Patients List: Dual View -->
    
    <!-- Mobile Card View -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 lg:hidden">
        @forelse($patients as $patient)
            <div class="glass-card rounded-2xl p-5 space-y-3.5">
                <div class="flex items-start justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-brand-500 text-white flex items-center justify-center font-bold text-xs shadow-xs">
                            {{ strtoupper(substr($patient->name, 0, 2)) }}
                        </div>
                        <div>
                            <span class="font-mono text-[10px] font-bold text-brand-600 bg-brand-50 px-2.5 py-0.5 rounded-md border border-brand-100/60">
                                {{ $patient->patient_id }}
                            </span>
                            <h3 class="text-sm font-bold text-slate-900 mt-1">{{ $patient->name }}</h3>
                            <p class="text-xs text-slate-500">{{ $patient->gender ?? '—' }}, {{ $patient->age ? $patient->age . ' yrs' : '—' }}</p>
                        </div>
                    </div>
                    @if($patient->blood_group)
                        <span class="px-2.5 py-0.5 rounded-md text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                            {{ $patient->blood_group }}
                        </span>
                    @endif
                </div>

                <div class="text-xs text-slate-600 space-y-1 pt-2 border-t border-slate-100">
                    <p><span class="font-medium text-slate-400">Phone:</span> {{ $patient->phone }}</p>
                    <p><span class="font-medium text-slate-400">Registered:</span> {{ $patient->created_at->format('d M Y') }}</p>
                    @if($patient->allergies)
                        <p class="text-amber-800 bg-amber-50 px-2.5 py-1 rounded-xl text-[11px] font-medium mt-1 border border-amber-200/60">
                            ⚠️ Allergies: {{ Str::limit($patient->allergies, 40) }}
                        </p>
                    @endif
                </div>

                <div class="pt-2 border-t border-slate-100 flex items-center justify-between">
                    <a href="{{ route('patients.show', $patient) }}" class="text-xs font-bold text-brand-600 hover:text-brand-700">
                        View Profile &rarr;
                    </a>
                    <div class="flex items-center gap-1.5">
                        @can('patients.edit')
                            <a href="{{ route('patients.edit', $patient) }}" class="btn-pill-secondary !py-1 !px-3 !text-[11px]">Edit</a>
                        @endcan
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full glass-card rounded-2xl p-8 text-center text-slate-400 border border-white/80">
                No patients found matching your search criteria.
            </div>
        @endforelse
    </div>

    <!-- Desktop Multi-column Table View -->
    <div class="hidden lg:block table-container">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50/70 text-[11px] uppercase font-bold text-slate-400 border-b border-slate-100 tracking-wider">
                    <tr>
                        <th class="px-6 py-4">Patient ID</th>
                        <th class="px-4 py-4">Patient Name</th>
                        <th class="px-4 py-4">Age / Gender</th>
                        <th class="px-4 py-4">Contact</th>
                        <th class="px-4 py-4">Blood Group</th>
                        <th class="px-4 py-4">Clinical Alerts</th>
                        <th class="px-4 py-4">Registered</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($patients as $patient)
                        <tr class="hover:bg-white/60 transition">
                            <td class="px-6 py-4 font-mono font-bold text-brand-600">
                                {{ $patient->patient_id }}
                            </td>
                            <td class="px-4 py-4 font-semibold text-slate-900">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-7 h-7 rounded-full bg-brand-50 text-brand-700 font-bold text-[11px] flex items-center justify-center border border-brand-100/60 shrink-0">
                                        {{ strtoupper(substr($patient->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <a href="{{ route('patients.show', $patient) }}" class="hover:text-brand-600 transition font-bold">
                                            {{ $patient->name }}
                                        </a>
                                        @if($patient->email)
                                            <p class="text-[11px] text-slate-400 font-normal">{{ $patient->email }}</p>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-4 text-slate-700 font-medium">
                                {{ $patient->age ? $patient->age . ' yrs' : '—' }} • {{ $patient->gender ?? '—' }}
                            </td>
                            <td class="px-4 py-4 text-slate-700 font-medium">
                                {{ $patient->phone }}
                            </td>
                            <td class="px-4 py-4">
                                @if($patient->blood_group)
                                    <span class="inline-block px-2.5 py-0.5 rounded-md text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                        {{ $patient->blood_group }}
                                    </span>
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-4">
                                @if(!empty($patient->allergies))
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-[11px] font-medium bg-amber-50 text-amber-800 border border-amber-200" title="{{ $patient->allergies }}">
                                        ⚠️ Allergy Flagged
                                    </span>
                                @else
                                    <span class="text-[11px] text-slate-400">Clear</span>
                                @endif
                            </td>
                            <td class="px-4 py-4 text-slate-500 whitespace-nowrap">
                                {{ $patient->created_at->format('d M Y') }}
                            </td>
                            <td class="px-6 py-4 text-right space-x-1.5 whitespace-nowrap">
                                <a href="{{ route('patients.show', $patient) }}" 
                                   class="btn-pill-primary !py-1.5 !px-3 !text-[11px]">
                                    Profile
                                </a>
                                @can('patients.edit')
                                <a href="{{ route('patients.edit', $patient) }}" 
                                   class="btn-pill-secondary !py-1.5 !px-3 !text-[11px]">
                                    Edit
                                </a>
                                @endcan
                                @can('patients.delete')
                                <form action="{{ route('patients.destroy', $patient) }}" method="POST" class="inline" onsubmit="return confirm('Archive this patient record?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-pill-secondary !py-1.5 !px-2.5 !text-[11px] !text-rose-600 hover:!bg-rose-50 hover:!text-rose-700 !border-rose-200">
                                        Archive
                                    </button>
                                </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-10 text-center text-slate-400">
                                No patients found matching your search filters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($patients->hasPages())
            <div class="px-6 py-4 border-t border-slate-100 bg-white/40">
                {{ $patients->links() }}
            </div>
        @endif
    </div>

</div>
@endsection

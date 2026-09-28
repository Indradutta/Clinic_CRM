@extends('layouts.app')

@section('title', 'Staff Subaccounts Directory')

@section('content')
<div class="space-y-8">
    
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-lg bg-brand-50/80 border border-brand-100 text-brand-700 text-xs font-semibold mb-2">
                <span>Team & Access Control</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-slate-900">Staff Directory (Subaccounts)</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">Manage clinic personnel, assign operational roles, and configure granular module permissions.</p>
        </div>
        @can('subaccounts.create')
            <a href="{{ route('subaccounts.create') }}" class="btn-pill-primary inline-flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                <span>Add New Staff</span>
            </a>
        @endcan
    </div>

    <!-- Filters & Search Bar -->
    <div class="glass-card rounded-2xl p-5">
        <form method="GET" action="{{ route('subaccounts.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5 items-center">
            <div>
                <div class="relative flex items-center">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <input type="text" 
                           name="search" 
                           value="{{ request('search') }}" 
                           placeholder="Search name, username, email..." 
                           class="apple-input w-full !pl-10 pr-3">
                </div>
            </div>
            <div>
                <select name="role" class="apple-input w-full">
                    <option value="">All Roles</option>
                    <option value="admin_doctor" {{ request('role') === 'admin_doctor' ? 'selected' : '' }}>Admin / Doctor</option>
                    <option value="doctor" {{ request('role') === 'doctor' ? 'selected' : '' }}>Doctor</option>
                    <option value="clinic_manager" {{ request('role') === 'clinic_manager' ? 'selected' : '' }}>Clinic Manager</option>
                    <option value="receptionist" {{ request('role') === 'receptionist' ? 'selected' : '' }}>Receptionist</option>
                    <option value="nurse" {{ request('role') === 'nurse' ? 'selected' : '' }}>Nurse</option>
                    <option value="assistant" {{ request('role') === 'assistant' ? 'selected' : '' }}>Assistant</option>
                    <option value="accountant" {{ request('role') === 'accountant' ? 'selected' : '' }}>Accountant</option>
                    <option value="other_staff" {{ request('role') === 'other_staff' ? 'selected' : '' }}>Other Staff</option>
                </select>
            </div>
            <div>
                <select name="status" class="apple-input w-full">
                    <option value="">All Statuses</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <div class="flex items-center gap-2">
                <button type="submit" class="btn-pill-primary w-full text-center justify-center">
                    Filter
                </button>
                <a href="{{ route('subaccounts.index') }}" class="btn-pill-secondary px-3.5" title="Reset Filters">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Staff List Table -->
    <div class="table-container">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50/70 border-b border-slate-100 text-[11px] font-bold uppercase tracking-wider text-slate-400">
                    <tr>
                        <th class="px-6 py-4">Staff Member</th>
                        <th class="px-4 py-4">Username</th>
                        <th class="px-4 py-4">Role</th>
                        <th class="px-4 py-4">Contact</th>
                        <th class="px-4 py-4">Status</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100/60">
                    @forelse($staff as $member)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="px-6 py-4 flex items-center gap-3">
                                <div class="w-9 h-9 rounded-full bg-brand-50 text-brand-700 flex items-center justify-center font-bold text-xs shrink-0 border border-brand-200/80">
                                    {{ strtoupper(substr($member->name, 0, 2)) }}
                                </div>
                                <div>
                                    <p class="font-bold text-slate-900">{{ $member->name }}</p>
                                    <p class="text-[11px] text-slate-400 font-medium">{{ $member->email }}</p>
                                </div>
                            </td>
                            <td class="px-4 py-4 font-mono font-semibold text-slate-700">
                                {{ $member->username }}
                            </td>
                            <td class="px-4 py-4">
                                @php
                                    $roleColors = [
                                        'admin_doctor' => 'bg-brand-50 text-brand-700 border-brand-200',
                                        'clinic_manager' => 'bg-purple-50 text-purple-700 border-purple-200',
                                        'receptionist' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                        'nurse' => 'bg-teal-50 text-teal-700 border-teal-200',
                                        'assistant' => 'bg-cyan-50 text-cyan-700 border-cyan-200',
                                        'accountant' => 'bg-amber-50 text-amber-700 border-amber-200',
                                        'other_staff' => 'bg-slate-100 text-slate-700 border-slate-200',
                                    ];
                                    $color = $roleColors[$member->role] ?? 'bg-slate-100 text-slate-700 border-slate-200';
                                @endphp
                                <span class="px-3 py-1 rounded-md text-[10px] font-bold border {{ $color }} capitalize">
                                    {{ str_replace('_', ' ', $member->role) }}
                                </span>
                            </td>
                            <td class="px-4 py-4 text-slate-500 font-mono text-[11px]">
                                {{ $member->phone ?? '—' }}
                            </td>
                            <td class="px-4 py-4">
                                @if($member->isActive())
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-md text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                        Active
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-md text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                        Inactive
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right space-x-1.5 whitespace-nowrap">
                                @can('subaccounts.edit')
                                    <a href="{{ route('subaccounts.edit', $member) }}" class="px-3 py-1.5 rounded-md bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-[11px] transition">Edit</a>
                                @endcan

                                @if(auth()->id() !== $member->id)
                                    @can('subaccounts.edit')
                                        <form action="{{ route('subaccounts.toggle-status', $member) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="px-3 py-1.5 rounded-md text-[11px] font-bold transition {{ $member->isActive() ? 'text-amber-700 bg-amber-50 hover:bg-amber-100' : 'text-emerald-700 bg-emerald-50 hover:bg-emerald-100' }}">{{ $member->isActive() ? 'Deactivate' : 'Activate' }}</button>
                                        </form>
                                    @endcan

                                    @can('subaccounts.delete')
                                        <form action="{{ route('subaccounts.destroy', $member) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this staff subaccount?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="px-3 py-1.5 rounded-md text-rose-600 bg-rose-50 hover:bg-rose-100 text-[11px] font-bold transition">Delete</button>
                                        </form>
                                    @endcan
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-400">
                                No staff members found matching your search.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($staff->hasPages())
            <div class="px-6 py-4 border-t border-slate-100">
                {{ $staff->links() }}
            </div>
        @endif
    </div>

</div>
@endsection

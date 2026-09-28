@extends('layouts.app')

@section('title', 'Doctors')

@section('content')
<div class="mx-auto max-w-7xl space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div><p class="text-xs font-semibold uppercase tracking-wide text-brand-700">Care team</p><h1 class="mt-1 text-2xl font-bold text-slate-900">Doctors</h1><p class="mt-1 text-sm text-slate-500">Contact details and practice information for your doctors.</p></div>
        @can('doctors.create')<a href="{{ route('doctors.create') }}" class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-brand-700 px-4 text-sm font-semibold text-white hover:bg-brand-800"><span aria-hidden="true">＋</span> Add doctor</a>@endcan
    </div>

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="hidden grid-cols-[minmax(0,1.4fr)_minmax(0,1fr)_minmax(0,1fr)_auto] gap-4 border-b border-slate-100 bg-slate-50 px-5 py-3 text-xs font-semibold uppercase tracking-wide text-slate-500 sm:grid"><span>Name</span><span>Specialty</span><span>Contact</span><span>Account</span></div>
        @forelse($doctors as $doctor)
            <div class="grid gap-3 border-b border-slate-100 px-4 py-4 last:border-0 sm:grid-cols-[minmax(0,1.4fr)_minmax(0,1fr)_minmax(0,1fr)_auto] sm:items-center sm:gap-4 sm:px-5">
                <div class="flex min-w-0 items-center gap-3"><span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-sky-50 text-sm font-bold text-brand-700">{{ strtoupper(substr($doctor->name, 0, 1)) }}</span><div class="min-w-0"><p class="truncate text-sm font-semibold text-slate-900">{{ $doctor->name }}</p><p class="truncate text-xs text-slate-500">{{ $doctor->doctorProfile?->education ?: 'Education not added' }}</p></div></div>
                <p class="text-sm text-slate-700"><span class="mr-2 text-xs text-slate-400 sm:hidden">Specialty</span>{{ $doctor->doctorProfile?->specialty ?: 'Not added' }}</p>
                <div class="min-w-0 text-sm"><p class="truncate text-slate-700">{{ $doctor->email }}</p><p class="text-xs text-slate-500">{{ $doctor->phone ?: 'Phone not added' }}</p></div>
                <div class="flex items-center justify-between gap-3"><span class="rounded-full px-2.5 py-1 text-xs font-medium {{ $doctor->status === 'active' ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">{{ ucfirst($doctor->status) }}</span>@can('doctors.edit')<a href="{{ route('doctors.edit', $doctor) }}" class="text-sm font-semibold text-brand-700 hover:text-brand-900">Edit<span class="sr-only"> {{ $doctor->name }}</span></a>@endcan</div>
            </div>
        @empty
            <div class="px-6 py-14 text-center"><div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-sky-50 text-2xl text-brand-700" aria-hidden="true">＋</div><h2 class="text-base font-semibold text-slate-900">No doctors added yet</h2><p class="mt-1 text-sm text-slate-500">Add a doctor to start assigning appointments.</p>@can('doctors.create')<a href="{{ route('doctors.create') }}" class="mt-4 inline-flex rounded-lg bg-brand-700 px-4 py-2.5 text-sm font-semibold text-white">Add doctor</a>@endcan</div>
        @endforelse
    </div>
    {{ $doctors->links() }}
</div>
@endsection

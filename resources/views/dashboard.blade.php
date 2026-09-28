@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
@php
    $nextAppointment = $todayAppointments->first(fn (\App\Models\Appointment $appointment) => in_array($appointment->status, [
        \App\Models\Appointment::STATUS_PENDING,
        \App\Models\Appointment::STATUS_CONFIRMED,
        \App\Models\Appointment::STATUS_CHECKED_IN,
        \App\Models\Appointment::STATUS_IN_CONSULTATION,
    ], true));
@endphp

<div class="space-y-5 sm:space-y-7">
    <header class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-sm font-medium text-text-secondary">{{ now()->format('l, d F Y') }}</p>
            <h1 class="mt-1 text-2xl sm:text-3xl font-bold tracking-tight text-text-primary">Good {{ now()->hour < 12 ? 'morning' : (now()->hour < 17 ? 'afternoon' : 'evening') }}, {{ auth()->user()->name }}</h1>
            <p class="mt-1 text-sm text-text-secondary">{{ $clinicName }} at a glance</p>
        </div>
        <div class="flex flex-wrap gap-2">
            @can('appointments.create')
                <a href="{{ route('appointments.create') }}" class="btn-pill-primary min-h-10">Book appointment</a>
            @endcan
            @can('patients.create')
                <a href="{{ route('patients.create') }}" class="btn-pill-secondary min-h-10">Add patient</a>
            @endcan
        </div>
    </header>

    <section class="grid grid-cols-2 gap-3 sm:grid-cols-2 sm:gap-4 xl:grid-cols-4" aria-label="Practice overview">
        @can('patients.view')
            <a href="{{ route('patients.index') }}" class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition hover:border-brand-300 sm:p-5">
                <p class="text-xs font-semibold text-slate-500">Patients</p>
                <p class="mt-2 text-2xl font-bold text-slate-900">{{ number_format($kpiStats['total_patients']) }}</p>
                <p class="mt-1 text-xs text-slate-500">{{ number_format($kpiStats['new_patients_week']) }} added this week</p>
            </a>
        @endcan
        @can('appointments.view')
            <a href="{{ route('appointments.index', ['date_preset' => 'today']) }}" class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition hover:border-brand-300 sm:p-5">
                <p class="text-xs font-semibold text-slate-500">Appointments today</p>
                <p class="mt-2 text-2xl font-bold text-slate-900">{{ number_format($kpiStats['today_appointments']) }}</p>
                <p class="mt-1 text-xs text-slate-500">{{ number_format($kpiStats['upcoming_appointments']) }} upcoming</p>
            </a>
        @endcan
        @can('invoices.view')
            <a href="{{ route('invoices.index') }}" class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition hover:border-brand-300 sm:p-5">
                <p class="text-xs font-semibold text-slate-500">Outstanding</p>
                <p class="mt-2 text-2xl font-bold text-slate-900">{{ $currencySymbol }}{{ number_format($revenueOverview['outstanding'], 2) }}</p>
                <p class="mt-1 text-xs text-slate-500">{{ number_format($revenueOverview['outstanding_count']) }} invoices due</p>
            </a>
        @endcan
        @can('invoices.view')
            <a href="{{ route('invoices.index', ['date_preset' => 'today']) }}" class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition hover:border-brand-300 sm:p-5">
                <p class="text-xs font-semibold text-slate-500">Received today</p>
                <p class="mt-2 text-2xl font-bold text-slate-900">{{ $currencySymbol }}{{ number_format($revenueOverview['today'], 2) }}</p>
                <p class="mt-1 text-xs text-slate-500">{{ $currencySymbol }}{{ number_format($revenueOverview['month'], 2) }} this month</p>
            </a>
        @endcan
    </section>

    <div class="grid grid-cols-1 gap-5 xl:grid-cols-3 sm:gap-6">
        @can('appointments.view')
        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm xl:col-span-2" aria-labelledby="schedule-heading">
            <div class="flex items-center justify-between gap-3 border-b border-slate-100 px-4 py-4 sm:px-5">
                <div>
                    <h2 id="schedule-heading" class="text-sm font-bold text-slate-900">Today’s schedule</h2>
                    <p class="mt-0.5 text-xs text-slate-500">{{ $todayAppointments->count() }} appointments</p>
                </div>
                @can('appointments.view')
                    <a href="{{ route('appointments.index', ['date_preset' => 'today']) }}" class="text-xs font-semibold text-brand-700 hover:underline">View schedule</a>
                @endcan
            </div>

            @forelse($todayAppointments as $appointment)
                <article class="flex flex-col gap-3 border-b border-slate-100 p-4 last:border-0 sm:flex-row sm:items-center sm:justify-between sm:px-5">
                    <a href="{{ route('appointments.show', $appointment) }}" class="flex min-w-0 items-start gap-3">
                        <div class="w-12 shrink-0 pt-0.5 text-center">
                            <span class="block text-sm font-bold text-slate-900">{{ date('g:i', strtotime($appointment->appointment_time)) }}</span>
                            <span class="block text-[10px] font-semibold uppercase text-slate-400">{{ date('a', strtotime($appointment->appointment_time)) }}</span>
                        </div>
                        <div class="min-w-0">
                            <p class="truncate text-sm font-semibold text-slate-900">{{ $appointment->patient->name ?? 'Patient' }}</p>
                            <p class="mt-0.5 truncate text-xs text-slate-500">{{ $appointment->appointment_type }} <span aria-hidden="true">·</span> {{ $appointment->doctor->name ?? 'Unassigned' }}</p>
                        </div>
                    </a>
                    <div class="flex items-center justify-between gap-3 sm:justify-end">
                        <span class="inline-flex rounded-full border px-2.5 py-1 text-[10px] font-bold {{ $appointment->status_badge_class }}">{{ $appointment->status_label }}</span>
                        @if($appointment->id === $nextAppointment?->id)
                            @can('consultations.view')
                            @can('consultations.create')
                                <a href="{{ route('consultations.create', ['appointment_id' => $appointment->id]) }}" class="text-xs font-semibold text-brand-700 hover:underline">Start visit</a>
                            @endcan
                            @endcan
                        @endif
                    </div>
                </article>
            @empty
                <div class="px-5 py-12 text-center">
                    <p class="text-sm font-semibold text-slate-700">No appointments scheduled today</p>
                    <p class="mt-1 text-xs text-slate-500">Your schedule will appear here when appointments are booked.</p>
                    @can('appointments.create')
                        <a href="{{ route('appointments.create') }}" class="btn-pill-primary mt-4">Book appointment</a>
                    @endcan
                </div>
            @endforelse
        </section>
        @endcan

        @can('invoices.view')
        <section class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5" aria-labelledby="revenue-heading">
            <div class="flex items-center justify-between gap-3">
                <h2 id="revenue-heading" class="text-sm font-bold text-slate-900">Revenue</h2>
                @can('invoices.view')
                    <a href="{{ route('invoices.index') }}" class="text-xs font-semibold text-brand-700 hover:underline">Billing</a>
                @endcan
            </div>
            <p class="mt-4 text-2xl font-bold text-slate-900">{{ $currencySymbol }}{{ number_format($revenueOverview['month'], 2) }}</p>
            <p class="mt-1 text-xs text-slate-500">Payments received this month</p>
            <dl class="mt-5 space-y-3 border-t border-slate-100 pt-4 text-xs">
                <div class="flex justify-between gap-3"><dt class="text-slate-500">Today</dt><dd class="font-semibold text-slate-900">{{ $currencySymbol }}{{ number_format($revenueOverview['today'], 2) }}</dd></div>
                <div class="flex justify-between gap-3"><dt class="text-slate-500">This week</dt><dd class="font-semibold text-slate-900">{{ $currencySymbol }}{{ number_format($revenueOverview['week'], 2) }}</dd></div>
                <div class="flex justify-between gap-3"><dt class="text-slate-500">Outstanding</dt><dd class="font-semibold text-amber-700">{{ $currencySymbol }}{{ number_format($revenueOverview['outstanding'], 2) }}</dd></div>
            </dl>
        </section>
        @endcan
    </div>

    @can('patients.view')
        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm" aria-labelledby="patients-heading">
            <div class="flex items-center justify-between gap-3 border-b border-slate-100 px-4 py-4 sm:px-5">
                <div>
                    <h2 id="patients-heading" class="text-sm font-bold text-slate-900">Recently added patients</h2>
                    <p class="mt-0.5 text-xs text-slate-500">Latest registrations in your clinic</p>
                </div>
                <a href="{{ route('patients.index') }}" class="text-xs font-semibold text-brand-700 hover:underline">Patient directory</a>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse($recentPatients as $patient)
                    <a href="{{ route('patients.show', $patient) }}" class="flex items-center justify-between gap-3 p-4 transition hover:bg-slate-50 sm:px-5">
                        <div class="flex min-w-0 items-center gap-3">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-brand-50 text-xs font-bold text-brand-700">{{ strtoupper(substr($patient->name, 0, 2)) }}</span>
                            <span class="min-w-0">
                                <span class="block truncate text-sm font-semibold text-slate-900">{{ $patient->name }}</span>
                                <span class="mt-0.5 block truncate text-xs text-slate-500">{{ $patient->patient_id }} <span aria-hidden="true">·</span> {{ $patient->phone }}</span>
                            </span>
                        </div>
                        <span class="hidden text-right text-xs text-slate-500 sm:block">
                            Last visit: {{ $patient->last_visit_display }}<br>
                            Next: {{ $patient->next_appointment_display }}
                        </span>
                        <span class="text-xs font-semibold text-brand-700 sm:hidden" aria-hidden="true">View →</span>
                    </a>
                @empty
                    <p class="p-5 text-sm text-slate-500">No patient records yet.</p>
                @endforelse
            </div>
        </section>
    @endcan
</div>
@endsection

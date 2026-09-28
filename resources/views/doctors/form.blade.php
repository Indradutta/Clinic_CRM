@extends('layouts.app')

@section('title', $editing ? 'Edit doctor' : 'Add doctor')

@section('content')
<div class="mx-auto max-w-4xl space-y-6">
    <div><a href="{{ route('doctors.index') }}" class="text-sm font-medium text-brand-700">← Doctors</a><h1 class="mt-3 text-2xl font-bold text-slate-900">{{ $editing ? 'Edit doctor details' : 'Add a doctor' }}</h1><p class="mt-1 text-sm text-slate-500">Save contact, education, and practice details in one place.</p></div>
    <form method="POST" action="{{ $editing ? route('doctors.update', $doctor) : route('doctors.store') }}" class="space-y-5 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-7" novalidate>
        @csrf @if($editing) @method('PUT') @endif
        <section class="space-y-4"><div><h2 class="text-base font-semibold text-slate-900">Contact details</h2><p class="text-sm text-slate-500">These details also identify the staff sign-in account.</p></div><div class="grid gap-4 sm:grid-cols-2">
            @foreach(['name' => 'Full name', 'email' => 'Work email', 'username' => 'Username', 'phone' => 'Phone number'] as $field => $label)
                <div><label for="{{ $field }}" class="mb-1.5 block text-sm font-medium text-slate-700">{{ $label }} @if(in_array($field, ['name','email','username']))<span class="text-rose-600">*</span>@endif</label><input id="{{ $field }}" name="{{ $field }}" type="{{ $field === 'email' ? 'email' : ($field === 'phone' ? 'tel' : 'text') }}" @if($field === 'phone') inputmode="numeric" minlength="10" maxlength="10" pattern="[6-9][0-9]{9}" placeholder="98765 43210" @endif value="{{ old($field, $doctor->{$field}) }}" @required(in_array($field, ['name','email','username'])) class="w-full rounded-xl border border-slate-300 px-3.5 py-3 text-sm focus:border-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-600/15">@error($field)<p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p>@enderror</div>
            @endforeach
        </div></section>
        <section class="space-y-4 border-t border-slate-100 pt-5"><div><h2 class="text-base font-semibold text-slate-900">Professional details</h2></div><div class="grid gap-4 sm:grid-cols-2">
            @foreach(['specialty' => 'Specialty', 'education' => 'Education', 'license_number' => 'License number', 'years_experience' => 'Years of experience', 'consultation_fee' => 'Consultation fee'] as $field => $label)
                <div><label for="{{ $field }}" class="mb-1.5 block text-sm font-medium text-slate-700">{{ $label }}</label><input id="{{ $field }}" name="{{ $field }}" value="{{ old($field, $profile?->{$field}) }}" @if(in_array($field, ['years_experience','consultation_fee'])) type="number" min="0" step="{{ $field === 'consultation_fee' ? '0.01' : '1' }}" @else type="text" @endif class="w-full rounded-xl border border-slate-300 px-3.5 py-3 text-sm focus:border-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-600/15">@error($field)<p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p>@enderror</div>
            @endforeach
            <div class="sm:col-span-2"><label for="address" class="mb-1.5 block text-sm font-medium text-slate-700">Practice address</label><textarea id="address" name="address" rows="2" class="w-full rounded-xl border border-slate-300 px-3.5 py-3 text-sm focus:border-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-600/15">{{ old('address', $profile?->address) }}</textarea>@error('address')<p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p>@enderror</div>
            <div class="sm:col-span-2"><label for="bio" class="mb-1.5 block text-sm font-medium text-slate-700">About</label><textarea id="bio" name="bio" rows="3" class="w-full rounded-xl border border-slate-300 px-3.5 py-3 text-sm focus:border-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-600/15">{{ old('bio', $profile?->bio) }}</textarea>@error('bio')<p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p>@enderror</div>
        </div></section>
        @if(!$editing)<p class="rounded-xl bg-sky-50 p-3 text-sm text-sky-900">The doctor can create a password with the Forgot password link using this work email.</p>@endif
        <div class="flex flex-col-reverse gap-3 border-t border-slate-100 pt-5 sm:flex-row sm:justify-end"><a href="{{ route('doctors.index') }}" class="inline-flex min-h-11 items-center justify-center rounded-xl border border-slate-300 px-4 text-sm font-semibold text-slate-700">Cancel</a><button type="submit" class="min-h-11 rounded-xl bg-brand-700 px-5 text-sm font-semibold text-white hover:bg-brand-800">{{ $editing ? 'Save changes' : 'Add doctor' }}</button></div>
    </form>
</div>
@endsection

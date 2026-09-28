@extends('layouts.guest')

@section('content')
<div class="auth-heading-block"><p class="auth-eyebrow">Account help</p><h1 id="login-heading">Reset your password</h1><p class="auth-intro">Enter your staff email and we’ll send a one-time reset code.</p></div>
@if(session('error'))<div class="auth-alert" role="alert">{{ session('error') }}</div>@endif
<form method="POST" action="{{ route('password.email') }}" class="auth-form">@csrf<div class="auth-field"><label for="email">Work email</label><div class="auth-input-wrap"><input id="email" name="email" type="email" autocomplete="email" required autofocus placeholder="you@clinic.com" value="{{ old('email') }}"></div>@error('email')<p class="auth-field-error">{{ $message }}</p>@enderror</div><button class="auth-submit" type="submit"><span>Email me a code</span></button></form>
<p class="mt-6 text-center"><a class="text-sm text-slate-600 hover:text-brand-700" href="{{ route('login') }}">Back to sign in</a></p>
@endsection

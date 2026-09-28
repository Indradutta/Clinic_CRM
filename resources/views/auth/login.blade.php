@extends('layouts.guest')

@section('content')
<div class="auth-heading-block">
    <h1 id="login-heading">Sign in</h1>
</div>

@if(session('error'))
    <div class="auth-alert" role="alert" aria-live="assertive" aria-atomic="true">
        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 8v4m0 4h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" /></svg>
        <span>{{ session('error') }}</span>
    </div>
@endif

@if(session('success'))
    <div class="auth-success" role="status">{{ session('success') }}</div>
@endif

<form method="POST" action="{{ route('login.post') }}" class="auth-form" x-data="{ showPassword: false }" novalidate>
    @csrf

    <div class="auth-alert" id="login-client-error" role="alert" aria-live="assertive" aria-atomic="true" hidden>
        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 8v4m0 4h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" /></svg>
        <span></span>
    </div>

    @if (app()->environment('local'))
        <div class="auth-field">
            <label for="demo-account">Demo account</label>
            <div class="auth-input-wrap">
                <select id="demo-account" aria-describedby="demo-account-help" class="w-full bg-transparent outline-none">
                    <option value="">Select a staff role</option>
                    <option value="doctor" data-email="doctor@mediflow.com" data-password="password">Doctor (clinic administrator)</option>
                    <option value="manager" data-email="manager@mediflow.com" data-password="password">Clinic manager</option>
                    <option value="receptionist" data-email="receptionist@mediflow.com" data-password="password">Receptionist</option>
                    <option value="nurse" data-email="nurse@mediflow.com" data-password="password">Nurse</option>
                    <option value="accountant" data-email="accountant@mediflow.com" data-password="password">Accountant</option>
                </select>
            </div>
            <p class="text-xs text-slate-500" id="demo-account-help">Local development only.</p>
        </div>
    @endif

    <div class="auth-field">
        <label for="login">Username or email</label>
        <div class="auth-input-wrap">
            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M20 21a8 8 0 0 0-16 0m8-10a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" /></svg>
            <input type="text" name="login" id="login" value="{{ old('login') }}" required autofocus autocomplete="username" placeholder="Enter your username or email" aria-describedby="login-error" aria-invalid="{{ $errors->has('login') ? 'true' : 'false' }}">
        </div>
        <p class="auth-field-error" id="login-error" aria-live="polite" @if (! $errors->has('login')) hidden @endif>{{ $errors->first('login') }}</p>
    </div>

    <div class="auth-field">
        <label for="password">Password</label>
        <div class="auth-input-wrap auth-password-wrap">
            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M7 10V7a5 5 0 0 1 10 0v3m-11 0h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-8a2 2 0 0 1 2-2Zm5 5v3" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" /></svg>
            <input x-bind:type="showPassword ? 'text' : 'password'" name="password" id="password" required autocomplete="current-password" placeholder="Enter your password" aria-describedby="password-error" aria-invalid="{{ $errors->has('password') ? 'true' : 'false' }}">
            <button class="auth-password-toggle" type="button" x-on:click="showPassword = !showPassword" x-bind:aria-label="showPassword ? 'Hide password' : 'Show password'" x-bind:aria-pressed="showPassword.toString()">
                <svg x-show="!showPassword" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12Z" stroke="currentColor" stroke-width="1.7"/><circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.7"/></svg>
                <svg x-show="showPassword" x-cloak viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="m3 3 18 18M10.6 10.6a2 2 0 0 0 2.8 2.8M9.9 5.2A10.8 10.8 0 0 1 12 5c6.4 0 10 7 10 7a14 14 0 0 1-3.1 3.8M6.2 6.2C3.5 8 2 12 2 12s3.6 7 10 7c1.4 0 2.7-.4 3.8-.9" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </button>
        </div>
        <p class="auth-field-error" id="password-error" aria-live="polite" @if (! $errors->has('password')) hidden @endif>{{ $errors->first('password') }}</p>
    </div>

    <div class="auth-form-options">
        <span class="text-xs text-slate-500">This device stays signed in for up to 30 days.</span>
        <a href="{{ route('password.request') }}" class="text-sm font-semibold text-brand-700 hover:text-brand-900">Forgot password?</a>
    </div>

    <button class="auth-submit" type="submit">
        <span>Sign in</span>
        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M5 12h14m-6-6 6 6-6 6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" /></svg>
    </button>
</form>

<script>
    const loginForm = document.querySelector('.auth-form');
    const loginInput = document.getElementById('login');
    const passwordInput = document.getElementById('password');
    const clientError = document.getElementById('login-client-error');
    const clientErrorMessage = clientError?.querySelector('span');

    function validateLoginField(input, message) {
        const error = document.getElementById(`${input.id}-error`);
        const isEmpty = input.value.trim() === '';

        error.textContent = isEmpty ? message : '';
        error.hidden = !isEmpty;
        input.setAttribute('aria-invalid', isEmpty ? 'true' : 'false');

        return !isEmpty;
    }

    function hideClientError() {
        clientError.hidden = true;
        clientErrorMessage.textContent = '';
    }

    [loginInput, passwordInput].forEach((input) => {
        input.addEventListener('blur', () => {
            validateLoginField(input, input === loginInput ? 'Enter your username or email.' : 'Enter your password.');
        });

        input.addEventListener('input', () => {
            if (!document.getElementById(`${input.id}-error`).hidden) {
                validateLoginField(input, input === loginInput ? 'Enter your username or email.' : 'Enter your password.');
            }

            hideClientError();
        });
    });

    loginForm.addEventListener('submit', (event) => {
        const loginIsValid = validateLoginField(loginInput, 'Enter your username or email.');
        const passwordIsValid = validateLoginField(passwordInput, 'Enter your password.');

        if (loginIsValid && passwordIsValid) {
            hideClientError();

            return;
        }

        event.preventDefault();

        const missingFields = [];
        if (!loginIsValid) {
            missingFields.push('username or email');
        }
        if (!passwordIsValid) {
            missingFields.push('password');
        }

        clientErrorMessage.textContent = `Please enter your ${missingFields.join(' and ')}.`;
        clientError.hidden = false;
        (loginIsValid ? passwordInput : loginInput).focus();
    });

    document.getElementById('demo-account')?.addEventListener('change', function () {
        const selectedAccount = this.options[this.selectedIndex];

        if (!selectedAccount.dataset.email) {
            return;
        }

        loginInput.value = selectedAccount.dataset.email;
        passwordInput.value = selectedAccount.dataset.password;
        loginInput.dispatchEvent(new Event('input', { bubbles: true }));
        passwordInput.dispatchEvent(new Event('input', { bubbles: true }));
    });
</script>

@endsection

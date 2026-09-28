<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\EmailVerificationCode;
use App\Models\User;
use App\Notifications\AuthEmailOtpNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * Show the login screen.
     */
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route($this->landingRoute(Auth::user()));
        }

        return view('auth.login');
    }

    /** Check staff credentials and start an authenticated session. */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'login' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'max:255'],
        ], [
            'login.required' => 'Enter your username or email.',
            'password.required' => 'Enter your password.',
        ]);

        $loginIdentifier = trim($credentials['login']);
        $loginColumn = filter_var($loginIdentifier, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        if ($loginColumn === 'email') {
            $loginIdentifier = Str::lower($loginIdentifier);
        }

        $user = User::query()
            ->where($loginColumn, $loginIdentifier)
            ->first();

        if (! $user || ! $user->isActive() || ! Hash::check($credentials['password'], $user->password)) {
            return back()->withInput($request->only('login', 'remember'))->with('error', 'Username/email or password is incorrect. Check your details and try again.');
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        return redirect()->intended(route($this->landingRoute($user)))->with('success', 'You are signed in.');
    }

    public function showForgotPassword(): View
    {
        return view('auth.forgot-password');
    }

    public function sendPasswordResetCode(Request $request): RedirectResponse
    {
        $validated = $request->validate(['email' => ['required', 'email', 'max:255']]);
        $email = Str::lower(trim($validated['email']));
        $user = User::whereRaw('LOWER(email) = ?', [$email])->where('status', 'active')->first();
        if ($user) {
            $request->session()->put('auth.reset_user_id', $user->id);
            $this->sendCode($user, EmailVerificationCode::PURPOSE_PASSWORD_RESET);
        }

        return redirect()->route('password.reset')->with('success', 'If an active staff account uses that email, a reset code has been sent.');
    }

    public function showResetPassword(): View|RedirectResponse
    {
        return session()->has('auth.reset_user_id') ? view('auth.reset-password') : redirect()->route('password.request');
    }

    public function resendPasswordResetCode(): RedirectResponse
    {
        $user = User::find(session('auth.reset_user_id'));
        if (! $user || ! $user->isActive()) {
            return redirect()->route('password.request')->with('success', 'Enter your staff email to request a reset code.');
        }

        $lastCode = EmailVerificationCode::where('user_id', $user->id)->where('purpose', EmailVerificationCode::PURPOSE_PASSWORD_RESET)->latest()->first();
        if ($lastCode && $lastCode->created_at->gt(now()->subMinute())) {
            return back()->with('error', 'Please wait a minute before requesting another code.');
        }

        $this->sendCode($user, EmailVerificationCode::PURPOSE_PASSWORD_RESET);

        return back()->with('success', 'A new reset code has been sent.');
    }

    public function resetPassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'digits:6'],
            'password' => ['required', 'confirmed', PasswordRule::min(10)->mixedCase()->numbers()->symbols()],
        ], [
            'password.min' => 'Use at least 10 characters in the password.',
            'password.mixed' => 'Use both uppercase and lowercase letters in the password.',
            'password.numbers' => 'Use at least one number in the password.',
            'password.symbols' => 'Use at least one symbol in the password.',
        ]);

        $user = User::find(session('auth.reset_user_id'));
        if (! $user || ! $user->isActive() || ! $this->consumeCode($user, EmailVerificationCode::PURPOSE_PASSWORD_RESET, $validated['code'])) {
            return back()->withErrors(['code' => 'That code is invalid or expired. Request a new one and try again.']);
        }

        $user->forceFill(['password' => $validated['password'], 'remember_token' => Str::random(60)])->save();
        $request->session()->forget('auth.reset_user_id');

        return redirect()->route('login')->with('success', 'Your password has been updated. Sign in with your new password.');
    }

    private function sendCode(User $user, string $purpose): void
    {
        EmailVerificationCode::where('user_id', $user->id)->where('purpose', $purpose)->whereNull('consumed_at')->update(['consumed_at' => now()]);
        $code = (string) random_int(100000, 999999);
        EmailVerificationCode::create(['user_id' => $user->id, 'purpose' => $purpose, 'code_hash' => Hash::make($code), 'expires_at' => now()->addMinutes(10)]);
        $user->notify(new AuthEmailOtpNotification($code));
    }

    private function consumeCode(User $user, string $purpose, string $plainCode): bool
    {
        $code = EmailVerificationCode::where('user_id', $user->id)->where('purpose', $purpose)->whereNull('consumed_at')->latest()->first();
        if (! $code || $code->expires_at->isPast() || $code->attempts >= 5 || ! Hash::check($plainCode, $code->code_hash)) {
            if ($code && ! $code->consumed_at) {
                $code->increment('attempts');
            }

            return false;
        }

        $code->update(['consumed_at' => now()]);

        return true;
    }

    /**
     * Log the user out of the application.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'You have been successfully signed out.');
    }

    private function landingRoute(User $user): string
    {
        $workspaceRoutes = [
            'dashboard.view' => 'dashboard',
            'doctors.view' => 'doctors.index',
            'appointments.view' => 'appointments.index',
            'patients.view' => 'patients.index',
            'prescriptions.view' => 'prescriptions.index',
            'invoices.view' => 'invoices.index',
            'reports.view' => 'reports.index',
            'subaccounts.view' => 'subaccounts.index',
            'settings.view' => 'settings.index',
        ];

        foreach ($workspaceRoutes as $permission => $route) {
            if ($user->hasPermission($permission)) {
                return $route;
            }
        }

        return 'dashboard';
    }
}

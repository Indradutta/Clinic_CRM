<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DoctorController extends Controller
{
    public function index(): View
    {
        $doctors = User::with('doctorProfile')->whereIn('role', [User::ROLE_DOCTOR, User::ROLE_ADMIN_DOCTOR])->latest()->paginate(12);

        return view('doctors.index', compact('doctors'));
    }

    public function create(): View
    {
        return view('doctors.form', ['doctor' => new User, 'profile' => null, 'editing' => false]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->normalizeEmail($request);
        $validated = $this->validateDoctor($request);
        $doctor = DB::transaction(function () use ($validated): User {
            $user = User::create([
                'name' => $validated['name'], 'email' => $validated['email'], 'username' => $validated['username'],
                'phone' => $validated['phone'] ?? null, 'password' => Hash::make(Str::random(48)),
                'role' => User::ROLE_DOCTOR, 'status' => 'active',
            ]);
            $user->doctorProfile()->create(collect($validated)->only(['specialty', 'education', 'license_number', 'years_experience', 'address', 'bio', 'consultation_fee'])->all());

            return $user;
        });

        return redirect()->route('doctors.index')->with('success', "Dr. {$doctor->name} was added. They can set a password using Forgot password.");
    }

    public function edit(User $doctor): View
    {
        abort_unless(in_array($doctor->role, [User::ROLE_DOCTOR, User::ROLE_ADMIN_DOCTOR], true), 404);

        return view('doctors.form', ['doctor' => $doctor, 'profile' => $doctor->doctorProfile, 'editing' => true]);
    }

    public function update(Request $request, User $doctor): RedirectResponse
    {
        abort_unless(in_array($doctor->role, [User::ROLE_DOCTOR, User::ROLE_ADMIN_DOCTOR], true), 404);
        $this->normalizeEmail($request);
        $validated = $this->validateDoctor($request, $doctor);
        DB::transaction(function () use ($doctor, $validated): void {
            $doctor->update(collect($validated)->only(['name', 'email', 'username', 'phone'])->all());
            $doctor->doctorProfile()->updateOrCreate([], collect($validated)->only(['specialty', 'education', 'license_number', 'years_experience', 'address', 'bio', 'consultation_fee'])->all());
        });

        return redirect()->route('doctors.index')->with('success', "Dr. {$doctor->name}'s details were saved.");
    }

    public function destroy(User $doctor): RedirectResponse
    {
        abort_unless($doctor->role === User::ROLE_DOCTOR, 404);
        $doctor->update(['status' => 'inactive']);

        return redirect()->route('doctors.index')->with('success', "Dr. {$doctor->name}'s account was deactivated.");
    }

    private function validateDoctor(Request $request, ?User $doctor = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($doctor?->id)],
            'username' => ['required', 'string', 'alpha_dash', 'max:100', Rule::unique('users', 'username')->ignore($doctor?->id)],
            'phone' => ['nullable', 'regex:/^[6-9][0-9]{9}$/'],
            'specialty' => ['nullable', 'string', 'max:150'],
            'education' => ['nullable', 'string', 'max:255'],
            'license_number' => ['nullable', 'string', 'max:100', Rule::unique('doctor_profiles', 'license_number')->ignore($doctor?->doctorProfile?->id)],
            'years_experience' => ['nullable', 'integer', 'min:0', 'max:80'],
            'address' => ['nullable', 'string', 'max:2000'],
            'bio' => ['nullable', 'string', 'max:3000'],
            'consultation_fee' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
        ], [
            'name.required' => 'Doctor full name is required.',
            'email.required' => 'Work email address is required.',
            'email.email' => 'Please enter a valid work email address (e.g. doctor@mediflow.com).',
            'email.unique' => 'This email is already in use by another user account.',
            'username.required' => 'Username is required.',
            'username.unique' => 'This username is already taken.',
            'phone.regex' => 'Please enter a valid 10-digit mobile number starting with 6, 7, 8, or 9 (e.g. 98765 43210).',
            'license_number.unique' => 'This medical license number is already registered to another doctor.',
            'years_experience.min' => 'Years of experience cannot be negative.',
            'consultation_fee.min' => 'Consultation fee cannot be negative.',
        ]);
    }

    private function normalizeEmail(Request $request): void
    {
        $email = $request->input('email');
        if (is_string($email)) {
            $request->merge(['email' => Str::lower(trim($email))]);
        }
    }
}

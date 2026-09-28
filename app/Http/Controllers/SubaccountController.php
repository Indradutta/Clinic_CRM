<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\View\View;

class SubaccountController extends Controller
{
    /**
     * Display a listing of all staff subaccounts.
     */
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'role' => ['nullable', Rule::in([
                User::ROLE_ADMIN_DOCTOR,
                User::ROLE_DOCTOR,
                User::ROLE_CLINIC_MANAGER,
                User::ROLE_RECEPTIONIST,
                User::ROLE_NURSE,
                User::ROLE_ASSISTANT,
                User::ROLE_ACCOUNTANT,
                User::ROLE_OTHER_STAFF,
            ])],
            'status' => ['nullable', Rule::in(['active', 'inactive'])],
        ]);
        $query = User::query()->with('permissions');

        if (! empty($filters['search'])) {
            $search = trim($filters['search']);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if (! empty($filters['role'])) {
            $query->where('role', $filters['role']);
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $staff = $query->latest()->paginate(15)->withQueryString();

        return view('subaccounts.index', compact('staff'));
    }

    /**
     * Show the form for creating a new staff subaccount.
     */
    public function create(): View
    {
        $permissions = Permission::all()->groupBy('module');

        return view('subaccounts.create', compact('permissions'));
    }

    /**
     * Store a newly created staff subaccount.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->normalizeEmail($request);
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'username' => ['required', 'string', 'max:100', 'alpha_dash', 'unique:users,username'],
            'phone' => ['nullable', 'regex:/^[6-9][0-9]{9}$/'],
            'password' => ['required', 'string', PasswordRule::min(10)->mixedCase()->numbers()->symbols()],
            'role' => ['required', 'string', Rule::in([
                User::ROLE_ADMIN_DOCTOR,
                User::ROLE_DOCTOR,
                User::ROLE_CLINIC_MANAGER,
                User::ROLE_RECEPTIONIST,
                User::ROLE_NURSE,
                User::ROLE_ASSISTANT,
                User::ROLE_ACCOUNTANT,
                User::ROLE_OTHER_STAFF,
            ])],
            'status' => ['required', 'string', Rule::in(['active', 'inactive'])],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['exists:permissions,id'],
            'permissions_submitted' => ['sometimes', 'boolean'],
        ], [
            'name.required' => 'Staff member full name is required.',
            'email.required' => 'Email address is required.',
            'email.email' => 'Please enter a valid email address (e.g. staff@clinic.com).',
            'email.unique' => 'This email address is already associated with an account.',
            'username.required' => 'Username is required.',
            'username.unique' => 'This username is already taken.',
            'phone.regex' => 'Please enter a valid 10-digit mobile number starting with 6, 7, 8, or 9 (e.g. 98765 43210).',
            'password.required' => 'A password is required for this staff subaccount.',
            'role.required' => 'Please assign a staff role.',
            'status.required' => 'Please select account status.',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'username' => $validated['username'],
            'phone' => $validated['phone'] ?? null,
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'status' => $validated['status'],
        ]);

        if (! empty($validated['permissions'])) {
            $user->permissions()->sync($validated['permissions']);
        }

        return redirect()->route('subaccounts.index')->with('success', "Staff account for '{$user->name}' created successfully.");
    }

    /**
     * Show the form for editing the specified staff subaccount.
     */
    public function edit(User $subaccount): View
    {
        $permissions = Permission::all()->groupBy('module');
        $userPermissionIds = $subaccount->permissions->pluck('id')->toArray();

        return view('subaccounts.edit', compact('subaccount', 'permissions', 'userPermissionIds'));
    }

    /**
     * Update the specified staff subaccount in storage.
     */
    public function update(Request $request, User $subaccount): RedirectResponse
    {
        $this->normalizeEmail($request);
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($subaccount->id)],
            'username' => ['required', 'string', 'max:100', 'alpha_dash', Rule::unique('users', 'username')->ignore($subaccount->id)],
            'phone' => ['nullable', 'regex:/^[6-9][0-9]{9}$/'],
            'password' => ['nullable', 'string', PasswordRule::min(10)->mixedCase()->numbers()->symbols()],
            'role' => ['required', 'string', Rule::in([
                User::ROLE_ADMIN_DOCTOR,
                User::ROLE_DOCTOR,
                User::ROLE_CLINIC_MANAGER,
                User::ROLE_RECEPTIONIST,
                User::ROLE_NURSE,
                User::ROLE_ASSISTANT,
                User::ROLE_ACCOUNTANT,
                User::ROLE_OTHER_STAFF,
            ])],
            'status' => ['required', 'string', Rule::in(['active', 'inactive'])],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['exists:permissions,id'],
        ], [
            'name.required' => 'Staff member full name is required.',
            'email.required' => 'Email address is required.',
            'email.email' => 'Please enter a valid email address (e.g. staff@clinic.com).',
            'email.unique' => 'This email address is already associated with an account.',
            'username.required' => 'Username is required.',
            'username.unique' => 'This username is already taken.',
            'phone.regex' => 'Please enter a valid 10-digit mobile number starting with 6, 7, 8, or 9 (e.g. 98765 43210).',
            'role.required' => 'Please assign a staff role.',
            'status.required' => 'Please select account status.',
        ]);

        $subaccount->name = $validated['name'];
        $subaccount->email = $validated['email'];
        $subaccount->username = $validated['username'];
        $subaccount->phone = $validated['phone'] ?? null;
        $subaccount->role = $validated['role'];
        $subaccount->status = $validated['status'];

        if (! empty($validated['password'])) {
            $subaccount->password = Hash::make($validated['password']);
        }

        $subaccount->save();

        if ($request->boolean('permissions_submitted')) {
            $subaccount->permissions()->sync($validated['permissions'] ?? []);
        }

        return redirect()->route('subaccounts.index')->with('success', "Staff account for '{$subaccount->name}' updated successfully.");
    }

    /**
     * Remove the specified staff subaccount.
     */
    public function destroy(User $subaccount): RedirectResponse
    {
        if (auth()->id() === $subaccount->id) {
            return back()->with('error', 'You cannot delete your own active administrator account.');
        }

        $subaccount->delete();

        return redirect()->route('subaccounts.index')->with('success', 'Staff account deleted successfully.');
    }

    /**
     * Toggle status between active and inactive.
     */
    public function toggleStatus(User $subaccount): RedirectResponse
    {
        if (auth()->id() === $subaccount->id) {
            return back()->with('error', 'You cannot deactivate your own account.');
        }

        $subaccount->status = $subaccount->status === 'active' ? 'inactive' : 'active';
        $subaccount->save();

        return back()->with('success', "Staff account status changed to '{$subaccount->status}'.");
    }

    private function normalizeEmail(Request $request): void
    {
        $email = $request->input('email');
        if (is_string($email)) {
            $request->merge(['email' => Str::lower(trim($email))]);
        }
    }
}

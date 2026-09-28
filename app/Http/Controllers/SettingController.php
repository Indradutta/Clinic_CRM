<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class SettingController extends Controller
{
    /**
     * Display the settings management hub.
     */
    public function index(Request $request): View
    {
        $activeTab = $request->query('tab', 'doctor');
        $settings = Setting::all()->pluck('value', 'key')->toArray();

        // Decode JSON values
        if (! empty($settings['appointment_working_days'])) {
            $settings['appointment_working_days'] = json_decode($settings['appointment_working_days'], true) ?: [];
        } else {
            $settings['appointment_working_days'] = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        }

        // Fetch active login sessions for current user (Sec 8, p. 11)
        $sessions = [];
        if ($activeTab === 'security') {
            $sessions = DB::table('sessions')
                ->where('user_id', auth()->id())
                ->orderBy('last_activity', 'desc')
                ->get();
        }

        return view('settings.index', compact('activeTab', 'settings', 'sessions'));
    }

    /**
     * Update settings for a specific group.
     */
    public function update(Request $request, string $group): RedirectResponse
    {
        abort_unless(in_array($group, ['doctor', 'clinic', 'appointment', 'prescription', 'invoice', 'notification'], true), 404);

        $messages = [
            'clinic_name.required' => 'Clinic name is required.',
            'clinic_address.required' => 'Clinic physical address is required.',
            'clinic_phone.required' => 'Clinic phone number is required.',
            'clinic_phone.regex' => 'Please enter a valid 10-digit mobile number starting with 6, 7, 8, or 9 (e.g. 98765 43210).',
            'clinic_email.required' => 'Clinic email is required.',
            'clinic_email.email' => 'Please enter a valid clinic email address.',
            'clinic_consultation_fee.min' => 'Standard consultation fee cannot be negative.',
            'appointment_working_hours_end.after' => 'Practice closing hours must be after opening hours.',
            'appointment_break_time_end.after' => 'Break end time must be after break start time.',
            'appointment_break_time_start.after_or_equal' => 'Break start time must be within working hours.',
            'appointment_working_days.required' => 'Please select at least one working day for the practice.',
            'invoice_prefix.regex' => 'Invoice prefix may only contain letters, numbers, and dashes.',
            'invoice_numbering.min' => 'Next invoice number must be at least 1.',
        ];

        $validated = $request->validate($this->settingsRules($group), $messages);
        $data = $validated;

        // Handle file uploads
        if ($group === 'doctor' && $request->hasFile('doctor_profile_photo')) {
            $path = $request->file('doctor_profile_photo')->store('settings', 'public');
            Setting::set('doctor_profile_photo', '/storage/'.$path, 'doctor');
            unset($data['doctor_profile_photo']);
        }

        if ($group === 'clinic' && $request->hasFile('clinic_logo')) {
            $path = $request->file('clinic_logo')->store('settings', 'public');
            Setting::set('clinic_logo', '/storage/'.$path, 'clinic');
            unset($data['clinic_logo']);
        }

        if ($group === 'prescription' && $request->hasFile('prescription_clinic_logo')) {
            $path = $request->file('prescription_clinic_logo')->store('settings', 'public');
            Setting::set('prescription_clinic_logo', '/storage/'.$path, 'prescription');
            unset($data['prescription_clinic_logo']);
        }

        if ($group === 'prescription' && $request->hasFile('prescription_signature')) {
            $path = $request->file('prescription_signature')->store('settings', 'public');
            Setting::set('prescription_signature', '/storage/'.$path, 'prescription');
            unset($data['prescription_signature']);
        }

        // Handle working days array
        if ($group === 'appointment') {
            $workingDays = $validated['appointment_working_days'] ?? [];
            Setting::set('appointment_working_days', json_encode($workingDays), 'appointment');
            unset($data['appointment_working_days']);

            $data['appointment_reminders'] = $request->has('appointment_reminders') ? '1' : '0';
        }

        // Handle notification toggles
        if ($group === 'notification') {
            $notificationKeys = [
                'notify_new_appointment',
                'notify_appointment_confirmation',
                'notify_appointment_cancellation',
                'notify_appointment_reminders',
                'notify_payment_received',
                'notify_invoice_due',
            ];

            foreach ($notificationKeys as $notifKey) {
                Setting::set($notifKey, $request->has($notifKey) ? '1' : '0', 'notification');
                unset($data[$notifKey]);
            }
        }

        // Save remaining fields
        foreach ($data as $key => $value) {
            Setting::set($key, $value ?? '', $group);
        }

        return redirect()->route('settings.index', ['tab' => $group])
            ->with('success', ucfirst($group).' settings updated successfully.');
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    private function settingsRules(string $group): array
    {
        $sharedText = ['sometimes', 'nullable', 'string', 'max:2000'];

        return match ($group) {
            'doctor' => [
                'doctor_name' => ['sometimes', 'required', 'string', 'max:255'],
                'doctor_registration_number' => ['sometimes', 'required', 'string', 'max:100'],
                'doctor_qualification' => ['sometimes', 'required', 'string', 'max:255'],
                'doctor_specialization' => ['sometimes', 'required', 'string', 'max:150'],
                'doctor_phone' => ['sometimes', 'required', 'regex:/^[6-9][0-9]{9}$/'],
                'doctor_email' => ['sometimes', 'required', 'email', 'max:255'],
                'doctor_clinic_name' => ['sometimes', 'nullable', 'string', 'max:255'],
                'doctor_clinic_address' => ['sometimes', 'nullable', 'string', 'max:2000'],
                'doctor_profile_photo' => ['sometimes', 'nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            ],
            'clinic' => [
                'clinic_name' => ['sometimes', 'required', 'string', 'max:255'],
                'clinic_address' => ['sometimes', 'required', 'string', 'max:2000'],
                'clinic_phone' => ['sometimes', 'required', 'regex:/^[6-9][0-9]{9}$/'],
                'clinic_email' => ['sometimes', 'required', 'email', 'max:255'],
                'clinic_website' => ['sometimes', 'nullable', 'url', 'max:255'],
                'clinic_working_hours' => ['sometimes', 'nullable', 'string', 'max:255'],
                'clinic_consultation_fee' => ['sometimes', 'required', 'numeric', 'min:0', 'max:99999999.99'],
                'clinic_currency' => ['sometimes', 'required', 'string', 'max:5'],
                'clinic_time_zone' => ['sometimes', 'required', 'timezone'],
                'clinic_logo' => ['sometimes', 'nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            ],
            'appointment' => [
                'appointment_duration' => ['required', Rule::in(['10', '15', '20', '30', '45', '60'])],
                'appointment_max_per_day' => ['required', 'integer', 'min:1', 'max:1000'],
                'appointment_working_hours_start' => ['required', 'date_format:H:i'],
                'appointment_working_hours_end' => ['required', 'date_format:H:i', 'after:appointment_working_hours_start'],
                'appointment_break_time_start' => ['nullable', 'date_format:H:i', 'after_or_equal:appointment_working_hours_start', 'required_with:appointment_break_time_end'],
                'appointment_break_time_end' => ['nullable', 'date_format:H:i', 'after:appointment_break_time_start', 'before_or_equal:appointment_working_hours_end', 'required_with:appointment_break_time_start'],
                'appointment_working_days' => ['required', 'array', 'min:1', 'max:7'],
                'appointment_working_days.*' => ['required', Rule::in(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday']), 'distinct'],
                'appointment_cancellation_rules' => ['sometimes', 'nullable', 'string', 'max:2000'],
                'appointment_reminders' => ['sometimes', 'boolean'],
            ],
            'prescription' => [
                'prescription_default_instructions' => $sharedText,
                'prescription_doctor_info' => $sharedText,
                'prescription_footer' => $sharedText,
                'prescription_header' => $sharedText,
                'prescription_clinic_logo' => ['sometimes', 'nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
                'prescription_signature' => ['sometimes', 'nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            ],
            'invoice' => [
                'invoice_prefix' => ['sometimes', 'required', 'string', 'max:20', 'regex:/^[A-Za-z0-9-]+$/'],
                'invoice_numbering' => ['sometimes', 'required', 'integer', 'min:1', 'max:999999999'],
                'invoice_tax_settings' => ['sometimes', 'nullable', 'numeric', 'min:0', 'max:100'],
                'invoice_business_information' => ['sometimes', 'nullable', 'string', 'max:255'],
                'invoice_payment_terms' => $sharedText,
                'invoice_footer' => $sharedText,
            ],
            'notification' => [
                'notify_new_appointment' => ['sometimes', 'boolean'],
                'notify_appointment_confirmation' => ['sometimes', 'boolean'],
                'notify_appointment_cancellation' => ['sometimes', 'boolean'],
                'notify_appointment_reminders' => ['sometimes', 'boolean'],
                'notify_payment_received' => ['sometimes', 'boolean'],
                'notify_invoice_due' => ['sometimes', 'boolean'],
            ],
        };
    }

    /**
     * Update user password (Security Settings - Sec 8, p. 11).
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(10)->mixedCase()->numbers()->symbols()],
        ], [
            'current_password.required' => 'Current password is required.',
            'current_password.current_password' => 'Your current password was incorrect.',
            'password.required' => 'A new password is required.',
            'password.confirmed' => 'New password confirmation does not match.',
        ]);

        $user = auth()->user();
        $user->password = Hash::make($validated['password']);
        $user->save();

        return redirect()->route('settings.index', ['tab' => 'security'])
            ->with('success', 'Your password has been changed successfully.');
    }

    /**
     * Terminate an active login session (Sec 8, p. 11).
     */
    public function destroySession(Request $request, string $sessionId): RedirectResponse
    {
        if ($sessionId === session()->getId()) {
            return back()->with('error', 'You cannot revoke your currently active session from here. Use Sign Out instead.');
        }

        DB::table('sessions')
            ->where('id', $sessionId)
            ->where('user_id', auth()->id())
            ->delete();

        return redirect()->route('settings.index', ['tab' => 'security'])
            ->with('success', 'The selected login session was revoked.');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Consultation;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\Setting;
use App\Models\User;
use App\Notifications\PracticeActivityNotification;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PrescriptionController extends Controller
{
    /**
     * Display a listing of digital prescriptions with search and date filters (Sec 6, pp. 6-7).
     */
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'date_preset' => ['nullable', Rule::in(['today', 'this_week', 'this_month'])],
            'doctor_id' => ['nullable', 'integer', 'exists:users,id'],
        ]);
        $query = Prescription::with(['patient', 'doctor', 'items'])->latest('prescription_date');

        // Search: RX Number, Patient Name, Patient ID, Diagnosis
        if (! empty($filters['search'])) {
            $search = trim($filters['search']);
            $query->where(function ($q) use ($search) {
                $q->where('prescription_number', 'like', "%{$search}%")
                    ->orWhere('diagnosis', 'like', "%{$search}%")
                    ->orWhereHas('patient', function ($pq) use ($search) {
                        $pq->where('name', 'like', "%{$search}%")
                            ->orWhere('patient_id', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    });
            });
        }

        // Filter: Date Presets
        $dateFilter = $filters['date_preset'] ?? null;
        if ($dateFilter === 'today') {
            $query->whereDate('prescription_date', now()->toDateString());
        } elseif ($dateFilter === 'this_week') {
            $query->whereBetween('prescription_date', [now()->startOfWeek()->toDateString(), now()->endOfWeek()->toDateString()]);
        } elseif ($dateFilter === 'this_month') {
            $query->whereMonth('prescription_date', now()->month)->whereYear('prescription_date', now()->year);
        }

        // Custom Date Range
        if ($request->filled('from_date')) {
            $query->whereDate('prescription_date', '>=', $request->input('from_date'));
        }
        if ($request->filled('to_date')) {
            $query->whereDate('prescription_date', '<=', $request->input('to_date'));
        }

        // Doctor Filter
        if (! empty($filters['doctor_id'])) {
            $query->where('doctor_id', $filters['doctor_id']);
        }

        $prescriptions = $query->paginate(15)->withQueryString();

        // Metrics
        $totalCount = Prescription::count();
        $todayCount = Prescription::whereDate('prescription_date', now()->toDateString())->count();
        $thisMonthCount = Prescription::whereMonth('prescription_date', now()->month)->whereYear('prescription_date', now()->year)->count();

        $doctors = User::whereIn('role', [
            User::ROLE_ADMIN_DOCTOR,
            User::ROLE_DOCTOR,
            User::ROLE_CLINIC_MANAGER,
            User::ROLE_NURSE,
            User::ROLE_ASSISTANT,
        ])->where('status', 'active')->orderBy('name')->get();

        return view('prescriptions.index', compact(
            'prescriptions',
            'totalCount',
            'todayCount',
            'thisMonthCount',
            'doctors'
        ));
    }

    /**
     * Show digital prescription authoring interface (Sec 6, pp. 6-7).
     */
    public function create(Request $request): View
    {
        $selectedPatient = null;
        $consultation = null;
        $appointment = null;

        if ($request->filled('consultation_id')) {
            $consultation = Consultation::with(['patient', 'doctor', 'appointment'])->find($request->input('consultation_id'));
            if ($consultation) {
                $selectedPatient = $consultation->patient;
                $appointment = $consultation->appointment;
            }
        } elseif ($request->filled('appointment_id')) {
            $appointment = Appointment::with(['patient', 'doctor'])->find($request->input('appointment_id'));
            if ($appointment) {
                $selectedPatient = $appointment->patient;
            }
        } elseif ($request->filled('patient_id')) {
            $selectedPatient = Patient::find($request->input('patient_id'));
        }

        $patients = Patient::orderBy('name')->get();
        $doctors = User::whereIn('role', [
            User::ROLE_ADMIN_DOCTOR,
            User::ROLE_DOCTOR,
            User::ROLE_CLINIC_MANAGER,
            User::ROLE_NURSE,
            User::ROLE_ASSISTANT,
        ])->where('status', 'active')->orderBy('name')->get();

        // Practice defaults from Settings
        $defaultInstructions = Setting::get('prescription_default_instructions', 'Take with lukewarm water after meals.');

        return view('prescriptions.create', compact(
            'selectedPatient',
            'consultation',
            'appointment',
            'patients',
            'doctors',
            'defaultInstructions'
        ));
    }

    /**
     * Store newly authored digital prescription and medicine items.
     */
    public function store(Request $request): RedirectResponse
    {
        if (! $request->filled('doctor_id') && auth()->check()) {
            $request->merge(['doctor_id' => auth()->id()]);
        }

        $validated = $request->validate([
            'patient_id' => ['required', 'exists:patients,id'],
            'doctor_id' => [
                'required',
                Rule::exists('users', 'id')->where(fn (QueryBuilder $query) => $query
                    ->whereIn('role', [User::ROLE_ADMIN_DOCTOR, User::ROLE_DOCTOR, User::ROLE_CLINIC_MANAGER, User::ROLE_NURSE, User::ROLE_ASSISTANT])
                    ->where('status', 'active')),
            ],
            'appointment_id' => ['nullable', 'exists:appointments,id'],
            'consultation_id' => ['nullable', 'exists:consultations,id'],
            'prescription_date' => ['required', 'date_format:Y-m-d', 'before_or_equal:today'],
            'diagnosis' => ['nullable', 'string', 'max:1000'],
            'symptoms' => ['nullable', 'string', 'max:1000'],
            'clinical_notes' => ['nullable', 'string', 'max:2000'],
            'tests' => ['nullable', Rule::when(is_array($request->input('tests')), ['array', 'max:30'], ['string', 'max:2000'])],
            'tests.*' => ['required', 'string', 'max:255'],
            'advice' => ['nullable', 'string', 'max:2000'],
            'follow_up_date' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:prescription_date'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.medicine_name' => ['required', 'string', 'max:255'],
            'items.*.dosage' => ['required', 'string', 'max:100'],
            'items.*.frequency' => ['required', 'string', 'max:100'],
            'items.*.duration' => ['required', 'string', 'max:100'],
            'items.*.route' => ['nullable', 'string', 'max:100'],
            'items.*.timing' => ['nullable', 'string', 'max:100'],
            'items.*.instructions' => ['nullable', 'string', 'max:500'],
        ], [
            'patient_id.required' => 'Please select a patient for this prescription.',
            'patient_id.exists' => 'The selected patient does not exist.',
            'doctor_id.required' => 'Please select a prescribing doctor.',
            'doctor_id.exists' => 'The selected doctor is not active or available.',
            'prescription_date.required' => 'Prescription date is required.',
            'prescription_date.before_or_equal' => 'Prescription date cannot be in the future.',
            'follow_up_date.after_or_equal' => 'Follow-up date cannot be earlier than prescription date.',
            'items.required' => 'At least one prescribed medicine is required.',
            'items.min' => 'Please add at least one medication to the prescription.',
            'items.*.medicine_name.required' => 'Medicine name is required for all prescribed items.',
            'items.*.dosage.required' => 'Dosage is required for all prescribed items.',
            'items.*.frequency.required' => 'Frequency (e.g. 1-0-1) is required for all prescribed items.',
            'items.*.duration.required' => 'Duration (e.g. 5 days) is required for all prescribed items.',
        ]);

        if (! empty($validated['appointment_id'])) {
            $appointment = Appointment::findOrFail($validated['appointment_id']);
            if ((int) $appointment->patient_id !== (int) $validated['patient_id']) {
                throw ValidationException::withMessages([
                    'appointment_id' => 'Choose an appointment belonging to the selected patient.',
                ]);
            }
        }

        if (! empty($validated['consultation_id'])) {
            $consultation = Consultation::findOrFail($validated['consultation_id']);
            if ((int) $consultation->patient_id !== (int) $validated['patient_id']) {
                throw ValidationException::withMessages([
                    'consultation_id' => 'Choose a consultation belonging to the selected patient.',
                ]);
            }

            if (! empty($validated['appointment_id']) && (int) $consultation->appointment_id !== (int) $validated['appointment_id']) {
                throw ValidationException::withMessages([
                    'appointment_id' => 'Choose the appointment linked to the selected consultation.',
                ]);
            }
        }

        $tests = $request->input('tests');
        if (is_string($tests)) {
            $tests = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n|,/', $tests))));
        }

        $prescription = Prescription::create([
            'patient_id' => $validated['patient_id'],
            'doctor_id' => $validated['doctor_id'],
            'appointment_id' => $validated['appointment_id'] ?? null,
            'consultation_id' => $validated['consultation_id'] ?? null,
            'prescription_date' => $validated['prescription_date'],
            'diagnosis' => $validated['diagnosis'] ?? null,
            'symptoms' => $validated['symptoms'] ?? null,
            'clinical_notes' => $validated['clinical_notes'] ?? null,
            'tests' => empty($tests) ? null : $tests,
            'advice' => $validated['advice'] ?? null,
            'follow_up_date' => $validated['follow_up_date'] ?? null,
        ]);

        foreach ($validated['items'] as $item) {
            $prescription->items()->create([
                'medicine_name' => $item['medicine_name'],
                'dosage' => $item['dosage'],
                'frequency' => $item['frequency'],
                'duration' => $item['duration'],
                'route' => $item['route'] ?? 'Oral',
                'timing' => $item['timing'] ?? 'After food',
                'instructions' => $item['instructions'] ?? null,
            ]);
        }

        PracticeActivityNotification::sendToActiveStaff([
            'category' => 'clinical',
            'title' => 'Prescription issued',
            'message' => 'Prescription '.$prescription->prescription_number.' was created for '.$prescription->patient->name.'.',
            'url' => route('prescriptions.show', $prescription),
            'permission' => 'prescriptions.view',
        ]);

        return redirect()->route('prescriptions.show', $prescription)
            ->with('success', "Prescription {$prescription->prescription_number} generated successfully.");
    }

    /**
     * Display a specific prescription (Sec 6, p. 6).
     */
    public function show(Prescription $prescription): View
    {
        $prescription->load(['patient', 'doctor', 'appointment', 'consultation', 'items']);
        $settings = Setting::allKeyValues();

        return view('prescriptions.show', compact('prescription', 'settings'));
    }

    /**
     * Printable prescription sheet layout with Clinic Letterhead (Sec 6, p. 7).
     */
    public function print(Prescription $prescription): View
    {
        $prescription->load(['patient', 'doctor', 'items']);
        $settings = Setting::allKeyValues();

        return view('prescriptions.print', compact('prescription', 'settings'));
    }

    /**
     * Show form to edit an existing prescription.
     */
    public function edit(Prescription $prescription): View
    {
        $prescription->load(['patient', 'doctor', 'items']);
        $doctors = User::whereIn('role', [
            User::ROLE_ADMIN_DOCTOR,
            User::ROLE_DOCTOR,
            User::ROLE_CLINIC_MANAGER,
            User::ROLE_NURSE,
            User::ROLE_ASSISTANT,
        ])->where('status', 'active')->orderBy('name')->get();

        return view('prescriptions.edit', compact('prescription', 'doctors'));
    }

    /**
     * Update prescription and items.
     */
    public function update(Request $request, Prescription $prescription): RedirectResponse
    {
        if (! $request->filled('doctor_id')) {
            $request->merge(['doctor_id' => $prescription->doctor_id ?? auth()->id()]);
        }

        $validated = $request->validate([
            'doctor_id' => [
                'required',
                Rule::exists('users', 'id')->where(fn (QueryBuilder $query) => $query
                    ->whereIn('role', [User::ROLE_ADMIN_DOCTOR, User::ROLE_DOCTOR, User::ROLE_CLINIC_MANAGER, User::ROLE_NURSE, User::ROLE_ASSISTANT])
                    ->where('status', 'active')),
            ],
            'prescription_date' => ['required', 'date_format:Y-m-d', 'before_or_equal:today'],
            'diagnosis' => ['nullable', 'string', 'max:1000'],
            'symptoms' => ['nullable', 'string', 'max:1000'],
            'clinical_notes' => ['nullable', 'string', 'max:2000'],
            'tests' => ['nullable', Rule::when(is_array($request->input('tests')), ['array', 'max:30'], ['string', 'max:2000'])],
            'tests.*' => ['required', 'string', 'max:255'],
            'advice' => ['nullable', 'string', 'max:2000'],
            'follow_up_date' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:prescription_date'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.medicine_name' => ['required', 'string', 'max:255'],
            'items.*.dosage' => ['required', 'string', 'max:100'],
            'items.*.frequency' => ['required', 'string', 'max:100'],
            'items.*.duration' => ['required', 'string', 'max:100'],
            'items.*.route' => ['nullable', 'string', 'max:100'],
            'items.*.timing' => ['nullable', 'string', 'max:100'],
            'items.*.instructions' => ['nullable', 'string', 'max:500'],
        ]);

        $tests = $request->input('tests');
        if (is_string($tests)) {
            $tests = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n|,/', $tests))));
        }

        $prescription->update([
            'doctor_id' => $validated['doctor_id'],
            'prescription_date' => $validated['prescription_date'],
            'diagnosis' => $validated['diagnosis'] ?? null,
            'symptoms' => $validated['symptoms'] ?? null,
            'clinical_notes' => $validated['clinical_notes'] ?? null,
            'tests' => empty($tests) ? null : $tests,
            'advice' => $validated['advice'] ?? null,
            'follow_up_date' => $validated['follow_up_date'] ?? null,
        ]);

        // Recreate items
        $prescription->items()->delete();
        foreach ($validated['items'] as $item) {
            $prescription->items()->create([
                'medicine_name' => $item['medicine_name'],
                'dosage' => $item['dosage'],
                'frequency' => $item['frequency'],
                'duration' => $item['duration'],
                'route' => $item['route'] ?? 'Oral',
                'timing' => $item['timing'] ?? 'After food',
                'instructions' => $item['instructions'] ?? null,
            ]);
        }

        return redirect()->route('prescriptions.show', $prescription)
            ->with('success', "Prescription {$prescription->prescription_number} updated successfully.");
    }

    /**
     * Remove digital prescription.
     */
    public function destroy(Request $request, Prescription $prescription): RedirectResponse
    {
        $patientId = $prescription->patient_id;
        $prescription->delete();

        if ($request->header('referer') && str_contains($request->header('referer'), 'patients')) {
            return redirect()->route('patients.show', ['patient' => $patientId, 'tab' => 'prescriptions'])
                ->with('success', 'Prescription removed.');
        }

        return redirect()->route('prescriptions.index')
            ->with('success', 'Prescription removed successfully.');
    }
}

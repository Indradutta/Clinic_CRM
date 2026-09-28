<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\PatientDocument;
use App\Models\PatientNote;
use App\Notifications\PracticeActivityNotification;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PatientController extends Controller
{
    /**
     * Display a listing of patients with search and multi-criteria filters (Sec 5, pp. 5-6).
     */
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'gender' => ['nullable', Rule::in(['Male', 'Female', 'Other'])],
            'min_age' => ['nullable', 'integer', 'min:0', 'max:130'],
            'max_age' => ['nullable', 'integer', 'min:0', 'max:130', Rule::when($request->filled('min_age'), ['gte:min_age'])],
            'from_date' => ['nullable', 'date_format:Y-m-d'],
            'to_date' => ['nullable', 'date_format:Y-m-d', Rule::when($request->filled('from_date'), ['after_or_equal:from_date'])],
            'blood_group' => ['nullable', Rule::in(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-', 'Unknown'])],
        ]);

        $query = Patient::query();

        // Search by: Patient ID, Name, Phone, Email (Sec 5, p. 6)
        if (! empty($filters['search'])) {
            $search = trim($filters['search']);
            $query->where(function ($q) use ($search) {
                $q->where('patient_id', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by: Gender (Sec 5, p. 6)
        if (! empty($filters['gender'])) {
            $query->where('gender', $filters['gender']);
        }

        // Filter by: Age range (Sec 5, p. 6)
        if (isset($filters['min_age'])) {
            $query->where('age', '>=', (int) $filters['min_age']);
        }
        if (isset($filters['max_age'])) {
            $query->where('age', '<=', (int) $filters['max_age']);
        }

        // Filter by: Registration Date (Sec 5, p. 6)
        if (! empty($filters['from_date'])) {
            $query->whereDate('created_at', '>=', $filters['from_date']);
        }
        if (! empty($filters['to_date'])) {
            $query->whereDate('created_at', '<=', $filters['to_date']);
        }

        // Filter by: Blood Group
        if (! empty($filters['blood_group'])) {
            $query->where('blood_group', $filters['blood_group']);
        }

        $patients = $query->latest()->paginate(12)->withQueryString();

        return view('patients.index', compact('patients'));
    }

    /**
     * Show the form for creating a new patient record (Sec 5, p. 5).
     */
    public function create(): View
    {
        return view('patients.create');
    }

    /**
     * Store a newly created patient with all 18 basic and medical fields (Sec 5, p. 5).
     */
    public function store(Request $request): RedirectResponse
    {
        $this->normalizeEmail($request);
        $validated = $request->validate([
            // Basic Information (11 fields)
            'name' => ['required', 'string', 'max:255'],
            'dob' => ['nullable', 'date_format:Y-m-d', 'before_or_equal:today'],
            'age' => ['nullable', 'integer', 'min:0', 'max:130'],
            'gender' => ['required', 'string', Rule::in(['Male', 'Female', 'Other'])],
            'phone' => ['required', 'regex:/^[6-9][0-9]{9}$/'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:2000'],
            'primary_concern' => ['nullable', 'string', 'max:3000'],
            'emergency_contact_name' => ['nullable', 'string', 'max:255'],
            'emergency_contact_phone' => ['nullable', 'regex:/^[6-9][0-9]{9}$/'],
            'blood_group' => ['nullable', Rule::in(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-', 'Unknown'])],
            'marital_status' => ['nullable', 'string', 'max:30'],
            'preferred_language' => ['nullable', 'string', 'max:80'],
            'height_cm' => ['nullable', 'numeric', 'min:0.1', 'max:300'],
            'weight_kg' => ['nullable', 'numeric', 'min:0.1', 'max:700'],

            // Medical Information (7 fields)
            'allergies' => ['nullable', 'string', 'max:5000'],
            'chronic_conditions' => ['nullable', 'string', 'max:5000'],
            'current_medications' => ['nullable', 'string', 'max:5000'],
            'past_surgeries' => ['nullable', 'string', 'max:5000'],
            'family_history' => ['nullable', 'string', 'max:5000'],
            'smoking_habits' => ['nullable', 'string', 'max:100'],
            'alcohol_consumption' => ['nullable', 'string', 'max:100'],
        ], [
            'name.required' => 'Patient full name is required.',
            'gender.required' => 'Please select patient gender.',
            'phone.required' => 'Phone number is required.',
            'phone.regex' => 'Please enter a valid 10-digit mobile number starting with 6, 7, 8, or 9 (e.g. 98765 43210).',
            'emergency_contact_phone.regex' => 'Emergency contact phone must be a valid 10-digit mobile number starting with 6, 7, 8, or 9.',
            'dob.before_or_equal' => 'Date of birth cannot be in the future. Please select today or an earlier date.',
            'dob.date_format' => 'Date of birth must be a valid date in YYYY-MM-DD format.',
            'age.min' => 'Age cannot be negative.',
            'age.max' => 'Age must be 130 or below.',
            'email.email' => 'Please enter a valid email address (e.g. name@example.com).',
            'height_cm.min' => 'Height must be a positive number.',
            'height_cm.max' => 'Height cannot exceed 300 cm.',
            'weight_kg.min' => 'Weight must be a positive number.',
            'weight_kg.max' => 'Weight cannot exceed 700 kg.',
        ]);

        // If age is empty but DOB provided, auto-calculate age
        if (! empty($validated['dob'])) {
            $calculatedAge = Carbon::parse($validated['dob'])->age;
            if (isset($validated['age']) && (int) $validated['age'] !== $calculatedAge) {
                throw ValidationException::withMessages(['age' => 'Age must match the date of birth.']);
            }
            $validated['age'] = $calculatedAge;
        }

        $patient = Patient::create($validated);

        PracticeActivityNotification::sendToActiveStaff([
            'category' => 'patients',
            'title' => 'New patient registered',
            'message' => $patient->name.' was added to the patient directory.',
            'url' => route('patients.show', $patient),
            'permission' => 'patients.view',
        ]);

        return redirect()->route('patients.show', $patient)
            ->with('success', "Patient record for '{$patient->name}' ({$patient->patient_id}) created successfully.");
    }

    /**
     * Display the 9-Section Patient Profile Hub (Sec 5, pp. 5-6).
     */
    public function show(Patient $patient, Request $request): View
    {
        $activeTab = $request->query('tab', 'personal');

        $patient->load([
            'notes.author',
            'documents.uploader',
            'appointments.doctor',
            'consultations.doctor',
            'prescriptions.items',
            'prescriptions.doctor',
            'invoices.items',
            'invoices.payments',
            'invoices.doctor',
            'payments.invoice',
        ]);

        return view('patients.show', compact('patient', 'activeTab'));
    }

    /**
     * Show the form for editing the patient record.
     */
    public function edit(Patient $patient): View
    {
        return view('patients.edit', compact('patient'));
    }

    /**
     * Update the patient record in storage.
     */
    public function update(Request $request, Patient $patient): RedirectResponse
    {
        $this->normalizeEmail($request);
        $validated = $request->validate([
            // Basic Information
            'name' => ['required', 'string', 'max:255'],
            'dob' => ['nullable', 'date_format:Y-m-d', 'before_or_equal:today'],
            'age' => ['nullable', 'integer', 'min:0', 'max:130'],
            'gender' => ['required', 'string', Rule::in(['Male', 'Female', 'Other'])],
            'phone' => ['required', 'regex:/^[6-9][0-9]{9}$/'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:2000'],
            'primary_concern' => ['nullable', 'string', 'max:3000'],
            'emergency_contact_name' => ['nullable', 'string', 'max:255'],
            'emergency_contact_phone' => ['nullable', 'regex:/^[6-9][0-9]{9}$/'],
            'blood_group' => ['nullable', Rule::in(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-', 'Unknown'])],
            'marital_status' => ['nullable', 'string', 'max:30'],
            'preferred_language' => ['nullable', 'string', 'max:80'],
            'height_cm' => ['nullable', 'numeric', 'min:0.1', 'max:300'],
            'weight_kg' => ['nullable', 'numeric', 'min:0.1', 'max:700'],

            // Medical Information
            'allergies' => ['nullable', 'string', 'max:5000'],
            'chronic_conditions' => ['nullable', 'string', 'max:5000'],
            'current_medications' => ['nullable', 'string', 'max:5000'],
            'past_surgeries' => ['nullable', 'string', 'max:5000'],
            'family_history' => ['nullable', 'string', 'max:5000'],
            'smoking_habits' => ['nullable', 'string', 'max:100'],
            'alcohol_consumption' => ['nullable', 'string', 'max:100'],
        ], [
            'name.required' => 'Patient full name is required.',
            'gender.required' => 'Please select patient gender.',
            'phone.required' => 'Phone number is required.',
            'phone.regex' => 'Please enter a valid 10-digit mobile number starting with 6, 7, 8, or 9 (e.g. 98765 43210).',
            'emergency_contact_phone.regex' => 'Emergency contact phone must be a valid 10-digit mobile number starting with 6, 7, 8, or 9.',
            'dob.before_or_equal' => 'Date of birth cannot be in the future. Please select today or an earlier date.',
            'dob.date_format' => 'Date of birth must be a valid date in YYYY-MM-DD format.',
            'age.min' => 'Age cannot be negative.',
            'age.max' => 'Age must be 130 or below.',
            'email.email' => 'Please enter a valid email address (e.g. name@example.com).',
            'height_cm.min' => 'Height must be a positive number.',
            'height_cm.max' => 'Height cannot exceed 300 cm.',
            'weight_kg.min' => 'Weight must be a positive number.',
            'weight_kg.max' => 'Weight cannot exceed 700 kg.',
        ]);

        if (! empty($validated['dob'])) {
            $calculatedAge = Carbon::parse($validated['dob'])->age;
            if (isset($validated['age']) && (int) $validated['age'] !== $calculatedAge) {
                throw ValidationException::withMessages(['age' => 'Age must match the date of birth.']);
            }
            $validated['age'] = $calculatedAge;
        }

        $patient->update($validated);

        return redirect()->route('patients.show', $patient)
            ->with('success', "Patient record for '{$patient->name}' updated successfully.");
    }

    /**
     * Soft-delete the patient record.
     */
    public function destroy(Patient $patient): RedirectResponse
    {
        $name = $patient->name;
        $patient->delete();

        return redirect()->route('patients.index')
            ->with('success', "Patient record for '{$name}' was archived.");
    }

    /**
     * Store a clinical/administrative note for the patient (Sec 5, p. 5, Section 8 Notes).
     */
    public function storeNote(Request $request, Patient $patient): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'note' => ['required', 'string'],
        ]);

        $patient->notes()->create([
            'user_id' => auth()->id(),
            'title' => $validated['title'] ?? 'Clinical Note',
            'note' => $validated['note'],
        ]);

        return redirect()->route('patients.show', ['patient' => $patient, 'tab' => 'notes'])
            ->with('success', 'Clinical note added successfully.');
    }

    /**
     * Remove a patient note.
     */
    public function destroyNote(PatientNote $note): RedirectResponse
    {
        $patientId = $note->patient_id;
        $note->delete();

        return redirect()->route('patients.show', ['patient' => $patientId, 'tab' => 'notes'])
            ->with('success', 'Note removed.');
    }

    /**
     * Upload a medical document or report for the patient (Sec 5, p. 5, Section 9 Documents).
     */
    public function storeDocument(Request $request, Patient $patient): RedirectResponse
    {
        $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'document' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png,docx,txt', 'max:10240'],
        ]);

        $file = $request->file('document');
        $path = $file->store("patient_documents/{$patient->id}", 'local');

        $patient->documents()->create([
            'user_id' => auth()->id(),
            'title' => $request->input('title'),
            'file_path' => $path,
            'file_type' => $file->getClientOriginalExtension(),
            'file_size' => $file->getSize(),
        ]);

        return redirect()->route('patients.show', ['patient' => $patient, 'tab' => 'documents'])
            ->with('success', 'Medical document uploaded successfully.');
    }

    /**
     * Securely download or stream an attached patient document.
     */
    public function downloadDocument(PatientDocument $document): StreamedResponse|RedirectResponse
    {
        if (! Storage::disk('local')->exists($document->file_path)) {
            return back()->with('error', 'The requested file does not exist on the server.');
        }

        return Storage::disk('local')->download($document->file_path, "{$document->title}.{$document->file_type}");
    }

    /**
     * Delete an attached patient document.
     */
    public function destroyDocument(PatientDocument $document): RedirectResponse
    {
        $patientId = $document->patient_id;

        if (Storage::disk('local')->exists($document->file_path)) {
            Storage::disk('local')->delete($document->file_path);
        }

        $document->delete();

        return redirect()->route('patients.show', ['patient' => $patientId, 'tab' => 'documents'])
            ->with('success', 'Medical attachment removed.');
    }

    private function normalizeEmail(Request $request): void
    {
        $email = $request->input('email');
        if (is_string($email)) {
            $request->merge(['email' => Str::lower(trim($email))]);
        }
    }
}

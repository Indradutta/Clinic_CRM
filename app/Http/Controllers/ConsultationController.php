<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Consultation;
use App\Models\Patient;
use App\Models\User;
use App\Notifications\PracticeActivityNotification;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ConsultationController extends Controller
{
    /**
     * Show consultation encounter intake form (Sec 12, pp. 14-15 & Sec 16, p. 17).
     */
    public function create(Request $request): View
    {
        $appointment = null;
        $patient = null;

        if ($request->filled('appointment_id')) {
            $appointment = Appointment::with(['patient', 'doctor'])->findOrFail($request->input('appointment_id'));
            $patient = $appointment->patient;

        } elseif ($request->filled('patient_id')) {
            $patient = Patient::findOrFail($request->input('patient_id'));
        }

        $patients = Patient::orderBy('name')->get();
        $doctors = User::whereIn('role', [
            User::ROLE_ADMIN_DOCTOR,
            User::ROLE_DOCTOR,
            User::ROLE_CLINIC_MANAGER,
            User::ROLE_NURSE,
            User::ROLE_ASSISTANT,
        ])->where('status', 'active')->orderBy('name')->get();

        // Load clinical history if patient is selected
        $previousConsultations = collect();
        $previousPrescriptions = collect();
        if ($patient) {
            $previousConsultations = $patient->consultations()->with('doctor')->take(5)->get();
            $previousPrescriptions = $patient->prescriptions()->with('items')->take(5)->get();
        }

        return view('consultations.create', compact(
            'appointment',
            'patient',
            'patients',
            'doctors',
            'previousConsultations',
            'previousPrescriptions'
        ));
    }

    /**
     * Store clinical consultation encounter (Sec 12, p. 14).
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
            'consultation_date' => ['required', 'date_format:Y-m-d', 'before_or_equal:today'],
            'symptoms' => ['required', 'string', 'max:2000'],
            'diagnosis' => ['required', 'string', 'max:2000'],
            'medical_notes' => ['nullable', 'string', 'max:5000'],
            'treatment' => ['nullable', 'string', 'max:5000'],
            'vital_bp' => ['nullable', 'regex:/^\d{2,3}\/\d{2,3}$/'],
            'vital_pulse' => ['nullable', 'numeric', 'min:20', 'max:300'],
            'vital_temperature' => ['nullable', 'numeric', 'min:77', 'max:113'],
            'vital_spo2' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'vital_weight' => ['nullable', 'numeric', 'min:0.1', 'max:700'],
            'vital_height' => ['nullable', 'numeric', 'min:20', 'max:300'],
            'vitals' => ['nullable', 'array'],
            'vitals.blood_pressure' => ['nullable', 'regex:/^\d{2,3}\/\d{2,3}$/'],
            'vitals.bp' => ['nullable', 'regex:/^\d{2,3}\/\d{2,3}$/'],
            'vitals.heart_rate' => ['nullable', 'numeric', 'min:20', 'max:300'],
            'vitals.pulse' => ['nullable', 'numeric', 'min:20', 'max:300'],
            'vitals.temperature' => ['nullable', 'numeric', 'min:77', 'max:113'],
            'vitals.oxygen_saturation' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'vitals.spo2' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'vitals.weight' => ['nullable', 'numeric', 'min:0.1', 'max:700'],
            'vitals.weight_kg' => ['nullable', 'numeric', 'min:0.1', 'max:700'],
            'vitals.height' => ['nullable', 'numeric', 'min:20', 'max:300'],
            'vitals.height_cm' => ['nullable', 'numeric', 'min:20', 'max:300'],
            'vitals.bmi' => ['nullable', 'numeric', 'min:5', 'max:100'],
            'vitals.respiratory_rate' => ['nullable', 'integer', 'min:1', 'max:100'],
        ], [
            'patient_id.required' => 'Please select a patient for this consultation.',
            'patient_id.exists' => 'The selected patient does not exist.',
            'doctor_id.required' => 'Please select a consulting doctor.',
            'doctor_id.exists' => 'The selected doctor is not active or available.',
            'consultation_date.required' => 'Encounter date is required.',
            'consultation_date.before_or_equal' => 'Encounter date cannot be in the future.',
            'symptoms.required' => 'Please record presenting symptoms and chief complaints.',
            'diagnosis.required' => 'Clinical diagnosis is required.',
            'vital_bp.regex' => 'Blood pressure must be in systolic/diastolic format (e.g. 120/80).',
            'vital_pulse.min' => 'Pulse rate must be at least 20 bpm.',
            'vital_pulse.max' => 'Pulse rate cannot exceed 300 bpm.',
            'vital_temperature.min' => 'Body temperature must be at least 77°F.',
            'vital_temperature.max' => 'Body temperature cannot exceed 113°F.',
            'vital_spo2.min' => 'Oxygen saturation (SpO2) cannot be negative.',
            'vital_spo2.max' => 'Oxygen saturation (SpO2) cannot exceed 100%.',
            'vital_weight.min' => 'Weight must be greater than zero.',
            'vital_height.min' => 'Height must be greater than 20 cm.',
        ]);

        $appointment = null;
        if (! empty($validated['appointment_id'])) {
            $appointment = Appointment::findOrFail($validated['appointment_id']);

            if ((int) $appointment->patient_id !== (int) $validated['patient_id']) {
                throw ValidationException::withMessages([
                    'appointment_id' => 'Choose an appointment belonging to the selected patient.',
                ]);
            }

            if ($appointment->status !== Appointment::STATUS_CHECKED_IN) {
                throw ValidationException::withMessages([
                    'appointment_id' => 'Check in the appointment before recording its consultation.',
                ]);
            }

            if ($appointment->consultation()->exists()) {
                throw ValidationException::withMessages([
                    'appointment_id' => 'A consultation is already recorded for this appointment.',
                ]);
            }
        }

        $vitalsInput = $request->input('vitals', []);
        $vitals = array_filter([
            'blood_pressure' => $request->input('vital_bp') ?? ($vitalsInput['blood_pressure'] ?? ($vitalsInput['bp'] ?? null)),
            'heart_rate' => $request->input('vital_pulse') ?? ($vitalsInput['heart_rate'] ?? ($vitalsInput['pulse'] ?? null)),
            'temperature' => $request->input('vital_temperature') ?? ($vitalsInput['temperature'] ?? null),
            'oxygen_saturation' => $request->input('vital_spo2') ?? ($vitalsInput['oxygen_saturation'] ?? ($vitalsInput['spo2'] ?? null)),
            'weight' => $request->input('vital_weight') ?? ($vitalsInput['weight'] ?? ($vitalsInput['weight_kg'] ?? null)),
            'height' => $request->input('vital_height') ?? ($vitalsInput['height'] ?? ($vitalsInput['height_cm'] ?? null)),
            'bmi' => $vitalsInput['bmi'] ?? null,
            'respiratory_rate' => $vitalsInput['respiratory_rate'] ?? null,
        ]);

        $consultation = Consultation::create([
            'patient_id' => $validated['patient_id'],
            'doctor_id' => $validated['doctor_id'],
            'appointment_id' => $validated['appointment_id'] ?? null,
            'consultation_date' => $validated['consultation_date'],
            'symptoms' => $validated['symptoms'],
            'diagnosis' => $validated['diagnosis'],
            'vitals' => empty($vitals) ? null : $vitals,
            'medical_notes' => $validated['medical_notes'] ?? null,
            'treatment' => $validated['treatment'] ?? null,
        ]);

        if ($appointment) {
            $appointment->update(['status' => Appointment::STATUS_IN_CONSULTATION]);
        }

        PracticeActivityNotification::sendToActiveStaff([
            'category' => 'clinical',
            'title' => 'Consultation recorded',
            'message' => 'A clinical consultation was recorded for '.$consultation->patient->name.'.',
            'url' => route('consultations.show', $consultation),
            'permission' => 'consultations.view',
        ]);

        // Check if doctor requested immediate prescription authoring
        if ($request->input('action') === 'prescribe'
            && $request->user()->can('consultations.view')
            && $request->user()->can('prescriptions.view')
            && $request->user()->can('prescriptions.create')) {
            return redirect()->route('prescriptions.create', [
                'consultation_id' => $consultation->id,
                'patient_id' => $consultation->patient_id,
                'appointment_id' => $consultation->appointment_id,
            ])->with('success', 'Consultation encounter recorded. Author digital prescription below.');
        }

        return redirect()->route('consultations.show', $consultation)
            ->with('success', 'Clinical consultation recorded successfully.');
    }

    /**
     * Display consultation encounter details.
     */
    public function show(Consultation $consultation): View
    {
        $consultation->load(['patient', 'doctor', 'appointment', 'prescription.items']);

        return view('consultations.show', compact('consultation'));
    }

    /**
     * Show edit form for consultation.
     */
    public function edit(Consultation $consultation): View
    {
        $consultation->load(['patient', 'doctor', 'appointment']);
        $doctors = User::whereIn('role', [
            User::ROLE_ADMIN_DOCTOR,
            User::ROLE_DOCTOR,
            User::ROLE_CLINIC_MANAGER,
            User::ROLE_NURSE,
            User::ROLE_ASSISTANT,
        ])->where('status', 'active')->orderBy('name')->get();

        return view('consultations.edit', compact('consultation', 'doctors'));
    }

    /**
     * Update consultation record.
     */
    public function update(Request $request, Consultation $consultation): RedirectResponse
    {
        if (! $request->filled('doctor_id')) {
            $request->merge(['doctor_id' => $consultation->doctor_id ?? auth()->id()]);
        }
        if (! $request->filled('symptoms')) {
            $request->merge(['symptoms' => $consultation->symptoms]);
        }
        if (! $request->filled('consultation_date')) {
            $request->merge(['consultation_date' => $consultation->consultation_date]);
        }

        $validated = $request->validate([
            'doctor_id' => [
                'required',
                Rule::exists('users', 'id')->where(fn (QueryBuilder $query) => $query
                    ->whereIn('role', [User::ROLE_ADMIN_DOCTOR, User::ROLE_DOCTOR, User::ROLE_CLINIC_MANAGER, User::ROLE_NURSE, User::ROLE_ASSISTANT])
                    ->where('status', 'active')),
            ],
            'consultation_date' => ['required', 'date_format:Y-m-d', 'before_or_equal:today'],
            'symptoms' => ['required', 'string', 'max:2000'],
            'diagnosis' => ['required', 'string', 'max:2000'],
            'medical_notes' => ['nullable', 'string', 'max:5000'],
            'treatment' => ['nullable', 'string', 'max:5000'],
            'vital_bp' => ['nullable', 'regex:/^\d{2,3}\/\d{2,3}$/'],
            'vital_pulse' => ['nullable', 'numeric', 'min:20', 'max:300'],
            'vital_temperature' => ['nullable', 'numeric', 'min:77', 'max:113'],
            'vital_spo2' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'vital_weight' => ['nullable', 'numeric', 'min:0.1', 'max:700'],
            'vital_height' => ['nullable', 'numeric', 'min:20', 'max:300'],
            'vitals' => ['nullable', 'array'],
            'vitals.blood_pressure' => ['nullable', 'regex:/^\d{2,3}\/\d{2,3}$/'],
            'vitals.bp' => ['nullable', 'regex:/^\d{2,3}\/\d{2,3}$/'],
            'vitals.heart_rate' => ['nullable', 'numeric', 'min:20', 'max:300'],
            'vitals.pulse' => ['nullable', 'numeric', 'min:20', 'max:300'],
            'vitals.temperature' => ['nullable', 'numeric', 'min:77', 'max:113'],
            'vitals.oxygen_saturation' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'vitals.spo2' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'vitals.weight' => ['nullable', 'numeric', 'min:0.1', 'max:700'],
            'vitals.weight_kg' => ['nullable', 'numeric', 'min:0.1', 'max:700'],
            'vitals.height' => ['nullable', 'numeric', 'min:20', 'max:300'],
            'vitals.height_cm' => ['nullable', 'numeric', 'min:20', 'max:300'],
            'vitals.bmi' => ['nullable', 'numeric', 'min:5', 'max:100'],
            'vitals.respiratory_rate' => ['nullable', 'integer', 'min:1', 'max:100'],
        ], [
            'consultation_date.required' => 'Encounter date is required.',
            'consultation_date.before_or_equal' => 'Encounter date cannot be in the future.',
            'symptoms.required' => 'Please record presenting symptoms and chief complaints.',
            'diagnosis.required' => 'Clinical diagnosis is required.',
            'vital_bp.regex' => 'Blood pressure must be in systolic/diastolic format (e.g. 120/80).',
            'vital_pulse.min' => 'Pulse rate must be at least 20 bpm.',
            'vital_pulse.max' => 'Pulse rate cannot exceed 300 bpm.',
            'vital_temperature.min' => 'Body temperature must be at least 77°F.',
            'vital_temperature.max' => 'Body temperature cannot exceed 113°F.',
            'vital_spo2.min' => 'Oxygen saturation (SpO2) cannot be negative.',
            'vital_spo2.max' => 'Oxygen saturation (SpO2) cannot exceed 100%.',
            'vital_weight.min' => 'Weight must be greater than zero.',
            'vital_height.min' => 'Height must be greater than 20 cm.',
        ]);

        $vitalsInput = $request->input('vitals', []);
        $vitals = array_filter([
            'blood_pressure' => $request->input('vital_bp') ?? ($vitalsInput['blood_pressure'] ?? ($vitalsInput['bp'] ?? ($consultation->vitals['blood_pressure'] ?? null))),
            'heart_rate' => $request->input('vital_pulse') ?? ($vitalsInput['heart_rate'] ?? ($vitalsInput['pulse'] ?? ($consultation->vitals['heart_rate'] ?? null))),
            'temperature' => $request->input('vital_temperature') ?? ($vitalsInput['temperature'] ?? ($consultation->vitals['temperature'] ?? null)),
            'oxygen_saturation' => $request->input('vital_spo2') ?? ($vitalsInput['oxygen_saturation'] ?? ($vitalsInput['spo2'] ?? ($consultation->vitals['oxygen_saturation'] ?? null))),
            'weight' => $request->input('vital_weight') ?? ($vitalsInput['weight'] ?? ($vitalsInput['weight_kg'] ?? ($consultation->vitals['weight'] ?? null))),
            'height' => $request->input('vital_height') ?? ($vitalsInput['height'] ?? ($vitalsInput['height_cm'] ?? ($consultation->vitals['height'] ?? null))),
            'bmi' => $vitalsInput['bmi'] ?? ($consultation->vitals['bmi'] ?? null),
        ]);

        $consultation->update([
            'doctor_id' => $validated['doctor_id'],
            'consultation_date' => $validated['consultation_date'],
            'symptoms' => $validated['symptoms'],
            'diagnosis' => $validated['diagnosis'],
            'vitals' => empty($vitals) ? $consultation->vitals : $vitals,
            'medical_notes' => $validated['medical_notes'] ?? null,
            'treatment' => $validated['treatment'] ?? null,
        ]);

        return redirect()->route('consultations.show', $consultation)
            ->with('success', 'Consultation encounter updated successfully.');
    }

    /**
     * Remove consultation.
     */
    public function destroy(Consultation $consultation): RedirectResponse
    {
        $patientId = $consultation->patient_id;
        $consultation->delete();

        return redirect()->route('patients.show', ['patient' => $patientId, 'tab' => 'consultations'])
            ->with('success', 'Consultation record removed.');
    }
}

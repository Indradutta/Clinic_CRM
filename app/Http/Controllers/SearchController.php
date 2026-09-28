<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Invoice;
use App\Models\Patient;
use App\Models\Prescription;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SearchController extends Controller
{
    /**
     * Global Relational Search across Patients, Appointments, Prescriptions, and Invoices (Sec 11, p. 14).
     */
    public function index(Request $request): View
    {
        $validated = $request->validate(['q' => ['nullable', 'string', 'max:100']]);
        $query = trim($validated['q'] ?? '');

        $patients = collect();
        $appointments = collect();
        $prescriptions = collect();
        $invoices = collect();

        if (strlen($query) >= 1) {
            // 1. Search Patients by name, phone, email, patient_id
            $patients = Patient::with(['appointments', 'invoices'])
                ->where(function ($patientQuery) use ($query): void {
                    $patientQuery->where('name', 'like', "%{$query}%")
                        ->orWhere('phone', 'like', "%{$query}%")
                        ->orWhere('email', 'like', "%{$query}%")
                        ->orWhere('patient_id', 'like', "%{$query}%");
                })
                ->take(10)
                ->get();

            // 2. Search Appointments by ID, patient name, or visit reason
            $appointments = Appointment::with(['patient', 'doctor'])
                ->where('appointment_id', 'like', "%{$query}%")
                ->orWhere('reason_for_visit', 'like', "%{$query}%")
                ->orWhereHas('patient', fn ($q) => $q->where('name', 'like', "%{$query}%")->orWhere('phone', 'like', "%{$query}%"))
                ->latest('appointment_date')
                ->take(10)
                ->get();

            // 3. Search Prescriptions by number, diagnosis, symptoms, or patient name
            $prescriptions = Prescription::with(['patient', 'doctor', 'items'])
                ->where('prescription_number', 'like', "%{$query}%")
                ->orWhere('diagnosis', 'like', "%{$query}%")
                ->orWhereHas('patient', fn ($q) => $q->where('name', 'like', "%{$query}%"))
                ->latest('prescription_date')
                ->take(10)
                ->get();

            // 4. Search Invoices by number or patient name
            $invoices = Invoice::with(['patient', 'doctor'])
                ->where('invoice_number', 'like', "%{$query}%")
                ->orWhereHas('patient', fn ($q) => $q->where('name', 'like', "%{$query}%"))
                ->latest('invoice_date')
                ->take(10)
                ->get();
        }

        $totalMatches = $patients->count() + $appointments->count() + $prescriptions->count() + $invoices->count();

        return view('search.index', compact('query', 'patients', 'appointments', 'prescriptions', 'invoices', 'totalMatches'));
    }
}

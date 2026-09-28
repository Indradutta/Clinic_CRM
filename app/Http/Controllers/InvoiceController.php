<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Invoice;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\Prescription;
use App\Models\Setting;
use App\Models\User;
use App\Notifications\PracticeActivityNotification;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    /**
     * Display listing of invoices with search, filters, and financial KPI stats (Sec 7, pp. 7-8).
     */
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::in(['all', Invoice::STATUS_UNPAID, Invoice::STATUS_PARTIALLY_PAID, Invoice::STATUS_PAID, Invoice::STATUS_OVERDUE, Invoice::STATUS_CANCELLED])],
            'date_preset' => ['nullable', Rule::in(['today', 'this_week', 'this_month'])],
            'from_date' => ['nullable', 'date_format:Y-m-d', Rule::when($request->filled('to_date'), ['before_or_equal:to_date'])],
            'to_date' => ['nullable', 'date_format:Y-m-d', Rule::when($request->filled('from_date'), ['after_or_equal:from_date'])],
            'doctor_id' => ['nullable', 'integer', 'exists:users,id'],
        ]);
        $query = Invoice::with(['patient', 'doctor', 'items', 'payments'])->latest('invoice_date');

        // Search: Invoice #, Patient Name, Patient ID, Phone
        if (! empty($filters['search'])) {
            $search = trim($filters['search']);
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                    ->orWhereHas('patient', function ($pq) use ($search) {
                        $pq->where('name', 'like', "%{$search}%")
                            ->orWhere('patient_id', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    });
            });
        }

        // Status Filter
        if (! empty($filters['status']) && $filters['status'] !== 'all') {
            $query->where('status', $filters['status']);
        }

        // Date Preset Filter
        $dateFilter = $filters['date_preset'] ?? null;
        if ($dateFilter === 'today') {
            $query->whereDate('invoice_date', now()->toDateString());
        } elseif ($dateFilter === 'this_week') {
            $query->whereBetween('invoice_date', [now()->startOfWeek()->toDateString(), now()->endOfWeek()->toDateString()]);
        } elseif ($dateFilter === 'this_month') {
            $query->whereMonth('invoice_date', now()->month)->whereYear('invoice_date', now()->year);
        }

        // Custom Date Range
        if (! empty($filters['from_date'])) {
            $query->whereDate('invoice_date', '>=', $filters['from_date']);
        }
        if (! empty($filters['to_date'])) {
            $query->whereDate('invoice_date', '<=', $filters['to_date']);
        }

        // Doctor Filter
        if (! empty($filters['doctor_id'])) {
            $query->where('doctor_id', $filters['doctor_id']);
        }

        $invoices = $query->paginate(15)->withQueryString();

        // Financial KPIs
        $totalInvoiced = Invoice::where('status', '!=', Invoice::STATUS_CANCELLED)->sum('grand_total');
        $totalReceived = Invoice::where('status', '!=', Invoice::STATUS_CANCELLED)->sum('paid_amount');
        $totalOutstanding = Invoice::where('status', '!=', Invoice::STATUS_CANCELLED)->sum('balance_due');
        $overdueCount = Invoice::where('status', Invoice::STATUS_OVERDUE)->count();

        $doctors = User::whereIn('role', [
            User::ROLE_ADMIN_DOCTOR,
            User::ROLE_DOCTOR,
            User::ROLE_CLINIC_MANAGER,
        ])->where('status', 'active')->orderBy('name')->get();

        return view('invoices.index', compact(
            'invoices',
            'totalInvoiced',
            'totalReceived',
            'totalOutstanding',
            'overdueCount',
            'doctors'
        ));
    }

    /**
     * Show form for creating a new invoice (Sec 7, p. 8).
     */
    public function create(Request $request): View
    {
        $selectedPatient = null;
        $appointment = null;
        $prescription = null;

        if ($request->filled('appointment_id')) {
            $appointment = Appointment::with(['patient', 'doctor'])->find($request->input('appointment_id'));
            if ($appointment) {
                $selectedPatient = $appointment->patient;
            }
        } elseif ($request->filled('prescription_id')) {
            $prescription = Prescription::with(['patient', 'doctor', 'items'])->find($request->input('prescription_id'));
            if ($prescription) {
                $selectedPatient = $prescription->patient;
            }
        } elseif ($request->filled('patient_id')) {
            $selectedPatient = Patient::find($request->input('patient_id'));
        }

        $patients = Patient::orderBy('name')->get();
        $doctors = User::whereIn('role', [
            User::ROLE_ADMIN_DOCTOR,
            User::ROLE_DOCTOR,
            User::ROLE_CLINIC_MANAGER,
        ])->where('status', 'active')->orderBy('name')->get();

        $defaultTerms = Setting::get('invoice_terms', 'Payment is due within 7 days of invoice issue date. Thank you for choosing our practice.');

        return view('invoices.create', compact(
            'selectedPatient',
            'appointment',
            'prescription',
            'patients',
            'doctors',
            'defaultTerms'
        ));
    }

    /**
     * Store a newly created invoice and its line items (Sec 7, p. 8).
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'patient_id' => ['required', 'exists:patients,id'],
            'doctor_id' => ['nullable', Rule::exists('users', 'id')->where(fn (QueryBuilder $query) => $query
                ->whereIn('role', [User::ROLE_ADMIN_DOCTOR, User::ROLE_DOCTOR, User::ROLE_CLINIC_MANAGER])
                ->where('status', 'active'))],
            'appointment_id' => ['nullable', 'exists:appointments,id'],
            'prescription_id' => ['nullable', 'exists:prescriptions,id'],
            'invoice_date' => ['required', 'date_format:Y-m-d', 'before_or_equal:today'],
            'due_date' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:invoice_date'],
            'tax_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'discount_type' => ['required', 'in:fixed,percentage'],
            'discount_value' => ['nullable', 'numeric', 'min:0', 'max:99999999.99', Rule::when($request->input('discount_type') === Invoice::DISCOUNT_PERCENTAGE, ['max:100'])],
            'notes' => ['nullable', 'string', 'max:1000'],
            'terms' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.description' => ['required', 'string', 'max:255'],
            'items.*.item_type' => ['nullable', Rule::in(['consultation', 'procedure', 'medicine', 'lab_test', 'other'])],
            'items.*.quantity' => ['required', 'numeric', 'decimal:0,2', 'min:0.01', 'max:999999.99'],
            'items.*.unit_price' => ['required', 'numeric', 'decimal:0,2', 'min:0', 'max:99999999.99'],
            'initial_payment_amount' => ['nullable', 'numeric', 'decimal:0,2', 'min:0', 'max:99999999.99'],
            'initial_payment_method' => ['nullable', Rule::in([
                Payment::METHOD_CASH,
                Payment::METHOD_CARD,
                Payment::METHOD_UPI,
                Payment::METHOD_BANK_TRANSFER,
                Payment::METHOD_INSURANCE,
                Payment::METHOD_CHEQUE,
            ])],
            'initial_payment_reference' => ['nullable', 'string', 'max:100'],
        ], [
            'patient_id.required' => 'Please select a patient for this invoice.',
            'patient_id.exists' => 'The selected patient does not exist.',
            'invoice_date.required' => 'Invoice date is required.',
            'invoice_date.before_or_equal' => 'Invoice date cannot be a future date.',
            'due_date.after_or_equal' => 'Payment due date cannot be earlier than invoice date.',
            'items.required' => 'At least one itemized service charge is required.',
            'items.min' => 'Please add at least one line item.',
            'items.*.description.required' => 'Every line item must have a description.',
            'items.*.quantity.required' => 'Item quantity is required.',
            'items.*.quantity.min' => 'Item quantity must be greater than zero.',
            'items.*.unit_price.required' => 'Unit price is required.',
            'items.*.unit_price.min' => 'Unit price cannot be negative.',
            'tax_percentage.max' => 'Tax percentage cannot exceed 100%.',
        ]);

        if (! empty($validated['appointment_id'])) {
            $appointment = Appointment::findOrFail($validated['appointment_id']);
            if ((int) $appointment->patient_id !== (int) $validated['patient_id']) {
                throw ValidationException::withMessages([
                    'appointment_id' => 'Choose an appointment belonging to the selected patient.',
                ]);
            }
        }

        if (! empty($validated['prescription_id'])) {
            $prescription = Prescription::findOrFail($validated['prescription_id']);
            if ((int) $prescription->patient_id !== (int) $validated['patient_id']) {
                throw ValidationException::withMessages([
                    'prescription_id' => 'Choose a prescription belonging to the selected patient.',
                ]);
            }
        }

        $subtotal = collect($validated['items'])->sum(fn (array $item): float => (float) $item['quantity'] * (float) $item['unit_price']);
        if ($subtotal > 99999999.99) {
            throw ValidationException::withMessages(['items' => 'The invoice total is too large. Reduce the quantity or unit price.']);
        }
        $discountValue = (float) ($validated['discount_value'] ?? 0);
        $discountAmount = $validated['discount_type'] === Invoice::DISCOUNT_PERCENTAGE
            ? round($subtotal * $discountValue / 100, 2)
            : min($discountValue, $subtotal);
        $taxableSubtotal = max(0, $subtotal - $discountAmount);
        $grandTotal = round($taxableSubtotal + round($taxableSubtotal * (float) ($validated['tax_percentage'] ?? 0) / 100, 2), 2);
        if ($grandTotal > 99999999.99) {
            throw ValidationException::withMessages(['items' => 'The invoice total is too large. Reduce the quantity or unit price.']);
        }

        if ((float) ($validated['initial_payment_amount'] ?? 0) > $grandTotal) {
            throw ValidationException::withMessages([
                'initial_payment_amount' => 'The initial payment cannot exceed the invoice total.',
            ]);
        }

        $invoice = Invoice::create([
            'patient_id' => $validated['patient_id'],
            'doctor_id' => $validated['doctor_id'] ?? null,
            'appointment_id' => $validated['appointment_id'] ?? null,
            'prescription_id' => $validated['prescription_id'] ?? null,
            'invoice_date' => $validated['invoice_date'],
            'due_date' => $validated['due_date'] ?? null,
            'tax_percentage' => $validated['tax_percentage'] ?? 0.00,
            'discount_type' => $validated['discount_type'],
            'discount_value' => $validated['discount_value'] ?? 0.00,
            'notes' => $validated['notes'] ?? null,
            'terms' => $validated['terms'] ?? null,
        ]);

        foreach ($validated['items'] as $item) {
            $qty = (float) $item['quantity'];
            $price = (float) $item['unit_price'];
            $invoice->items()->create([
                'item_type' => $item['item_type'] ?? 'consultation',
                'description' => $item['description'],
                'quantity' => $qty,
                'unit_price' => $price,
                'total' => round($qty * $price, 2),
            ]);
        }

        $invoice->recalculateTotals();

        // Process immediate initial payment if provided
        if (! empty($validated['initial_payment_amount']) && (float) $validated['initial_payment_amount'] > 0) {
            $payment = Payment::create([
                'invoice_id' => $invoice->id,
                'patient_id' => $invoice->patient_id,
                'received_by' => auth()->id(),
                'payment_date' => now(),
                'amount' => (float) $validated['initial_payment_amount'],
                'payment_method' => $validated['initial_payment_method'] ?? Payment::METHOD_CASH,
                'transaction_reference' => $validated['initial_payment_reference'] ?? null,
                'notes' => 'Settled at invoice creation',
            ]);

            PracticeActivityNotification::sendToActiveStaff([
                'category' => 'billing',
                'title' => 'Payment received',
                'message' => '₹'.number_format($payment->amount, 2).' received for invoice '.$invoice->invoice_number.'.',
                'url' => route('invoices.show', $invoice),
                'permission' => 'invoices.view',
            ], 'notify_payment_received');
        }

        PracticeActivityNotification::sendToActiveStaff([
            'category' => 'billing',
            'title' => 'Invoice created',
            'message' => 'Invoice '.$invoice->invoice_number.' was created for '.$invoice->patient->name.'.',
            'url' => route('invoices.show', $invoice),
            'permission' => 'invoices.view',
        ]);

        return redirect()->route('invoices.show', $invoice)
            ->with('success', "Invoice {$invoice->invoice_number} generated successfully.");
    }

    /**
     * Display invoice with itemized charges, payments ledger, and payment modal (Sec 7, p. 8).
     */
    public function show(Invoice $invoice): View
    {
        $invoice->load(['patient', 'doctor', 'appointment', 'prescription', 'items', 'payments.receiver']);
        $settings = Setting::allKeyValues();

        return view('invoices.show', compact('invoice', 'settings'));
    }

    /**
     * Printable invoice sheet with clinic branding (Sec 7, p. 8).
     */
    public function print(Invoice $invoice): View
    {
        $invoice->load(['patient', 'doctor', 'items', 'payments.receiver']);
        $settings = Setting::allKeyValues();

        return view('invoices.print', compact('invoice', 'settings'));
    }

    /**
     * Show form to edit existing invoice.
     */
    public function edit(Invoice $invoice): View
    {
        $invoice->load(['patient', 'doctor', 'items']);
        $patients = Patient::orderBy('name')->get();
        $doctors = User::whereIn('role', [
            User::ROLE_ADMIN_DOCTOR,
            User::ROLE_DOCTOR,
            User::ROLE_CLINIC_MANAGER,
        ])->where('status', 'active')->orderBy('name')->get();

        return view('invoices.edit', compact('invoice', 'patients', 'doctors'));
    }

    /**
     * Update invoice and its line items.
     */
    public function update(Request $request, Invoice $invoice): RedirectResponse
    {
        if (! $request->filled('doctor_id')) {
            $request->merge(['doctor_id' => $invoice->doctor_id ?? auth()->id()]);
        }

        $validated = $request->validate([
            'patient_id' => ['required', 'exists:patients,id'],
            'doctor_id' => ['nullable', Rule::exists('users', 'id')->where(fn (QueryBuilder $query) => $query
                ->whereIn('role', [User::ROLE_ADMIN_DOCTOR, User::ROLE_DOCTOR, User::ROLE_CLINIC_MANAGER])
                ->where(fn (QueryBuilder $doctorQuery) => $doctorQuery
                    ->where('status', 'active')
                    ->orWhere('id', $invoice->doctor_id)))],
            'invoice_date' => ['required', 'date_format:Y-m-d', 'before_or_equal:today'],
            'due_date' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:invoice_date'],
            'tax_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'discount_type' => ['required', 'in:fixed,percentage'],
            'discount_value' => ['nullable', 'numeric', 'decimal:0,2', 'min:0', 'max:99999999.99', Rule::when($request->input('discount_type') === Invoice::DISCOUNT_PERCENTAGE, ['max:100'])],
            'notes' => ['nullable', 'string', 'max:1000'],
            'terms' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.description' => ['required', 'string', 'max:255'],
            'items.*.item_type' => ['nullable', Rule::in(['consultation', 'procedure', 'medicine', 'lab_test', 'other'])],
            'items.*.quantity' => ['required', 'numeric', 'decimal:0,2', 'min:0.01', 'max:999999.99'],
            'items.*.unit_price' => ['required', 'numeric', 'decimal:0,2', 'min:0', 'max:99999999.99'],
        ], [
            'patient_id.required' => 'Please select a patient for this invoice.',
            'patient_id.exists' => 'The selected patient does not exist.',
            'invoice_date.required' => 'Invoice date is required.',
            'invoice_date.before_or_equal' => 'Invoice date cannot be a future date.',
            'due_date.after_or_equal' => 'Payment due date cannot be earlier than invoice date.',
            'items.required' => 'At least one itemized service charge is required.',
            'items.min' => 'Please add at least one line item.',
            'items.*.description.required' => 'Every line item must have a description.',
            'items.*.quantity.required' => 'Item quantity is required.',
            'items.*.quantity.min' => 'Item quantity must be greater than zero.',
            'items.*.unit_price.required' => 'Unit price is required.',
            'items.*.unit_price.min' => 'Unit price cannot be negative.',
            'tax_percentage.max' => 'Tax percentage cannot exceed 100%.',
        ]);

        $subtotal = collect($validated['items'])->sum(fn (array $item): float => (float) $item['quantity'] * (float) $item['unit_price']);
        $discountValue = (float) ($validated['discount_value'] ?? 0);
        $discountAmount = $validated['discount_type'] === Invoice::DISCOUNT_PERCENTAGE
            ? round($subtotal * $discountValue / 100, 2)
            : min($discountValue, $subtotal);
        $taxableSubtotal = max(0, $subtotal - $discountAmount);
        $newGrandTotal = round($taxableSubtotal + round($taxableSubtotal * (float) ($validated['tax_percentage'] ?? 0) / 100, 2), 2);

        if ($subtotal > 99999999.99 || $newGrandTotal > 99999999.99) {
            throw ValidationException::withMessages(['items' => 'The invoice total is too large. Reduce the quantity or unit price.']);
        }

        if ((float) $invoice->payments()->sum('amount') > $newGrandTotal) {
            throw ValidationException::withMessages(['items' => 'The new invoice total cannot be lower than payments already received.']);
        }

        DB::transaction(function () use ($invoice, $validated): void {
            $invoice->update([
                'patient_id' => $validated['patient_id'],
                'doctor_id' => $validated['doctor_id'] ?? null,
                'invoice_date' => $validated['invoice_date'],
                'due_date' => $validated['due_date'] ?? null,
                'tax_percentage' => $validated['tax_percentage'] ?? 0.00,
                'discount_type' => $validated['discount_type'],
                'discount_value' => $validated['discount_value'] ?? 0.00,
                'notes' => $validated['notes'] ?? null,
                'terms' => $validated['terms'] ?? null,
            ]);

            $invoice->items()->delete();
            foreach ($validated['items'] as $item) {
                $quantity = (float) $item['quantity'];
                $unitPrice = (float) $item['unit_price'];
                $invoice->items()->create([
                    'item_type' => $item['item_type'] ?? 'consultation',
                    'description' => $item['description'],
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'total' => round($quantity * $unitPrice, 2),
                ]);
            }

            $invoice->recalculateTotals();
        });

        return redirect()->route('invoices.show', $invoice)
            ->with('success', "Invoice {$invoice->invoice_number} updated successfully.");
    }

    /**
     * Remove invoice.
     */
    public function destroy(Request $request, Invoice $invoice): RedirectResponse
    {
        $patientId = $invoice->patient_id;
        $invoice->delete();

        if ($request->header('referer') && str_contains($request->header('referer'), 'patients')) {
            return redirect()->route('patients.show', ['patient' => $patientId, 'tab' => 'invoices'])
                ->with('success', 'Invoice removed.');
        }

        return redirect()->route('invoices.index')
            ->with('success', 'Invoice removed successfully.');
    }
}

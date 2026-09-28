<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Setting;
use App\Notifications\PracticeActivityNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PaymentController extends Controller
{
    /**
     * Record payment transaction against an invoice (Sec 7, pp. 8-9).
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'invoice_id' => ['required', 'exists:invoices,id'],
            'amount' => ['required', 'numeric', 'decimal:0,2', 'min:0.01', 'max:99999999.99'],
            'payment_method' => [
                'required',
                'string',
                Rule::in([
                    Payment::METHOD_CASH,
                    Payment::METHOD_CARD,
                    Payment::METHOD_UPI,
                    Payment::METHOD_BANK_TRANSFER,
                    Payment::METHOD_INSURANCE,
                    Payment::METHOD_CHEQUE,
                ]),
            ],
            'payment_date' => ['required', 'date_format:Y-m-d', 'before_or_equal:today'],
            'transaction_reference' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $invoice = Invoice::findOrFail($validated['invoice_id']);
        $maxPayable = (float) $invoice->balance_due;

        if ($invoice->status === Invoice::STATUS_CANCELLED || $maxPayable <= 0) {
            throw ValidationException::withMessages([
                'amount' => 'This invoice has no remaining balance to collect.',
            ]);
        }

        validator($validated, [
            'amount' => ['required', 'numeric', 'min:0.01', "max:{$maxPayable}"],
        ])->validate();

        $payment = Payment::create([
            'invoice_id' => $invoice->id,
            'patient_id' => $invoice->patient_id,
            'received_by' => auth()->id(),
            'payment_date' => $validated['payment_date'],
            'amount' => (float) $validated['amount'],
            'payment_method' => $validated['payment_method'],
            'transaction_reference' => $validated['transaction_reference'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        PracticeActivityNotification::sendToActiveStaff([
            'category' => 'billing',
            'title' => 'Payment received',
            'message' => '₹'.number_format($payment->amount, 2).' received for invoice '.$invoice->invoice_number.'.',
            'url' => route('invoices.show', $invoice),
            'permission' => 'invoices.view',
        ], 'notify_payment_received');

        return redirect()->route('invoices.show', $invoice)
            ->with('success', 'Payment of ₹'.number_format($payment->amount, 2)." logged successfully ({$payment->receipt_number}).");
    }

    /**
     * Print-ready payment receipt voucher (Sec 7, p. 9).
     */
    public function receipt(Payment $payment): View
    {
        $payment->load(['invoice.patient', 'invoice.doctor', 'receiver']);
        $settings = Setting::allKeyValues();

        return view('payments.receipt', compact('payment', 'settings'));
    }

    /**
     * Void a payment transaction and restore invoice balance.
     */
    public function destroy(Payment $payment): RedirectResponse
    {
        $invoice = $payment->invoice;
        $receipt = $payment->receipt_number;
        $payment->delete();

        return redirect()->route('invoices.show', $invoice)
            ->with('success', "Payment receipt {$receipt} has been voided.");
    }
}

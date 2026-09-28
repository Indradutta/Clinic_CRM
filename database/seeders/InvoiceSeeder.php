<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Invoice;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\Prescription;
use App\Models\User;
use Illuminate\Database\Seeder;

class InvoiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $doctor = User::where('role', User::ROLE_ADMIN_DOCTOR)->first() ?? User::first();
        if (! $doctor) {
            return;
        }

        if (Invoice::where('invoice_number', 'INV-00001')->exists()) {
            return;
        }

        $patients = Patient::take(4)->get();
        if ($patients->isEmpty()) {
            return;
        }

        $p1 = $patients[0] ?? null;
        $p2 = $patients[1] ?? $p1;
        $p3 = $patients[2] ?? $p1;

        $apt1 = Appointment::where('patient_id', $p1->id)->first();
        $rx1 = Prescription::where('patient_id', $p1->id)->first();

        // 1. Fully Paid Invoice with UPI (Patient 1)
        $inv1 = Invoice::create([
            'invoice_number' => 'INV-00001',
            'patient_id' => $p1->id,
            'doctor_id' => $doctor->id,
            'appointment_id' => $apt1?->id,
            'prescription_id' => $rx1?->id,
            'invoice_date' => now()->subDays(3)->toDateString(),
            'due_date' => now()->addDays(4)->toDateString(),
            'subtotal' => 1550.00,
            'discount_type' => 'fixed',
            'discount_value' => 100.00,
            'discount_amount' => 100.00,
            'tax_percentage' => 5.00,
            'tax_amount' => 72.50,
            'grand_total' => 1522.50,
            'paid_amount' => 1522.50,
            'balance_due' => 0.00,
            'status' => Invoice::STATUS_PAID,
            'notes' => 'Patient cleared payment via Clinic UPI QR Code at reception.',
            'terms' => 'Thank you for your visit. Retain this invoice for tax and insurance claims.',
        ]);

        $inv1->items()->createMany([
            [
                'item_type' => 'consultation',
                'description' => 'Comprehensive Diabetology & Cardiovascular Evaluation',
                'quantity' => 1,
                'unit_price' => 600.00,
                'total' => 600.00,
            ],
            [
                'item_type' => 'lab_test',
                'description' => 'HbA1c Glycated Hemoglobin Test (Automated HPLC)',
                'quantity' => 1,
                'unit_price' => 800.00,
                'total' => 800.00,
            ],
            [
                'item_type' => 'lab_test',
                'description' => 'Fasting Blood Glucose Test',
                'quantity' => 1,
                'unit_price' => 150.00,
                'total' => 150.00,
            ],
        ]);

        Payment::create([
            'receipt_number' => 'RCT-00001',
            'invoice_id' => $inv1->id,
            'patient_id' => $p1->id,
            'received_by' => $doctor->id,
            'payment_date' => now()->subDays(3),
            'amount' => 1522.50,
            'payment_method' => Payment::METHOD_UPI,
            'transaction_reference' => 'UPI/9823412091/MEDIFLOW',
            'notes' => 'Full payment received via phone UPI transfer.',
        ]);

        // 2. Partially Paid Invoice (Patient 2 - Bronchial Asthma)
        $apt2 = Appointment::where('patient_id', $p2->id)->first();
        $inv2 = Invoice::create([
            'invoice_number' => 'INV-00002',
            'patient_id' => $p2->id,
            'doctor_id' => $doctor->id,
            'appointment_id' => $apt2?->id,
            'prescription_id' => null,
            'invoice_date' => now()->subDays(1)->toDateString(),
            'due_date' => now()->addDays(6)->toDateString(),
            'subtotal' => 2350.00,
            'discount_type' => 'percentage',
            'discount_value' => 10.00,
            'discount_amount' => 235.00,
            'tax_percentage' => 0.00,
            'tax_amount' => 0.00,
            'grand_total' => 2115.00,
            'paid_amount' => 1000.00,
            'balance_due' => 1115.00,
            'status' => Invoice::STATUS_PARTIALLY_PAID,
            'notes' => 'Patient paid ₹1000 cash advance; balance ₹1115 promised at follow-up test.',
            'terms' => 'Balance due on or before review appointment date.',
        ]);

        $inv2->items()->createMany([
            [
                'item_type' => 'consultation',
                'description' => 'Specialist Pulmonology Consultation & Spirometry Review',
                'quantity' => 1,
                'unit_price' => 800.00,
                'total' => 800.00,
            ],
            [
                'item_type' => 'procedure',
                'description' => 'Computerized Spirometry (PFT) Lung Function Test',
                'quantity' => 1,
                'unit_price' => 1200.00,
                'total' => 1200.00,
            ],
            [
                'item_type' => 'procedure',
                'description' => 'Therapeutic Salbutamol Nebulization Session',
                'quantity' => 1,
                'unit_price' => 350.00,
                'total' => 350.00,
            ],
        ]);

        Payment::create([
            'receipt_number' => 'RCT-00002',
            'invoice_id' => $inv2->id,
            'patient_id' => $p2->id,
            'received_by' => $doctor->id,
            'payment_date' => now()->subDays(1),
            'amount' => 1000.00,
            'payment_method' => Payment::METHOD_CASH,
            'transaction_reference' => null,
            'notes' => 'Cash received at clinic reception counter.',
        ]);

        // 3. Unpaid Invoice (Patient 3)
        if ($p3) {
            $inv3 = Invoice::create([
                'invoice_number' => 'INV-00003',
                'patient_id' => $p3->id,
                'doctor_id' => $doctor->id,
                'appointment_id' => null,
                'prescription_id' => null,
                'invoice_date' => now()->toDateString(),
                'due_date' => now()->addDays(5)->toDateString(),
                'subtotal' => 1200.00,
                'discount_type' => 'fixed',
                'discount_value' => 0.00,
                'discount_amount' => 0.00,
                'tax_percentage' => 0.00,
                'tax_amount' => 0.00,
                'grand_total' => 1200.00,
                'paid_amount' => 0.00,
                'balance_due' => 1200.00,
                'status' => Invoice::STATUS_UNPAID,
                'notes' => 'Walk-in urgent acute care visit.',
                'terms' => 'Payment due within 5 business days.',
            ]);

            $inv3->items()->createMany([
                [
                    'item_type' => 'consultation',
                    'description' => 'Acute Urgent Care Consultation',
                    'quantity' => 1,
                    'unit_price' => 700.00,
                    'total' => 700.00,
                ],
                [
                    'item_type' => 'lab_test',
                    'description' => 'Rapid Strep Throat Swab Test',
                    'quantity' => 1,
                    'unit_price' => 500.00,
                    'total' => 500.00,
                ],
            ]);
        }
    }
}

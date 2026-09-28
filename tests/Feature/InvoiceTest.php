<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\Patient;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\SettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PermissionSeeder::class);
        $this->seed(SettingSeeder::class);
    }

    public function test_guests_cannot_access_invoices(): void
    {
        $response = $this->get('/invoices');
        $response->assertRedirect('/login');
    }

    public function test_staff_without_permission_cannot_access_invoices(): void
    {
        $nurse = User::factory()->create([
            'role' => User::ROLE_NURSE,
            'status' => 'active',
        ]);

        $response = $this->actingAs($nurse)->get('/invoices');
        $response->assertStatus(403);
    }

    public function test_authorized_staff_can_view_invoices_list(): void
    {
        $accountant = User::factory()->create([
            'role' => User::ROLE_ACCOUNTANT,
            'status' => 'active',
        ]);

        $patient = Patient::create([
            'name' => 'Charles Xavier',
            'gender' => 'Male',
            'phone' => '+1 555 1001',
        ]);

        $invoice = Invoice::create([
            'patient_id' => $patient->id,
            'invoice_date' => now()->toDateString(),
            'subtotal' => 1000.00,
            'grand_total' => 1000.00,
            'balance_due' => 1000.00,
            'status' => Invoice::STATUS_UNPAID,
        ]);

        $response = $this->actingAs($accountant)->get('/invoices');
        $response->assertStatus(200);
        $response->assertSee('Invoices & Billing');
        $response->assertSee('Charles Xavier');
        $response->assertSee($invoice->invoice_number);
    }

    public function test_invoices_search_and_status_filter(): void
    {
        $doctor = User::factory()->create([
            'role' => User::ROLE_ADMIN_DOCTOR,
            'status' => 'active',
        ]);

        $p1 = Patient::create([
            'name' => 'Erik Lehnsherr',
            'gender' => 'Male',
            'phone' => '+1 555 1002',
        ]);

        $p2 = Patient::create([
            'name' => 'Raven Darkholme',
            'gender' => 'Female',
            'phone' => '+1 555 1003',
        ]);

        Invoice::create([
            'patient_id' => $p1->id,
            'invoice_date' => now()->toDateString(),
            'subtotal' => 500.00,
            'grand_total' => 500.00,
            'balance_due' => 500.00,
            'status' => Invoice::STATUS_UNPAID,
        ]);

        Invoice::create([
            'patient_id' => $p2->id,
            'invoice_date' => now()->toDateString(),
            'subtotal' => 800.00,
            'grand_total' => 800.00,
            'paid_amount' => 800.00,
            'balance_due' => 0.00,
            'status' => Invoice::STATUS_PAID,
        ]);

        // Search test
        $response = $this->actingAs($doctor)->get('/invoices?search=Lehnsherr');
        $response->assertStatus(200);
        $response->assertSee('Erik Lehnsherr');
        $response->assertDontSee('Raven Darkholme');

        // Status filter test
        $statusResponse = $this->actingAs($doctor)->get('/invoices?status=paid');
        $statusResponse->assertStatus(200);
        $statusResponse->assertSee('Raven Darkholme');
        $statusResponse->assertDontSee('Erik Lehnsherr');
    }

    public function test_invoice_filters_reject_invalid_status_and_date_ranges(): void
    {
        $accountant = User::factory()->create([
            'role' => User::ROLE_ACCOUNTANT,
            'status' => 'active',
        ]);

        $response = $this->actingAs($accountant)->get('/invoices?status=unknown');

        $response->assertSessionHasErrors('status');

        $response = $this->actingAs($accountant)->get('/invoices?from_date=2026-10-01&to_date=2026-09-01');

        $response->assertSessionHasErrors(['from_date', 'to_date']);
    }

    public function test_can_render_invoice_create_page(): void
    {
        $accountant = User::factory()->create([
            'role' => User::ROLE_ACCOUNTANT,
            'status' => 'active',
        ]);

        $response = $this->actingAs($accountant)->get('/invoices/create');
        $response->assertStatus(200);
        $response->assertSee('Create Patient Invoice');
        $response->assertSee('Itemized Clinical');
    }

    public function test_can_store_invoice_with_line_items_and_recalculate(): void
    {
        $doctor = User::factory()->create([
            'role' => User::ROLE_ADMIN_DOCTOR,
            'status' => 'active',
        ]);

        $patient = Patient::create([
            'name' => 'Logan Howlett',
            'gender' => 'Male',
            'phone' => '+1 555 1004',
        ]);

        $payload = [
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addDays(7)->toDateString(),
            'discount_type' => 'fixed',
            'discount_value' => 100.00,
            'tax_percentage' => 10.00,
            'notes' => 'Adamantium bone density assessment',
            'items' => [
                [
                    'description' => 'Orthopedic Specialist Consultation',
                    'item_type' => 'consultation',
                    'quantity' => 1,
                    'unit_price' => 500.00,
                ],
                [
                    'description' => 'Full Skeletal Radiography',
                    'item_type' => 'lab_test',
                    'quantity' => 2,
                    'unit_price' => 300.00,
                ],
            ],
        ];

        // Subtotal = 500 + 600 = 1100.
        // Discount = 100 -> Taxable = 1000.
        // Tax 10% = 100.
        // Grand Total = 1100. Balance due = 1100.

        $response = $this->actingAs($doctor)->post('/invoices', $payload);
        $response->assertRedirect();

        $this->assertDatabaseHas('invoices', [
            'patient_id' => $patient->id,
            'subtotal' => 1100.00,
            'discount_amount' => 100.00,
            'tax_amount' => 100.00,
            'grand_total' => 1100.00,
            'balance_due' => 1100.00,
            'status' => Invoice::STATUS_UNPAID,
        ]);

        $this->assertDatabaseHas('invoice_items', [
            'description' => 'Orthopedic Specialist Consultation',
            'total' => 500.00,
        ]);

        $this->assertDatabaseHas('invoice_items', [
            'description' => 'Full Skeletal Radiography',
            'total' => 600.00,
        ]);
    }

    public function test_can_view_single_invoice(): void
    {
        $doctor = User::factory()->create([
            'role' => User::ROLE_ADMIN_DOCTOR,
            'status' => 'active',
        ]);

        $patient = Patient::create([
            'name' => 'Jean Grey',
            'gender' => 'Female',
            'phone' => '+1 555 1005',
        ]);

        $invoice = Invoice::create([
            'patient_id' => $patient->id,
            'invoice_date' => now()->toDateString(),
            'subtotal' => 750.00,
            'grand_total' => 750.00,
            'balance_due' => 750.00,
            'status' => Invoice::STATUS_UNPAID,
        ]);

        $invoice->items()->create([
            'description' => 'Neurological Reflex Test',
            'quantity' => 1,
            'unit_price' => 750.00,
            'total' => 750.00,
        ]);

        $response = $this->actingAs($doctor)->get("/invoices/{$invoice->id}");
        $response->assertStatus(200);
        $response->assertSee('Jean Grey');
        $response->assertSee('Neurological Reflex Test');
        $response->assertSee($invoice->invoice_number);
    }

    public function test_can_view_printable_invoice(): void
    {
        $doctor = User::factory()->create([
            'role' => User::ROLE_ADMIN_DOCTOR,
            'status' => 'active',
        ]);

        $patient = Patient::create([
            'name' => 'Scott Summers',
            'gender' => 'Male',
            'phone' => '+1 555 1006',
        ]);

        $invoice = Invoice::create([
            'patient_id' => $patient->id,
            'invoice_date' => now()->toDateString(),
            'subtotal' => 450.00,
            'grand_total' => 450.00,
            'balance_due' => 450.00,
            'status' => Invoice::STATUS_UNPAID,
        ]);

        $invoice->items()->create([
            'description' => 'Optometry Optical Exam',
            'quantity' => 1,
            'unit_price' => 450.00,
            'total' => 450.00,
        ]);

        $response = $this->actingAs($doctor)->get("/invoices/{$invoice->id}/print");
        $response->assertStatus(200);
        $response->assertSee('Scott Summers');
        $response->assertSee('Optometry Optical Exam');
        $response->assertSee('INVOICE');
    }

    public function test_can_update_invoice(): void
    {
        $doctor = User::factory()->create([
            'role' => User::ROLE_ADMIN_DOCTOR,
            'status' => 'active',
        ]);

        $patient = Patient::create([
            'name' => 'Ororo Munroe',
            'gender' => 'Female',
            'phone' => '+1 555 1007',
        ]);

        $invoice = Invoice::create([
            'patient_id' => $patient->id,
            'invoice_date' => now()->toDateString(),
            'subtotal' => 300.00,
            'grand_total' => 300.00,
            'balance_due' => 300.00,
            'discount_type' => 'fixed',
            'status' => Invoice::STATUS_UNPAID,
        ]);

        $response = $this->actingAs($doctor)->put("/invoices/{$invoice->id}", [
            'patient_id' => $patient->id,
            'invoice_date' => now()->toDateString(),
            'discount_type' => 'fixed',
            'discount_value' => 0.00,
            'tax_percentage' => 0.00,
            'items' => [
                [
                    'description' => 'Updated Pulmonary Exam',
                    'item_type' => 'consultation',
                    'quantity' => 1,
                    'unit_price' => 600.00,
                ],
            ],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'grand_total' => 600.00,
        ]);
        $this->assertDatabaseHas('invoice_items', [
            'invoice_id' => $invoice->id,
            'description' => 'Updated Pulmonary Exam',
        ]);
    }

    public function test_can_delete_invoice(): void
    {
        $doctor = User::factory()->create([
            'role' => User::ROLE_ADMIN_DOCTOR,
            'status' => 'active',
        ]);

        $patient = Patient::create([
            'name' => 'Hank McCoy',
            'gender' => 'Male',
            'phone' => '+1 555 1008',
        ]);

        $invoice = Invoice::create([
            'patient_id' => $patient->id,
            'invoice_date' => now()->toDateString(),
            'subtotal' => 200.00,
            'grand_total' => 200.00,
            'balance_due' => 200.00,
            'status' => Invoice::STATUS_UNPAID,
        ]);

        $response = $this->actingAs($doctor)->delete("/invoices/{$invoice->id}");
        $response->assertRedirect('/invoices');

        $this->assertSoftDeleted('invoices', [
            'id' => $invoice->id,
        ]);
    }
}

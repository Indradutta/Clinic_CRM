<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\SettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PermissionSeeder::class);
        $this->seed(SettingSeeder::class);
    }

    public function test_guests_cannot_log_payments(): void
    {
        $response = $this->post('/payments', [
            'invoice_id' => 1,
            'amount' => 500,
            'payment_method' => 'Cash',
        ]);
        $response->assertRedirect('/login');
    }

    public function test_staff_without_permission_cannot_log_payments(): void
    {
        $nurse = User::factory()->create([
            'role' => User::ROLE_NURSE,
            'status' => 'active',
        ]);

        $patient = Patient::create([
            'name' => 'Emma Frost',
            'gender' => 'Female',
            'phone' => '+1 555 2001',
        ]);

        $invoice = Invoice::create([
            'patient_id' => $patient->id,
            'invoice_date' => now()->toDateString(),
            'subtotal' => 500.00,
            'grand_total' => 500.00,
            'balance_due' => 500.00,
            'status' => Invoice::STATUS_UNPAID,
        ]);

        $response = $this->actingAs($nurse)->post('/payments', [
            'invoice_id' => $invoice->id,
            'amount' => 500.00,
            'payment_method' => 'Cash',
            'payment_date' => now()->toDateString(),
        ]);
        $response->assertStatus(403);
    }

    public function test_can_log_full_payment_and_auto_close_invoice(): void
    {
        $accountant = User::factory()->create([
            'role' => User::ROLE_ACCOUNTANT,
            'status' => 'active',
        ]);

        $patient = Patient::create([
            'name' => 'Bobby Drake',
            'gender' => 'Male',
            'phone' => '+1 555 2002',
        ]);

        $invoice = Invoice::create([
            'patient_id' => $patient->id,
            'invoice_date' => now()->toDateString(),
            'subtotal' => 1200.00,
            'grand_total' => 1200.00,
            'balance_due' => 1200.00,
            'status' => Invoice::STATUS_UNPAID,
        ]);

        $response = $this->actingAs($accountant)->post('/payments', [
            'invoice_id' => $invoice->id,
            'amount' => 1200.00,
            'payment_method' => 'UPI',
            'transaction_reference' => 'UPI/129381029/OKHDFC',
            'payment_date' => now()->toDateString(),
            'notes' => 'Settled in full via QR code',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('payments', [
            'invoice_id' => $invoice->id,
            'amount' => 1200.00,
            'payment_method' => 'UPI',
        ]);

        $invoice->refresh();
        $this->assertEquals(1200.00, (float) $invoice->paid_amount);
        $this->assertEquals(0.00, (float) $invoice->balance_due);
        $this->assertEquals(Invoice::STATUS_PAID, $invoice->status);
    }

    public function test_can_log_partial_payment_and_update_balance(): void
    {
        $accountant = User::factory()->create([
            'role' => User::ROLE_ACCOUNTANT,
            'status' => 'active',
        ]);

        $patient = Patient::create([
            'name' => 'Kurt Wagner',
            'gender' => 'Male',
            'phone' => '+1 555 2003',
        ]);

        $invoice = Invoice::create([
            'patient_id' => $patient->id,
            'invoice_date' => now()->toDateString(),
            'subtotal' => 2000.00,
            'grand_total' => 2000.00,
            'balance_due' => 2000.00,
            'status' => Invoice::STATUS_UNPAID,
        ]);

        $response = $this->actingAs($accountant)->post('/payments', [
            'invoice_id' => $invoice->id,
            'amount' => 800.00,
            'payment_method' => 'Cash',
            'payment_date' => now()->toDateString(),
            'notes' => 'Part payment advance',
        ]);

        $response->assertRedirect();

        $invoice->refresh();
        $this->assertEquals(800.00, (float) $invoice->paid_amount);
        $this->assertEquals(1200.00, (float) $invoice->balance_due);
        $this->assertEquals(Invoice::STATUS_PARTIALLY_PAID, $invoice->status);
    }

    public function test_can_log_multi_split_payments_across_methods(): void
    {
        $accountant = User::factory()->create([
            'role' => User::ROLE_ACCOUNTANT,
            'status' => 'active',
        ]);

        $patient = Patient::create([
            'name' => 'Warren Worthington',
            'gender' => 'Male',
            'phone' => '+1 555 2004',
        ]);

        $invoice = Invoice::create([
            'patient_id' => $patient->id,
            'invoice_date' => now()->toDateString(),
            'subtotal' => 1500.00,
            'grand_total' => 1500.00,
            'balance_due' => 1500.00,
            'status' => Invoice::STATUS_UNPAID,
        ]);

        // Payment 1: ₹500 Cash
        $this->actingAs($accountant)->post('/payments', [
            'invoice_id' => $invoice->id,
            'amount' => 500.00,
            'payment_method' => 'Cash',
            'payment_date' => now()->toDateString(),
        ]);

        $invoice->refresh();
        $this->assertEquals(500.00, (float) $invoice->paid_amount);
        $this->assertEquals(1000.00, (float) $invoice->balance_due);
        $this->assertEquals(Invoice::STATUS_PARTIALLY_PAID, $invoice->status);

        // Payment 2: ₹1000 Card
        $this->actingAs($accountant)->post('/payments', [
            'invoice_id' => $invoice->id,
            'amount' => 1000.00,
            'payment_method' => 'Card',
            'transaction_reference' => 'TXN-9823412',
            'payment_date' => now()->toDateString(),
        ]);

        $invoice->refresh();
        $this->assertEquals(1500.00, (float) $invoice->paid_amount);
        $this->assertEquals(0.00, (float) $invoice->balance_due);
        $this->assertEquals(Invoice::STATUS_PAID, $invoice->status);
        $this->assertCount(2, $invoice->payments);
    }

    public function test_can_view_payment_receipt(): void
    {
        $accountant = User::factory()->create([
            'role' => User::ROLE_ACCOUNTANT,
            'status' => 'active',
        ]);

        $patient = Patient::create([
            'name' => 'Remy LeBeau',
            'gender' => 'Male',
            'phone' => '+1 555 2005',
        ]);

        $invoice = Invoice::create([
            'patient_id' => $patient->id,
            'invoice_date' => now()->toDateString(),
            'subtotal' => 600.00,
            'grand_total' => 600.00,
            'paid_amount' => 600.00,
            'balance_due' => 0.00,
            'status' => Invoice::STATUS_PAID,
        ]);

        $payment = Payment::create([
            'invoice_id' => $invoice->id,
            'patient_id' => $patient->id,
            'received_by' => $accountant->id,
            'payment_date' => now(),
            'amount' => 600.00,
            'payment_method' => 'UPI',
            'transaction_reference' => 'GAMBIT/UPI/777',
        ]);

        $response = $this->actingAs($accountant)->get("/payments/{$payment->id}/receipt");
        $response->assertStatus(200);
        $response->assertSee('Remy LeBeau');
        $response->assertSee('PAYMENT RECEIPT');
        $response->assertSee($payment->receipt_number);
        $response->assertSee('600.00');
    }

    public function test_can_void_payment_and_restore_invoice_balance(): void
    {
        $accountant = User::factory()->create([
            'role' => User::ROLE_ACCOUNTANT,
            'status' => 'active',
        ]);

        $patient = Patient::create([
            'name' => 'Anna Marie',
            'gender' => 'Female',
            'phone' => '+1 555 2006',
        ]);

        $invoice = Invoice::create([
            'patient_id' => $patient->id,
            'invoice_date' => now()->toDateString(),
            'subtotal' => 1000.00,
            'grand_total' => 1000.00,
            'paid_amount' => 1000.00,
            'balance_due' => 0.00,
            'status' => Invoice::STATUS_PAID,
        ]);

        $payment = Payment::create([
            'invoice_id' => $invoice->id,
            'patient_id' => $patient->id,
            'received_by' => $accountant->id,
            'payment_date' => now(),
            'amount' => 1000.00,
            'payment_method' => 'Cash',
        ]);

        $response = $this->actingAs($accountant)->delete("/payments/{$payment->id}");
        $response->assertRedirect();

        $this->assertSoftDeleted('payments', [
            'id' => $payment->id,
        ]);

        $invoice->refresh();
        $this->assertEquals(0.00, (float) $invoice->paid_amount);
        $this->assertEquals(1000.00, (float) $invoice->balance_due);
        $this->assertEquals(Invoice::STATUS_UNPAID, $invoice->status);
    }
}

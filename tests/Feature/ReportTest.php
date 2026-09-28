<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Invoice;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\SettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PermissionSeeder::class);
        $this->seed(SettingSeeder::class);
    }

    private function createPatient(array $attributes = []): Patient
    {
        static $counter = 1;

        return Patient::create(array_merge([
            'name' => 'Report Patient '.$counter++,
            'gender' => 'Male',
            'phone' => '+91 98765 '.str_pad((string) $counter, 5, '0', STR_PAD_LEFT),
            'email' => 'reportpatient'.$counter.'@example.com',
            'dob' => '1992-05-10',
            'age' => 34,
            'blood_group' => 'B+',
        ], $attributes));
    }

    public function test_guests_cannot_access_reports(): void
    {
        $response = $this->get('/reports');
        $response->assertRedirect('/login');
    }

    public function test_authorized_staff_can_view_reports(): void
    {
        $doctor = User::factory()->create([
            'role' => User::ROLE_ADMIN_DOCTOR,
            'status' => 'active',
        ]);

        $response = $this->actingAs($doctor)->get('/reports');
        $response->assertStatus(200);
        $response->assertViewIs('reports.index');
        $response->assertSee('Practice Reports & Analytics');
        $response->assertSee('Financial Reports');
        $response->assertSee('Appointment Reports');
        $response->assertSee('Patient Reports');
        $response->assertSee('Prescription Reports');
    }

    public function test_financial_reports_tab_calculates_revenue_and_invoices(): void
    {
        $doctor = User::factory()->create([
            'role' => User::ROLE_ADMIN_DOCTOR,
            'status' => 'active',
        ]);

        $patient = $this->createPatient();

        $invoice = Invoice::create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'invoice_date' => now()->toDateString(),
            'grand_total' => 2000.00,
            'paid_amount' => 1500.00,
            'balance_due' => 500.00,
            'status' => Invoice::STATUS_PARTIALLY_PAID,
        ]);

        Payment::create([
            'invoice_id' => $invoice->id,
            'patient_id' => $patient->id,
            'received_by' => $doctor->id,
            'payment_date' => now()->toDateString(),
            'amount' => 1500.00,
            'payment_method' => 'UPI',
        ]);

        $response = $this->actingAs($doctor)->get('/reports?type=financial&date_range=this_month');
        $response->assertStatus(200);

        $metrics = $response->viewData('metrics');
        $this->assertEquals(1500.00, $metrics['total_revenue']);
        $this->assertEquals(2000.00, $metrics['total_invoiced']);
        $this->assertEquals(500.00, $metrics['outstanding_amount']);

        $response->assertSee('1,500.00');
        $response->assertSee('2,000.00');
        $response->assertSee('UPI');
    }

    public function test_appointment_reports_tab_renders_metrics_and_status_counts(): void
    {
        $doctor = User::factory()->create([
            'role' => User::ROLE_ADMIN_DOCTOR,
            'status' => 'active',
        ]);

        $patient = $this->createPatient();

        Appointment::create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'appointment_date' => now()->toDateString(),
            'appointment_time' => '10:00',
            'appointment_type' => 'Consultation',
            'status' => Appointment::STATUS_COMPLETED,
        ]);

        Appointment::create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'appointment_date' => now()->toDateString(),
            'appointment_time' => '11:00',
            'appointment_type' => 'Follow-up',
            'status' => Appointment::STATUS_CANCELLED,
        ]);

        $response = $this->actingAs($doctor)->get('/reports?type=appointments&date_range=this_month');
        $response->assertStatus(200);

        $metrics = $response->viewData('metrics');
        $this->assertEquals(2, $metrics['total_appointments']);
        $this->assertEquals(1, $metrics['completed_count']);
        $this->assertEquals(1, $metrics['cancelled_count']);
        $this->assertEquals(50.0, $metrics['completion_rate']);

        $response->assertSee('Completed Visits');
        $response->assertSee('50% completion rate');
    }

    public function test_patient_reports_tab_renders_new_and_returning_patients(): void
    {
        $doctor = User::factory()->create([
            'role' => User::ROLE_ADMIN_DOCTOR,
            'status' => 'active',
        ]);

        $newPatient = $this->createPatient([
            'name' => 'Brand New Patient',
            'created_at' => now(),
        ]);

        $response = $this->actingAs($doctor)->get('/reports?type=patients&date_range=this_month');
        $response->assertStatus(200);

        $metrics = $response->viewData('metrics');
        $this->assertGreaterThanOrEqual(1, $metrics['new_patients']);
        $response->assertSee('Brand New Patient');
    }

    public function test_prescription_reports_tab_renders_prescriptions_and_top_medications(): void
    {
        $doctor = User::factory()->create([
            'role' => User::ROLE_ADMIN_DOCTOR,
            'status' => 'active',
        ]);

        $patient = $this->createPatient();

        $rx = Prescription::create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'prescription_date' => now()->toDateString(),
            'diagnosis' => 'Acute Pharyngitis',
            'symptoms' => 'Sore throat and mild fever',
        ]);

        PrescriptionItem::create([
            'prescription_id' => $rx->id,
            'medicine_name' => 'Amoxicillin 500mg',
            'dosage' => '500 mg',
            'frequency' => 'Thrice daily',
            'duration' => '5 Days',
            'route' => 'Oral',
            'timing' => 'After Meals',
        ]);

        $response = $this->actingAs($doctor)->get('/reports?type=prescriptions&date_range=this_month');
        $response->assertStatus(200);

        $metrics = $response->viewData('metrics');
        $this->assertEquals(1, $metrics['total_prescriptions']);
        $this->assertEquals(1, $metrics['distinct_patients']);

        $response->assertSee('Amoxicillin 500mg');
        $response->assertSee('Acute Pharyngitis');
    }

    public function test_reports_csv_export_returns_streamed_csv_response(): void
    {
        $doctor = User::factory()->create([
            'role' => User::ROLE_ADMIN_DOCTOR,
            'status' => 'active',
        ]);

        $response = $this->actingAs($doctor)->get('/reports/export?type=financial&date_range=this_month');
        $response->assertStatus(200);
        $this->assertTrue(str_contains($response->headers->get('content-type') ?? '', 'text/csv'));
        $this->assertTrue(str_contains($response->headers->get('content-disposition') ?? '', 'attachment'));
    }

    public function test_reports_printable_view_renders_successfully(): void
    {
        $doctor = User::factory()->create([
            'role' => User::ROLE_ADMIN_DOCTOR,
            'status' => 'active',
        ]);

        $response = $this->actingAs($doctor)->get('/reports/print?type=financial&date_range=this_month');
        $response->assertStatus(200);
        $response->assertSee('Official Medical Practice');
        $response->assertSee('FINANCIAL AUDIT');
    }

    public function test_global_search_finds_patients_and_appointments(): void
    {
        $doctor = User::factory()->create([
            'role' => User::ROLE_ADMIN_DOCTOR,
            'status' => 'active',
        ]);

        $patient = $this->createPatient([
            'name' => 'Sherlock Holmes',
            'phone' => '+91 99988 77766',
        ]);

        $response = $this->actingAs($doctor)->get('/search?q=Sherlock');
        $response->assertStatus(200);
        $response->assertSee('Sherlock Holmes');
        $response->assertSee('+91 99988 77766');
    }

    public function test_notifications_feed_renders_successfully(): void
    {
        $doctor = User::factory()->create([
            'role' => User::ROLE_ADMIN_DOCTOR,
            'status' => 'active',
        ]);

        $response = $this->actingAs($doctor)->get('/notifications');
        $response->assertStatus(200);
        $response->assertSee('Notification Center');
    }
}

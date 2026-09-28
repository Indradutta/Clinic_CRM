<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Consultation;
use App\Models\Invoice;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\SettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PermissionSeeder::class);
        $this->seed(SettingSeeder::class);
    }

    public function test_guests_cannot_access_dashboard(): void
    {
        $resRoot = $this->get('/');
        $resRoot->assertRedirect('/login');

        $resDash = $this->get('/dashboard');
        $resDash->assertRedirect('/login');
    }

    public function test_authorized_staff_can_view_dashboard(): void
    {
        $doctor = User::factory()->create([
            'role' => User::ROLE_ADMIN_DOCTOR,
            'status' => 'active',
        ]);

        $response = $this->actingAs($doctor)->get('/');
        $response->assertStatus(200);
        $response->assertViewIs('dashboard');
        $response->assertSee('at a glance');
        $response->assertSee($doctor->name);
    }

    public function test_staff_without_dashboard_permission_cannot_access_dashboard(): void
    {
        $nurse = User::factory()->create([
            'role' => User::ROLE_NURSE,
            'status' => 'active',
        ]);

        $response = $this->actingAs($nurse)->get('/');
        $response->assertStatus(403);
    }

    private function createPatient(array $attributes = []): Patient
    {
        static $counter = 1;

        return Patient::create(array_merge([
            'name' => 'Test Patient '.$counter++,
            'gender' => 'Male',
            'phone' => '+91 98765 '.str_pad((string) $counter, 5, '0', STR_PAD_LEFT),
            'email' => 'patient'.$counter.'@example.com',
            'dob' => '1990-01-01',
            'age' => 36,
            'blood_group' => 'O+',
        ], $attributes));
    }

    public function test_dashboard_displays_correct_patient_and_appointment_statistics(): void
    {
        $doctor = User::factory()->create([
            'role' => User::ROLE_ADMIN_DOCTOR,
            'status' => 'active',
        ]);

        $patients = [
            $this->createPatient(),
            $this->createPatient(),
            $this->createPatient(),
            $this->createPatient(),
        ];

        // Appointments setup
        Appointment::create([
            'patient_id' => $patients[0]->id,
            'doctor_id' => $doctor->id,
            'appointment_date' => now()->toDateString(),
            'appointment_time' => '10:00',
            'appointment_type' => 'Consultation',
            'status' => Appointment::STATUS_CHECKED_IN,
        ]);

        Appointment::create([
            'patient_id' => $patients[1]->id,
            'doctor_id' => $doctor->id,
            'appointment_date' => now()->toDateString(),
            'appointment_time' => '14:30',
            'appointment_type' => 'Follow-up',
            'status' => Appointment::STATUS_CONFIRMED,
        ]);

        Appointment::create([
            'patient_id' => $patients[2]->id,
            'doctor_id' => $doctor->id,
            'appointment_date' => now()->addDays(2)->toDateString(),
            'appointment_time' => '11:00',
            'appointment_type' => 'Routine Checkup',
            'status' => Appointment::STATUS_PENDING,
        ]);

        Appointment::create([
            'patient_id' => $patients[3]->id,
            'doctor_id' => $doctor->id,
            'appointment_date' => now()->subDay()->toDateString(),
            'appointment_time' => '09:00',
            'appointment_type' => 'Emergency',
            'status' => Appointment::STATUS_COMPLETED,
        ]);

        Appointment::create([
            'patient_id' => $patients[0]->id,
            'doctor_id' => $doctor->id,
            'appointment_date' => now()->subDays(2)->toDateString(),
            'appointment_time' => '16:00',
            'appointment_type' => 'Consultation',
            'status' => Appointment::STATUS_CANCELLED,
        ]);

        $response = $this->actingAs($doctor)->get('/');
        $response->assertStatus(200);

        $kpis = $response->viewData('kpiStats');
        $this->assertEquals(4, $kpis['total_patients']);
        $this->assertEquals(2, $kpis['today_appointments']);
        $this->assertEquals(1, $kpis['today_morning']);
        $this->assertEquals(1, $kpis['today_afternoon']);
        $this->assertEquals(1, $kpis['upcoming_appointments']);
        $this->assertEquals(1, $kpis['completed_appointments']);
        $this->assertEquals(1, $kpis['pending_appointments']);
        $this->assertEquals(1, $kpis['cancelled_appointments']);
    }

    public function test_dashboard_displays_correct_financial_kpi_cards(): void
    {
        $doctor = User::factory()->create([
            'role' => User::ROLE_ADMIN_DOCTOR,
            'status' => 'active',
        ]);

        $patient = $this->createPatient();

        $inv1 = Invoice::create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'invoice_date' => now()->toDateString(),
            'grand_total' => 1500.00,
            'paid_amount' => 1000.00,
            'balance_due' => 500.00,
            'status' => Invoice::STATUS_PARTIALLY_PAID,
        ]);

        Payment::create([
            'invoice_id' => $inv1->id,
            'patient_id' => $patient->id,
            'received_by' => $doctor->id,
            'payment_date' => now()->toDateString(),
            'amount' => 1000.00,
            'payment_method' => 'UPI',
        ]);

        $inv2 = Invoice::create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'invoice_date' => now()->toDateString(),
            'grand_total' => 800.00,
            'paid_amount' => 0.00,
            'balance_due' => 800.00,
            'status' => Invoice::STATUS_UNPAID,
        ]);

        $response = $this->actingAs($doctor)->get('/');
        $response->assertStatus(200);

        $kpis = $response->viewData('kpiStats');
        $this->assertEquals(2, $kpis['total_invoices']);
        $this->assertEquals(1000.00, $kpis['paid_amount']);
        $this->assertEquals(1300.00, $kpis['pending_amount']);

        $rev = $response->viewData('revenueOverview');
        $this->assertEquals(1000.00, $rev['today']);
        $this->assertEquals(1300.00, $rev['outstanding']);
        $this->assertEquals(2, $rev['outstanding_count']);
    }

    public function test_dashboard_renders_todays_appointments_queue_with_actions(): void
    {
        $doctor = User::factory()->create([
            'role' => User::ROLE_ADMIN_DOCTOR,
            'status' => 'active',
        ]);

        $patient = $this->createPatient([
            'name' => 'Johnathan Wick',
            'phone' => '+91 98765 43210',
        ]);

        $apt = Appointment::create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'appointment_date' => now()->toDateString(),
            'appointment_time' => '10:30',
            'appointment_type' => 'Consultation',
            'status' => Appointment::STATUS_CHECKED_IN,
        ]);

        $response = $this->actingAs($doctor)->get('/');
        $response->assertStatus(200);
        $response->assertSee('Johnathan Wick');
        $response->assertSee('10:30');
        $response->assertSee('Consultation');
        $response->assertSee('Checked In');
        $response->assertSee('Start visit');
    }

    public function test_dashboard_shows_empty_state_when_no_appointments_today(): void
    {
        $doctor = User::factory()->create([
            'role' => User::ROLE_ADMIN_DOCTOR,
            'status' => 'active',
        ]);

        $response = $this->actingAs($doctor)->get('/');
        $response->assertStatus(200);
        $response->assertSee('No appointments scheduled today');
    }

    public function test_dashboard_renders_recent_patients_with_computed_visit_info(): void
    {
        $doctor = User::factory()->create([
            'role' => User::ROLE_ADMIN_DOCTOR,
            'status' => 'active',
        ]);

        $patient = $this->createPatient([
            'name' => 'Eleanor Vance',
            'phone' => '+91 91234 56789',
        ]);

        // Prior consultation
        Consultation::create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'consultation_date' => now()->subDays(5)->toDateString(),
            'symptoms' => 'Severe throbbing headache',
            'diagnosis' => 'Acute Migraine',
        ]);

        // Future appointment
        Appointment::create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'appointment_date' => now()->addDays(3)->toDateString(),
            'appointment_time' => '11:15',
            'appointment_type' => 'Follow-up',
            'status' => Appointment::STATUS_CONFIRMED,
        ]);

        $response = $this->actingAs($doctor)->get('/');
        $response->assertStatus(200);
        $response->assertSee('Eleanor Vance');
        $response->assertSee($patient->patient_id);
        $response->assertSee(now()->subDays(5)->format('d M Y'));
        $response->assertSee(now()->addDays(3)->format('d M Y'));
    }

    public function test_dashboard_masks_financial_metrics_for_staff_without_invoice_permissions(): void
    {
        // Receptionist has dashboard.view, but not invoices.view by default
        $receptionist = User::factory()->create([
            'role' => User::ROLE_RECEPTIONIST,
            'status' => 'active',
        ]);

        $response = $this->actingAs($receptionist)->get('/');
        $response->assertStatus(200);
        $response->assertDontSee('Monthly Revenue Trend');
        $response->assertDontSee('Invoices by Payment Status');
    }

    public function test_dashboard_passes_chart_datasets_to_view(): void
    {
        $doctor = User::factory()->create([
            'role' => User::ROLE_ADMIN_DOCTOR,
            'status' => 'active',
        ]);

        $response = $this->actingAs($doctor)->get('/');
        $response->assertStatus(200);

        $monthlyChart = $response->viewData('monthlyRevenueChart');
        $this->assertIsArray($monthlyChart);
        $this->assertCount(6, $monthlyChart['labels']);
        $this->assertCount(6, $monthlyChart['revenue']);
        $this->assertCount(6, $monthlyChart['invoiced']);

        $distChart = $response->viewData('appointmentDistribution');
        $this->assertIsArray($distChart);
        $this->assertCount(6, $distChart['labels']);
        $this->assertCount(6, $distChart['data']);
    }

    public function test_status_can_be_updated_directly_from_dashboard_quick_action(): void
    {
        $doctor = User::factory()->create([
            'role' => User::ROLE_ADMIN_DOCTOR,
            'status' => 'active',
        ]);

        $patient = $this->createPatient();

        $apt = Appointment::create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'appointment_date' => now()->toDateString(),
            'appointment_time' => '10:00',
            'appointment_type' => 'Consultation',
            'status' => Appointment::STATUS_IN_CONSULTATION,
        ]);

        $response = $this->actingAs($doctor)->post(route('appointments.update-status', $apt), [
            'status' => Appointment::STATUS_COMPLETED,
        ]);

        $response->assertSessionHas('success');
        $this->assertEquals(Appointment::STATUS_COMPLETED, $apt->fresh()->status);
    }
}

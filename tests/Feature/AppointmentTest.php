<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Patient;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AppointmentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PermissionSeeder::class);
    }

    public function test_guests_cannot_access_appointments(): void
    {
        $response = $this->get('/appointments');
        $response->assertRedirect('/login');
    }

    public function test_staff_without_permission_cannot_access_appointments(): void
    {
        $accountant = User::factory()->create([
            'role' => User::ROLE_ACCOUNTANT,
            'status' => 'active',
        ]);

        $response = $this->actingAs($accountant)->get('/appointments');
        $response->assertStatus(403);
    }

    public function test_authorized_staff_can_view_appointments_list(): void
    {
        $doctor = User::factory()->create([
            'role' => User::ROLE_ADMIN_DOCTOR,
            'status' => 'active',
        ]);

        $patient = Patient::create([
            'name' => 'Alice Walker',
            'gender' => 'Female',
            'phone' => '+1 555 1234',
        ]);

        $appointment = Appointment::create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'appointment_date' => now()->toDateString(),
            'appointment_time' => '10:00',
            'appointment_type' => 'Consultation',
            'status' => Appointment::STATUS_CONFIRMED,
        ]);

        $response = $this->actingAs($doctor)->get('/appointments');
        $response->assertStatus(200);
        $response->assertSee('Appointments & Scheduling');
        $response->assertSee('Alice Walker');
        $response->assertSee($appointment->appointment_id);
    }

    public function test_can_filter_appointments_by_status(): void
    {
        $doctor = User::factory()->create([
            'role' => User::ROLE_ADMIN_DOCTOR,
            'status' => 'active',
        ]);

        $patient = Patient::create([
            'name' => 'Bob Stone',
            'gender' => 'Male',
            'phone' => '1112223333',
        ]);

        $confirmedApt = Appointment::create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'appointment_date' => now()->toDateString(),
            'appointment_time' => '09:00',
            'appointment_type' => 'Consultation',
            'status' => Appointment::STATUS_CONFIRMED,
        ]);

        $cancelledApt = Appointment::create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'appointment_date' => now()->toDateString(),
            'appointment_time' => '11:00',
            'appointment_type' => 'Routine Checkup',
            'status' => Appointment::STATUS_CANCELLED,
        ]);

        // Filter for confirmed
        $response = $this->actingAs($doctor)->get('/appointments?status=confirmed');
        $response->assertSee($confirmedApt->appointment_id);
        $response->assertDontSee($cancelledApt->appointment_id);
    }

    public function test_can_schedule_new_appointment(): void
    {
        $doctor = User::factory()->create([
            'role' => User::ROLE_ADMIN_DOCTOR,
            'status' => 'active',
        ]);

        $patient = Patient::create([
            'name' => 'Charlie Brown',
            'gender' => 'Male',
            'phone' => '4445556666',
        ]);

        $payload = [
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'appointment_date' => now()->addDay()->toDateString(),
            'appointment_time' => '14:30',
            'appointment_type' => 'Follow-up',
            'reason_for_visit' => 'Blood test review',
            'notes' => 'Patient has reports on phone.',
            'status' => 'checked_in',
            'payment_status' => 'paid',
        ];

        $response = $this->actingAs($doctor)->post('/appointments', $payload);

        $this->assertDatabaseHas('appointments', [
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'appointment_time' => '14:30',
            'appointment_type' => 'Follow-up',
            'status' => Appointment::STATUS_PENDING,
        ]);

        $apt = Appointment::where('patient_id', $patient->id)->first();
        $this->assertNotNull($apt);
        $this->assertStringStartsWith('APT-', $apt->appointment_id);

        $response->assertRedirect(route('appointments.show', $apt));
    }

    public function test_can_view_appointment_details(): void
    {
        $doctor = User::factory()->create([
            'role' => User::ROLE_ADMIN_DOCTOR,
            'status' => 'active',
        ]);

        $patient = Patient::create([
            'name' => 'David Warner',
            'gender' => 'Male',
            'phone' => '9990001111',
            'allergies' => 'Penicillin',
        ]);

        $apt = Appointment::create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'appointment_date' => now()->toDateString(),
            'appointment_time' => '15:00',
            'appointment_type' => 'Emergency',
            'reason_for_visit' => 'Severe acute migrainous pain',
            'status' => Appointment::STATUS_PENDING,
        ]);

        $response = $this->actingAs($doctor)->get(route('appointments.show', $apt));
        $response->assertStatus(200);
        $response->assertSee($apt->appointment_id);
        $response->assertSee('David Warner');
        $response->assertSee('Penicillin');
        $response->assertSee('Severe acute migrainous pain');
    }

    public function test_can_update_appointment_details(): void
    {
        $doctor = User::factory()->create([
            'role' => User::ROLE_ADMIN_DOCTOR,
            'status' => 'active',
        ]);

        $patient = Patient::create([
            'name' => 'Eva Green',
            'gender' => 'Female',
            'phone' => '2223334444',
        ]);

        $apt = Appointment::create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'appointment_date' => now()->toDateString(),
            'appointment_time' => '09:30',
            'appointment_type' => 'Consultation',
            'status' => Appointment::STATUS_PENDING,
        ]);

        $response = $this->actingAs($doctor)->put(route('appointments.update', $apt), [
            'doctor_id' => $doctor->id,
            'appointment_date' => now()->addDays(2)->toDateString(),
            'appointment_time' => '11:00',
            'appointment_type' => 'Follow-up',
            'status' => 'confirmed',
            'reason_for_visit' => 'Rescheduled due to doctor conference',
        ]);

        $response->assertRedirect(route('appointments.show', $apt));

        $this->assertDatabaseHas('appointments', [
            'id' => $apt->id,
            'appointment_time' => '11:00',
            'appointment_type' => 'Follow-up',
            'status' => 'confirmed',
            'payment_status' => Appointment::PAYMENT_UNPAID,
        ]);
    }

    public function test_can_transition_lifecycle_status(): void
    {
        $doctor = User::factory()->create([
            'role' => User::ROLE_ADMIN_DOCTOR,
            'status' => 'active',
        ]);

        $patient = Patient::create([
            'name' => 'Frank Castle',
            'gender' => 'Male',
            'phone' => '7778889999',
        ]);

        $apt = Appointment::create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'appointment_date' => now()->toDateString(),
            'appointment_time' => '10:00',
            'appointment_type' => 'Consultation',
            'status' => Appointment::STATUS_CONFIRMED,
        ]);

        // Step 1: Check in
        $response = $this->actingAs($doctor)->post(route('appointments.update-status', $apt), [
            'status' => 'checked_in',
        ]);
        $this->assertEquals(Appointment::STATUS_CHECKED_IN, $apt->fresh()->status);

        // Step 2: Start consultation
        $response = $this->actingAs($doctor)->post(route('appointments.update-status', $apt), [
            'status' => 'in_consultation',
        ]);
        $this->assertEquals(Appointment::STATUS_IN_CONSULTATION, $apt->fresh()->status);

        // Step 3: Complete
        $response = $this->actingAs($doctor)->post(route('appointments.update-status', $apt), [
            'status' => 'completed',
        ]);
        $this->assertEquals(Appointment::STATUS_COMPLETED, $apt->fresh()->status);
    }

    public function test_can_cancel_appointment_with_reason(): void
    {
        $doctor = User::factory()->create([
            'role' => User::ROLE_ADMIN_DOCTOR,
            'status' => 'active',
        ]);

        $patient = Patient::create([
            'name' => 'Grace Hopper',
            'gender' => 'Female',
            'phone' => '8889990000',
        ]);

        $apt = Appointment::create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'appointment_date' => now()->toDateString(),
            'appointment_time' => '13:00',
            'appointment_type' => 'Consultation',
            'status' => Appointment::STATUS_CONFIRMED,
        ]);

        $response = $this->actingAs($doctor)->post(route('appointments.update-status', $apt), [
            'status' => 'cancelled',
            'cancelled_reason' => 'Patient has fever and requested teleconsultation.',
        ]);

        $fresh = $apt->fresh();
        $this->assertEquals(Appointment::STATUS_CANCELLED, $fresh->status);
        $this->assertEquals('Patient has fever and requested teleconsultation.', $fresh->cancelled_reason);
    }

    public function test_can_view_interactive_calendar_views(): void
    {
        $doctor = User::factory()->create([
            'role' => User::ROLE_ADMIN_DOCTOR,
            'status' => 'active',
        ]);

        $patient = Patient::create([
            'name' => 'Henry Ford',
            'gender' => 'Male',
            'phone' => '3334445555',
        ]);

        Appointment::create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'appointment_date' => now()->toDateString(),
            'appointment_time' => '10:00',
            'appointment_type' => 'Consultation',
            'status' => Appointment::STATUS_CONFIRMED,
        ]);

        // Month view
        $response = $this->actingAs($doctor)->get('/appointments?view=calendar&cal_type=month');
        $response->assertStatus(200);
        $response->assertSee('Interactive Calendar');
        $response->assertSee('Month View');

        // Week view
        $response = $this->actingAs($doctor)->get('/appointments?view=calendar&cal_type=week');
        $response->assertStatus(200);
        $response->assertSee('Week View');

        // Day view
        $response = $this->actingAs($doctor)->get('/appointments?view=calendar&cal_type=day');
        $response->assertStatus(200);
        $response->assertSee('Day View');
        $response->assertSee('Timeline Schedule');
        $response->assertSee('Henry Ford');
    }

    public function test_can_soft_delete_appointment(): void
    {
        $doctor = User::factory()->create([
            'role' => User::ROLE_ADMIN_DOCTOR,
            'status' => 'active',
        ]);

        $patient = Patient::create([
            'name' => 'Ivy League',
            'gender' => 'Female',
            'phone' => '6667778888',
        ]);

        $apt = Appointment::create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'appointment_date' => now()->toDateString(),
            'appointment_time' => '16:00',
            'appointment_type' => 'Consultation',
            'status' => Appointment::STATUS_PENDING,
        ]);

        $response = $this->actingAs($doctor)->delete(route('appointments.destroy', $apt));
        $response->assertRedirect(route('appointments.index'));

        $this->assertSoftDeleted('appointments', ['id' => $apt->id]);
    }

    public function test_patient_profile_renders_appointment_history(): void
    {
        $doctor = User::factory()->create([
            'role' => User::ROLE_ADMIN_DOCTOR,
            'status' => 'active',
        ]);

        $patient = Patient::create([
            'name' => 'Jack Ryan',
            'gender' => 'Male',
            'phone' => '5554443333',
        ]);

        $apt = Appointment::create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'appointment_date' => now()->toDateString(),
            'appointment_time' => '09:15',
            'appointment_type' => 'Consultation',
            'status' => Appointment::STATUS_CHECKED_IN,
        ]);

        $response = $this->actingAs($doctor)->get(route('patients.show', ['patient' => $patient, 'tab' => 'appointments']));
        $response->assertStatus(200);
        $response->assertSee('Past Appointments');
        $response->assertSee($apt->appointment_id);
    }

    public function test_appointment_forms_auto_assign_single_doctor_without_dropdown(): void
    {
        $doctor = User::factory()->create([
            'name' => 'Dr. Single Specialist',
            'role' => User::ROLE_ADMIN_DOCTOR,
            'status' => 'active',
        ]);

        $patient = Patient::create([
            'name' => 'John Doe',
            'gender' => 'Male',
            'phone' => '1234567890',
        ]);

        $apt = Appointment::create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'appointment_date' => now()->toDateString(),
            'appointment_time' => '10:00',
            'appointment_type' => 'Consultation',
            'status' => Appointment::STATUS_PENDING,
        ]);

        // Create view check
        $createResponse = $this->actingAs($doctor)->get(route('appointments.create'));
        $createResponse->assertStatus(200);
        $createResponse->assertSee('Dr. Single Specialist');
        $createResponse->assertSee('Assigned Doctor');
        $createResponse->assertDontSee('<select name="doctor_id"', false);

        // Edit view check
        $editResponse = $this->actingAs($doctor)->get(route('appointments.edit', $apt));
        $editResponse->assertStatus(200);
        $editResponse->assertSee('Dr. Single Specialist');
        $editResponse->assertSee('Assigned Doctor');
        $editResponse->assertDontSee('<select name="doctor_id"', false);

        // Index filter check (no doctor filter dropdown if only 1 doctor)
        $indexResponse = $this->actingAs($doctor)->get(route('appointments.index'));
        $indexResponse->assertStatus(200);
        $indexResponse->assertDontSee('<select name="doctor_id"', false);

        // Calendar filter check (no doctor filter dropdown if only 1 doctor)
        $calResponse = $this->actingAs($doctor)->get(route('appointments.index', ['view' => 'calendar']));
        $calResponse->assertStatus(200);
        $calResponse->assertDontSee('<select name="doctor_id"', false);
    }

    public function test_appointment_forms_render_dropdown_when_multiple_doctors_exist(): void
    {
        $doc1 = User::factory()->create([
            'name' => 'Dr. Alice Smith',
            'role' => User::ROLE_ADMIN_DOCTOR,
            'status' => 'active',
        ]);

        $doc2 = User::factory()->create([
            'name' => 'Dr. Bob Jones',
            'role' => User::ROLE_ADMIN_DOCTOR,
            'status' => 'active',
        ]);

        $nurse = User::factory()->create([
            'name' => 'Nurse Nancy',
            'role' => User::ROLE_NURSE,
            'status' => 'active',
        ]);

        $patient = Patient::create([
            'name' => 'Jane Smith',
            'gender' => 'Female',
            'phone' => '9876543210',
        ]);

        $apt = Appointment::create([
            'patient_id' => $patient->id,
            'doctor_id' => $doc1->id,
            'appointment_date' => now()->toDateString(),
            'appointment_time' => '11:00',
            'appointment_type' => 'Consultation',
            'status' => Appointment::STATUS_PENDING,
        ]);

        // Create view
        $createResponse = $this->actingAs($doc1)->get(route('appointments.create'));
        $createResponse->assertStatus(200);
        $createResponse->assertSee('<select name="doctor_id"', false);
        $createResponse->assertSee('Dr. Alice Smith');
        $createResponse->assertSee('Dr. Bob Jones');
        // Role should NOT be appended to name
        $createResponse->assertDontSee('Dr. Alice Smith (Admin Doctor)');
        // Non-doctors should not appear
        $createResponse->assertDontSee('Nurse Nancy');

        // Edit view
        $editResponse = $this->actingAs($doc1)->get(route('appointments.edit', $apt));
        $editResponse->assertStatus(200);
        $editResponse->assertSee('<select name="doctor_id"', false);
        $editResponse->assertSee('Dr. Alice Smith');
        $editResponse->assertSee('Dr. Bob Jones');
        $editResponse->assertDontSee('Dr. Alice Smith (Admin Doctor)');
        $editResponse->assertDontSee('Nurse Nancy');

        // Index filter
        $indexResponse = $this->actingAs($doc1)->get(route('appointments.index'));
        $indexResponse->assertStatus(200);
        $indexResponse->assertSee('<select name="doctor_id"', false);
        $indexResponse->assertSee('All Doctors');

        // Calendar filter
        $calResponse = $this->actingAs($doc1)->get(route('appointments.index', ['view' => 'calendar']));
        $calResponse->assertStatus(200);
        $calResponse->assertSee('<select name="doctor_id"', false);
        $calResponse->assertSee('All Doctors');
    }
}

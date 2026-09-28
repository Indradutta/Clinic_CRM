<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Consultation;
use App\Models\Patient;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\SettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConsultationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PermissionSeeder::class);
        $this->seed(SettingSeeder::class);
    }

    public function test_guests_cannot_access_consultations(): void
    {
        $response = $this->get('/consultations/create');
        $response->assertRedirect('/login');
    }

    public function test_can_render_consultation_create_page(): void
    {
        $doctor = User::factory()->create([
            'role' => User::ROLE_ADMIN_DOCTOR,
            'status' => 'active',
        ]);

        $patient = Patient::create([
            'name' => 'Wanda Maximoff',
            'gender' => 'Female',
            'phone' => '+1 555 8888',
        ]);

        $response = $this->actingAs($doctor)->get("/consultations/create?patient_id={$patient->id}");
        $response->assertStatus(200);
        $response->assertSee('Clinical Consultation');
        $response->assertSee('Wanda Maximoff');
        $response->assertSee('Vital Signs');
    }

    public function test_can_store_consultation_with_vitals(): void
    {
        $doctor = User::factory()->create([
            'role' => User::ROLE_ADMIN_DOCTOR,
            'status' => 'active',
        ]);

        $patient = Patient::create([
            'name' => 'Steve Rogers',
            'gender' => 'Male',
            'phone' => '+1 555 9999',
        ]);

        $appointment = Appointment::create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'appointment_date' => now()->toDateString(),
            'appointment_time' => '11:00',
            'appointment_type' => 'Consultation',
            'status' => Appointment::STATUS_CHECKED_IN,
        ]);

        $payload = [
            'patient_id' => $patient->id,
            'appointment_id' => $appointment->id,
            'consultation_date' => now()->toDateString(),
            'symptoms' => 'Mild fatigue and muscle soreness',
            'diagnosis' => 'Post-exertional fatigue',
            'vitals' => [
                'blood_pressure' => '115/75',
                'heart_rate' => '62',
                'temperature' => '98.2',
                'weight' => '85',
                'height' => '185',
                'bmi' => '24.8',
            ],
            'medical_notes' => 'Patient is an athlete. Cardiovascular and musculoskeletal exams normal.',
            'treatment' => 'Adequate hydration and sleep rest.',
        ];

        $response = $this->actingAs($doctor)->post('/consultations', $payload);
        $response->assertRedirect();

        $this->assertDatabaseHas('consultations', [
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'diagnosis' => 'Post-exertional fatigue',
        ]);
    }

    public function test_can_view_consultation(): void
    {
        $doctor = User::factory()->create([
            'role' => User::ROLE_ADMIN_DOCTOR,
            'status' => 'active',
        ]);

        $patient = Patient::create([
            'name' => 'Natasha Romanoff',
            'gender' => 'Female',
            'phone' => '+1 555 1010',
        ]);

        $consultation = Consultation::create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'consultation_date' => now()->toDateString(),
            'symptoms' => 'Wrist sprain',
            'diagnosis' => 'Grade 1 Ligament Sprain',
            'vitals' => ['blood_pressure' => '110/70', 'heart_rate' => '65'],
            'medical_notes' => 'Minimal edema on dorsal wrist aspect.',
        ]);

        $response = $this->actingAs($doctor)->get("/consultations/{$consultation->id}");
        $response->assertStatus(200);
        $response->assertSee('Natasha Romanoff');
        $response->assertSee('Grade 1 Ligament Sprain');
        $response->assertSee('110/70');
    }

    public function test_can_update_consultation(): void
    {
        $doctor = User::factory()->create([
            'role' => User::ROLE_ADMIN_DOCTOR,
            'status' => 'active',
        ]);

        $patient = Patient::create([
            'name' => 'Tony Stark',
            'gender' => 'Male',
            'phone' => '+1 555 2020',
        ]);

        $consultation = Consultation::create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'consultation_date' => now()->toDateString(),
            'symptoms' => 'Mild chest discomfort',
            'diagnosis' => 'Costochondritis',
        ]);

        $response = $this->actingAs($doctor)->put("/consultations/{$consultation->id}", [
            'patient_id' => $patient->id,
            'consultation_date' => now()->toDateString(),
            'symptoms' => 'Mild chest discomfort resolved',
            'diagnosis' => 'Resolved Costochondritis',
            'medical_notes' => 'Tender palpation at costosternal junction subsided.',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('consultations', [
            'id' => $consultation->id,
            'diagnosis' => 'Resolved Costochondritis',
        ]);
    }

    public function test_can_delete_consultation(): void
    {
        $doctor = User::factory()->create([
            'role' => User::ROLE_ADMIN_DOCTOR,
            'status' => 'active',
        ]);

        $patient = Patient::create([
            'name' => 'Thor Odinson',
            'gender' => 'Male',
            'phone' => '+1 555 3030',
        ]);

        $consultation = Consultation::create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'consultation_date' => now()->toDateString(),
            'symptoms' => 'Electric shock sensations and tingling in fingers',
            'diagnosis' => 'Acute Lightning Strike Aftereffects',
        ]);

        $response = $this->actingAs($doctor)->delete("/consultations/{$consultation->id}");
        $response->assertRedirect();

        $this->assertSoftDeleted('consultations', [
            'id' => $consultation->id,
        ]);
    }
}

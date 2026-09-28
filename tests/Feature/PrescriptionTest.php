<?php

namespace Tests\Feature;

use App\Models\Patient;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\SettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PrescriptionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PermissionSeeder::class);
        $this->seed(SettingSeeder::class);
    }

    public function test_guests_cannot_access_prescriptions(): void
    {
        $response = $this->get('/prescriptions');
        $response->assertRedirect('/login');
    }

    public function test_staff_without_permission_cannot_access_prescriptions(): void
    {
        $accountant = User::factory()->create([
            'role' => User::ROLE_ACCOUNTANT,
            'status' => 'active',
        ]);

        $response = $this->actingAs($accountant)->get('/prescriptions');
        $response->assertStatus(403);
    }

    public function test_authorized_staff_can_view_prescriptions_list(): void
    {
        $doctor = User::factory()->create([
            'role' => User::ROLE_ADMIN_DOCTOR,
            'status' => 'active',
        ]);

        $patient = Patient::create([
            'name' => 'Eleanor Vance',
            'gender' => 'Female',
            'phone' => '+1 555 9876',
        ]);

        $prescription = Prescription::create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'prescription_date' => now()->toDateString(),
            'diagnosis' => 'Acute Bronchitis',
        ]);

        PrescriptionItem::create([
            'prescription_id' => $prescription->id,
            'medicine_name' => 'Amoxicillin 500mg',
            'dosage' => '500 mg',
            'frequency' => 'TDS',
            'duration' => '5 days',
        ]);

        $response = $this->actingAs($doctor)->get('/prescriptions');
        $response->assertStatus(200);
        $response->assertSee('Digital Prescriptions');
        $response->assertSee('Eleanor Vance');
        $response->assertSee($prescription->prescription_number);
    }

    public function test_prescriptions_search_filter(): void
    {
        $doctor = User::factory()->create([
            'role' => User::ROLE_ADMIN_DOCTOR,
            'status' => 'active',
        ]);

        $patient1 = Patient::create([
            'name' => 'Sarah Connor',
            'gender' => 'Female',
            'phone' => '+1 555 1111',
        ]);

        $patient2 = Patient::create([
            'name' => 'John Wick',
            'gender' => 'Male',
            'phone' => '+1 555 2222',
        ]);

        $rx1 = Prescription::create([
            'patient_id' => $patient1->id,
            'doctor_id' => $doctor->id,
            'prescription_date' => now()->toDateString(),
            'diagnosis' => 'Hypertension',
        ]);

        $rx2 = Prescription::create([
            'patient_id' => $patient2->id,
            'doctor_id' => $doctor->id,
            'prescription_date' => now()->toDateString(),
            'diagnosis' => 'Migraine',
        ]);

        $response = $this->actingAs($doctor)->get('/prescriptions?search=Connor');
        $response->assertStatus(200);
        $response->assertSee('Sarah Connor');
        $response->assertDontSee('John Wick');
    }

    public function test_prescription_list_rejects_unknown_date_preset(): void
    {
        $doctor = User::factory()->create([
            'role' => User::ROLE_ADMIN_DOCTOR,
            'status' => 'active',
        ]);

        $response = $this->actingAs($doctor)->get('/prescriptions?date_preset=forever');

        $response->assertSessionHasErrors('date_preset');
    }

    public function test_can_render_prescription_create_page(): void
    {
        $doctor = User::factory()->create([
            'role' => User::ROLE_ADMIN_DOCTOR,
            'status' => 'active',
        ]);

        $response = $this->actingAs($doctor)->get('/prescriptions/create');
        $response->assertStatus(200);
        $response->assertSee('Author New Digital Prescription');
        $response->assertSee('Prescribed Medications');
    }

    public function test_can_store_prescription_with_items(): void
    {
        $doctor = User::factory()->create([
            'role' => User::ROLE_ADMIN_DOCTOR,
            'status' => 'active',
        ]);

        $patient = Patient::create([
            'name' => 'Bruce Wayne',
            'gender' => 'Male',
            'phone' => '+1 555 3333',
        ]);

        $payload = [
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'prescription_date' => now()->toDateString(),
            'diagnosis' => 'Muscle Contusion',
            'symptoms' => 'Shoulder pain following trauma',
            'clinical_notes' => 'Range of motion moderately reduced',
            'advice' => 'Ice packs and rest for 48 hours',
            'tests' => ['Right Shoulder X-Ray AP / Lat'],
            'follow_up_date' => now()->addDays(7)->toDateString(),
            'items' => [
                [
                    'medicine_name' => 'Ibuprofen 400mg',
                    'dosage' => '400 mg',
                    'frequency' => 'TDS',
                    'duration' => '3 days',
                    'route' => 'Oral',
                    'timing' => 'After Meals',
                    'instructions' => 'Take with water after food.',
                ],
                [
                    'medicine_name' => 'Thiocolchicoside 4mg',
                    'dosage' => '4 mg',
                    'frequency' => 'BD',
                    'duration' => '5 days',
                    'route' => 'Oral',
                    'timing' => 'After Meals',
                    'instructions' => 'Muscle relaxant at morning and night.',
                ],
            ],
        ];

        $response = $this->actingAs($doctor)->post('/prescriptions', $payload);
        $response->assertRedirect();

        $this->assertDatabaseHas('prescriptions', [
            'patient_id' => $patient->id,
            'diagnosis' => 'Muscle Contusion',
        ]);

        $this->assertDatabaseHas('prescription_items', [
            'medicine_name' => 'Ibuprofen 400mg',
            'dosage' => '400 mg',
        ]);

        $this->assertDatabaseHas('prescription_items', [
            'medicine_name' => 'Thiocolchicoside 4mg',
            'duration' => '5 days',
        ]);
    }

    public function test_can_view_single_prescription(): void
    {
        $doctor = User::factory()->create([
            'role' => User::ROLE_ADMIN_DOCTOR,
            'status' => 'active',
        ]);

        $patient = Patient::create([
            'name' => 'Peter Parker',
            'gender' => 'Male',
            'phone' => '+1 555 4444',
        ]);

        $rx = Prescription::create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'prescription_date' => now()->toDateString(),
            'diagnosis' => 'Arachnid Sting Reaction',
        ]);

        PrescriptionItem::create([
            'prescription_id' => $rx->id,
            'medicine_name' => 'Cetirizine 10mg',
            'dosage' => '10 mg',
            'frequency' => 'OD',
            'duration' => '5 days',
            'route' => 'Oral',
            'timing' => 'Bedtime',
            'instructions' => 'Take before sleep.',
        ]);

        $response = $this->actingAs($doctor)->get("/prescriptions/{$rx->id}");
        $response->assertStatus(200);
        $response->assertSee('Peter Parker');
        $response->assertSee('Cetirizine 10mg');
        $response->assertSee($rx->prescription_number);
    }

    public function test_can_view_prescription_print_layout(): void
    {
        $doctor = User::factory()->create([
            'role' => User::ROLE_ADMIN_DOCTOR,
            'status' => 'active',
        ]);

        $patient = Patient::create([
            'name' => 'Diana Prince',
            'gender' => 'Female',
            'phone' => '+1 555 5555',
        ]);

        $rx = Prescription::create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'prescription_date' => now()->toDateString(),
            'diagnosis' => 'Routine General Wellness',
            'tests' => ['Complete Hemogram', 'Serum Vitamin D3'],
        ]);

        PrescriptionItem::create([
            'prescription_id' => $rx->id,
            'medicine_name' => 'Cholecalciferol 60K',
            'dosage' => '60,000 IU',
            'frequency' => 'Weekly',
            'duration' => '8 Weeks',
            'route' => 'Oral',
            'timing' => 'With Milk',
            'instructions' => 'Take once every Sunday morning.',
        ]);

        $response = $this->actingAs($doctor)->get("/prescriptions/{$rx->id}/print");
        $response->assertStatus(200);
        $response->assertSee('Diana Prince');
        $response->assertSee('Cholecalciferol 60K');
        $response->assertSee('Serum Vitamin D3');
    }

    public function test_can_update_prescription(): void
    {
        $doctor = User::factory()->create([
            'role' => User::ROLE_ADMIN_DOCTOR,
            'status' => 'active',
        ]);

        $patient = Patient::create([
            'name' => 'Clark Kent',
            'gender' => 'Male',
            'phone' => '+1 555 6666',
        ]);

        $rx = Prescription::create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'prescription_date' => now()->toDateString(),
            'diagnosis' => 'Mild Allergy',
        ]);

        $response = $this->actingAs($doctor)->put("/prescriptions/{$rx->id}", [
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'prescription_date' => now()->toDateString(),
            'diagnosis' => 'Updated: Allergic Conjunctivitis',
            'items' => [
                [
                    'medicine_name' => 'Olopatadine Eye Drops',
                    'dosage' => '1 drop',
                    'frequency' => 'BD',
                    'duration' => '7 days',
                    'route' => 'Ophthalmic',
                    'timing' => 'After Meals',
                    'instructions' => 'Instill in both eyes.',
                ],
            ],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('prescriptions', [
            'id' => $rx->id,
            'diagnosis' => 'Updated: Allergic Conjunctivitis',
        ]);
        $this->assertDatabaseHas('prescription_items', [
            'prescription_id' => $rx->id,
            'medicine_name' => 'Olopatadine Eye Drops',
        ]);
    }

    public function test_can_delete_prescription(): void
    {
        $doctor = User::factory()->create([
            'role' => User::ROLE_ADMIN_DOCTOR,
            'status' => 'active',
        ]);

        $patient = Patient::create([
            'name' => 'Barry Allen',
            'gender' => 'Male',
            'phone' => '+1 555 7777',
        ]);

        $rx = Prescription::create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'prescription_date' => now()->toDateString(),
            'diagnosis' => 'Exhaustion',
        ]);

        $response = $this->actingAs($doctor)->delete("/prescriptions/{$rx->id}");
        $response->assertRedirect('/prescriptions');

        $this->assertSoftDeleted('prescriptions', [
            'id' => $rx->id,
        ]);
    }
}

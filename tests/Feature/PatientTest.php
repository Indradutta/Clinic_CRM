<?php

namespace Tests\Feature;

use App\Models\Patient;
use App\Models\PatientDocument;
use App\Models\PatientNote;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PatientTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PermissionSeeder::class);
    }

    public function test_guest_cannot_access_patients(): void
    {
        $response = $this->get('/patients');
        $response->assertRedirect('/login');
    }

    public function test_authorized_user_can_view_patients_directory(): void
    {
        $doctor = User::factory()->create([
            'role' => User::ROLE_ADMIN_DOCTOR,
            'status' => 'active',
        ]);

        Patient::create([
            'name' => 'Alice Walker',
            'gender' => 'Female',
            'phone' => '+1 (555) 123-4567',
            'email' => 'alice@example.com',
        ]);

        $response = $this->actingAs($doctor)->get('/patients');

        $response->assertStatus(200);
        $response->assertSee('Patient Directory');
        $response->assertSee('Alice Walker');
    }

    public function test_patient_search_and_filters_work(): void
    {
        $doctor = User::factory()->create([
            'role' => User::ROLE_ADMIN_DOCTOR,
            'status' => 'active',
        ]);

        $p1 = Patient::create([
            'name' => 'John Doe',
            'gender' => 'Male',
            'blood_group' => 'O+',
            'age' => 45,
            'phone' => '1112223333',
        ]);

        $p2 = Patient::create([
            'name' => 'Sarah Connor',
            'gender' => 'Female',
            'blood_group' => 'A+',
            'age' => 30,
            'phone' => '9998887777',
        ]);

        // Search by name
        $response = $this->actingAs($doctor)->get('/patients?search=John');
        $response->assertSee('John Doe');
        $response->assertDontSee('Sarah Connor');

        // Filter by gender
        $response = $this->actingAs($doctor)->get('/patients?gender=Female');
        $response->assertSee('Sarah Connor');
        $response->assertDontSee('John Doe');

        // Filter by blood group
        $response = $this->actingAs($doctor)->get('/patients?blood_group=O%2B');
        $response->assertSee('John Doe');
        $response->assertDontSee('Sarah Connor');
    }

    public function test_can_register_new_patient_with_all_18_fields(): void
    {
        $doctor = User::factory()->create([
            'role' => User::ROLE_ADMIN_DOCTOR,
            'status' => 'active',
        ]);

        $payload = [
            'name' => 'Michael Scott',
            'dob' => '1965-03-15',
            'age' => 61,
            'gender' => 'Male',
            'blood_group' => 'B+',
            'phone' => '9876543210',
            'email' => 'mscott@dundermifflin.com',
            'address' => '1725 Slough Avenue, Scranton, PA',
            'emergency_contact_name' => 'Dwight Schrute',
            'emergency_contact_phone' => '9876543211',
            'emergency_contact_relation' => 'Assistant to the Regional Manager',
            'allergies' => 'Peanuts, Penicillin',
            'chronic_conditions' => 'Hypertension',
            'current_medications' => 'Lisinopril 10mg once daily',
            'past_surgeries' => 'Appendectomy (1998)',
            'family_medical_history' => 'Father had heart disease',
            'smoking_status' => 'Never',
            'alcohol_consumption' => 'Occasional',
        ];

        $response = $this->actingAs($doctor)->post('/patients', $payload);

        $this->assertDatabaseHas('patients', [
            'name' => 'Michael Scott',
            'email' => 'mscott@dundermifflin.com',
            'blood_group' => 'B+',
            'allergies' => 'Peanuts, Penicillin',
        ]);

        $patient = Patient::where('name', 'Michael Scott')->first();
        $this->assertNotNull($patient);
        $this->assertStringStartsWith('PAT-', $patient->patient_id);

        $response->assertRedirect(route('patients.show', $patient));
    }

    public function test_patient_phone_must_be_a_ten_digit_indian_mobile_number(): void
    {
        $doctor = User::factory()->create([
            'role' => User::ROLE_ADMIN_DOCTOR,
            'status' => 'active',
        ]);

        $response = $this->actingAs($doctor)->post('/patients', [
            'name' => 'Invalid Phone Patient',
            'gender' => 'Female',
            'phone' => '12345678901',
        ]);

        $response->assertSessionHasErrors('phone');
        $this->assertDatabaseMissing('patients', ['name' => 'Invalid Phone Patient']);
    }

    public function test_patient_age_must_match_the_date_of_birth(): void
    {
        $doctor = User::factory()->create([
            'role' => User::ROLE_ADMIN_DOCTOR,
            'status' => 'active',
        ]);

        $response = $this->actingAs($doctor)->post('/patients', [
            'name' => 'Age Mismatch Patient',
            'dob' => '2000-01-01',
            'age' => 18,
            'gender' => 'Female',
            'phone' => '9876543210',
        ]);

        $response->assertSessionHasErrors('age');
        $this->assertDatabaseMissing('patients', ['name' => 'Age Mismatch Patient']);
    }

    public function test_can_view_patient_profile_and_all_tabs(): void
    {
        $doctor = User::factory()->create([
            'role' => User::ROLE_ADMIN_DOCTOR,
            'status' => 'active',
        ]);

        $patient = Patient::create([
            'name' => 'Emily Blunt',
            'gender' => 'Female',
            'phone' => '1234567890',
            'blood_group' => 'AB+',
        ]);

        // Default tab (personal)
        $response = $this->actingAs($doctor)->get(route('patients.show', $patient));
        $response->assertStatus(200);
        $response->assertSee('Emily Blunt');
        $response->assertSee($patient->patient_id);

        // Medical tab
        $response = $this->actingAs($doctor)->get(route('patients.show', ['patient' => $patient, 'tab' => 'medical']));
        $response->assertStatus(200);
        $response->assertSee('Clinical Medical Record');

        // Notes tab
        $response = $this->actingAs($doctor)->get(route('patients.show', ['patient' => $patient, 'tab' => 'notes']));
        $response->assertStatus(200);
        $response->assertSee('Add Clinical or Administrative Note');
    }

    public function test_can_update_patient_information(): void
    {
        $doctor = User::factory()->create([
            'role' => User::ROLE_ADMIN_DOCTOR,
            'status' => 'active',
        ]);

        $patient = Patient::create([
            'name' => 'Original Name',
            'gender' => 'Male',
            'phone' => '9876543210',
        ]);

        $response = $this->actingAs($doctor)->put(route('patients.update', $patient), [
            'name' => 'Updated Name',
            'gender' => 'Male',
            'phone' => '9876543212',
            'allergies' => 'Latex',
        ]);

        $response->assertRedirect(route('patients.show', $patient));

        $this->assertDatabaseHas('patients', [
            'id' => $patient->id,
            'name' => 'Updated Name',
            'allergies' => 'Latex',
        ]);
    }

    public function test_can_add_and_delete_clinical_notes(): void
    {
        $doctor = User::factory()->create([
            'role' => User::ROLE_ADMIN_DOCTOR,
            'status' => 'active',
        ]);

        $patient = Patient::create([
            'name' => 'Gregory House',
            'gender' => 'Male',
            'phone' => '5550001',
        ]);

        // Add note
        $response = $this->actingAs($doctor)->post(route('patients.notes.store', $patient), [
            'title' => 'Follow-up observation',
            'note' => 'Patient reports reduced chronic pain after dosage modification.',
        ]);

        $response->assertRedirect(route('patients.show', ['patient' => $patient, 'tab' => 'notes']));

        $this->assertDatabaseHas('patient_notes', [
            'patient_id' => $patient->id,
            'title' => 'Follow-up observation',
        ]);

        $note = PatientNote::where('patient_id', $patient->id)->first();

        // Delete note
        $delResponse = $this->actingAs($doctor)->delete(route('patients.notes.destroy', $note));
        $delResponse->assertRedirect(route('patients.show', ['patient' => $patient, 'tab' => 'notes']));

        $this->assertDatabaseMissing('patient_notes', [
            'id' => $note->id,
        ]);
    }

    public function test_can_upload_and_download_medical_document(): void
    {
        Storage::fake('local');

        $doctor = User::factory()->create([
            'role' => User::ROLE_ADMIN_DOCTOR,
            'status' => 'active',
        ]);

        $patient = Patient::create([
            'name' => 'James Wilson',
            'gender' => 'Male',
            'phone' => '5550002',
        ]);

        $file = UploadedFile::fake()->create('blood_test.pdf', 250, 'application/pdf');

        $response = $this->actingAs($doctor)->post(route('patients.documents.store', $patient), [
            'title' => 'Comprehensive Metabolic Panel',
            'document' => $file,
        ]);

        $response->assertRedirect(route('patients.show', ['patient' => $patient, 'tab' => 'documents']));

        $this->assertDatabaseHas('patient_documents', [
            'patient_id' => $patient->id,
            'title' => 'Comprehensive Metabolic Panel',
            'file_type' => 'pdf',
        ]);

        $doc = PatientDocument::where('patient_id', $patient->id)->first();
        Storage::disk('local')->assertExists($doc->file_path);

        // Download document
        $downloadResponse = $this->actingAs($doctor)->get(route('patients.documents.download', $doc));
        $downloadResponse->assertStatus(200);

        // Delete document
        $delResponse = $this->actingAs($doctor)->delete(route('patients.documents.destroy', $doc));
        $this->assertDatabaseMissing('patient_documents', ['id' => $doc->id]);
        Storage::disk('local')->assertMissing($doc->file_path);
    }

    public function test_can_archive_patient(): void
    {
        $doctor = User::factory()->create([
            'role' => User::ROLE_ADMIN_DOCTOR,
            'status' => 'active',
        ]);

        $patient = Patient::create([
            'name' => 'Archivable Patient',
            'gender' => 'Male',
            'phone' => '5550003',
        ]);

        $response = $this->actingAs($doctor)->delete(route('patients.destroy', $patient));
        $response->assertRedirect(route('patients.index'));

        $this->assertSoftDeleted('patients', [
            'id' => $patient->id,
        ]);
    }
}

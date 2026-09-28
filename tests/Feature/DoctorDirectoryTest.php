<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DoctorDirectoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_add_a_doctor_and_save_professional_details(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN_DOCTOR, 'status' => 'active']);

        $response = $this->actingAs($admin)->post(route('doctors.store'), [
            'name' => 'Dr. Mira Sen',
            'email' => 'mira@example.com',
            'username' => 'mira_sen',
            'phone' => '9876543210',
            'specialty' => 'Cardiology',
            'education' => 'MBBS, MD Cardiology',
            'license_number' => 'MED-123456',
            'years_experience' => 12,
            'address' => 'Kolkata',
            'bio' => 'Consultant cardiologist.',
            'consultation_fee' => 800,
        ]);

        $response->assertRedirect(route('doctors.index'));
        $doctor = User::where('email', 'mira@example.com')->firstOrFail();
        $this->assertSame(User::ROLE_DOCTOR, $doctor->role);
        $this->assertSame('Cardiology', $doctor->doctorProfile->specialty);
        $this->assertSame('MED-123456', $doctor->doctorProfile->license_number);
        $this->assertDatabaseHas('doctor_profiles', ['user_id' => $doctor->id, 'education' => 'MBBS, MD Cardiology']);
    }

    public function test_staff_without_doctor_access_cannot_open_the_directory(): void
    {
        $staff = User::factory()->create(['role' => User::ROLE_NURSE, 'status' => 'active']);

        $this->actingAs($staff)->get(route('doctors.index'))->assertForbidden();

    }
}

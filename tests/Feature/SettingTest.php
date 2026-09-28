<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\SettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SettingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PermissionSeeder::class);
        $this->seed(SettingSeeder::class);
    }

    public function test_unauthenticated_guests_cannot_access_settings(): void
    {
        $response = $this->get('/settings');

        $response->assertRedirect('/login');
    }

    public function test_staff_without_permission_cannot_access_settings(): void
    {
        $receptionist = User::factory()->create([
            'role' => User::ROLE_RECEPTIONIST,
            'status' => 'active',
        ]);

        $response = $this->actingAs($receptionist)->get('/settings');

        $response->assertStatus(403);
    }

    public function test_admin_doctor_can_view_settings(): void
    {
        $doctor = User::factory()->create([
            'role' => User::ROLE_ADMIN_DOCTOR,
            'status' => 'active',
        ]);

        $response = $this->actingAs($doctor)->get('/settings');

        $response->assertStatus(200);
        $response->assertSee('Practice Configuration');
        $response->assertSee('Doctor Profile Information');
    }

    public function test_admin_can_update_doctor_profile_settings(): void
    {
        $doctor = User::factory()->create([
            'role' => User::ROLE_ADMIN_DOCTOR,
            'status' => 'active',
        ]);

        $response = $this->actingAs($doctor)->post('/settings/update/doctor', [
            'doctor_name' => 'Dr. Robert Koch',
            'doctor_qualification' => 'MD, PhD',
            'doctor_specialization' => 'Infectious Disease',
            'doctor_registration_number' => 'MED-123456',
            'doctor_phone' => '9876544332',
            'doctor_email' => 'koch@mediflow.com',
            'doctor_clinic_name' => 'Koch Institute Clinic',
            'doctor_clinic_address' => '77 Research Blvd',
        ]);

        $response->assertRedirect(route('settings.index', ['tab' => 'doctor']));
        $this->assertEquals('Dr. Robert Koch', Setting::get('doctor_name'));
        $this->assertEquals('Infectious Disease', Setting::get('doctor_specialization'));
    }

    public function test_admin_can_update_appointment_settings(): void
    {
        $doctor = User::factory()->create([
            'role' => User::ROLE_ADMIN_DOCTOR,
            'status' => 'active',
        ]);

        $response = $this->actingAs($doctor)->post('/settings/update/appointment', [
            'appointment_duration' => '30',
            'appointment_max_per_day' => '25',
            'appointment_working_hours_start' => '08:00',
            'appointment_working_hours_end' => '17:00',
            'appointment_working_days' => ['Monday', 'Wednesday', 'Friday'],
            'appointment_reminders' => '1',
        ]);

        $response->assertRedirect(route('settings.index', ['tab' => 'appointment']));
        $this->assertEquals('30', Setting::get('appointment_duration'));
        $this->assertEquals('25', Setting::get('appointment_max_per_day'));
    }

    public function test_user_can_update_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('old-password'),
            'status' => 'active',
            'role' => User::ROLE_ADMIN_DOCTOR,
        ]);

        $response = $this->actingAs($user)->post('/settings/password', [
            'current_password' => 'old-password',
            'password' => 'NewSecret123!',
            'password_confirmation' => 'NewSecret123!',
        ]);

        $response->assertRedirect(route('settings.index', ['tab' => 'security']));
        $this->assertTrue(Hash::check('NewSecret123!', $user->fresh()->password));
    }

    public function test_user_cannot_update_password_with_wrong_current_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('correct-password'),
            'status' => 'active',
            'role' => User::ROLE_ADMIN_DOCTOR,
        ]);

        $response = $this->actingAs($user)->post('/settings/password', [
            'current_password' => 'wrong-password',
            'password' => 'NewSecret123!',
            'password_confirmation' => 'NewSecret123!',
        ]);

        $response->assertSessionHasErrors('current_password');
        $this->assertTrue(Hash::check('correct-password', $user->fresh()->password));
    }

    public function test_user_cannot_change_password_to_a_weak_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('old-password'),
            'status' => 'active',
            'role' => User::ROLE_ADMIN_DOCTOR,
        ]);

        $response = $this->actingAs($user)->post('/settings/password', [
            'current_password' => 'old-password',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertTrue(Hash::check('old-password', $user->fresh()->password));
    }
}

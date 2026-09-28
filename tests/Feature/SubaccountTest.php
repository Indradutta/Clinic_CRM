<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubaccountTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PermissionSeeder::class);
    }

    public function test_guests_cannot_access_subaccounts(): void
    {
        $response = $this->get('/subaccounts');

        $response->assertRedirect('/login');
    }

    public function test_staff_without_permission_cannot_access_subaccounts(): void
    {
        $receptionist = User::factory()->create([
            'role' => User::ROLE_RECEPTIONIST,
            'status' => 'active',
        ]);

        $response = $this->actingAs($receptionist)->get('/subaccounts');

        $response->assertStatus(403);
    }

    public function test_admin_doctor_can_view_subaccounts(): void
    {
        $doctor = User::factory()->create([
            'role' => User::ROLE_ADMIN_DOCTOR,
            'status' => 'active',
        ]);

        $response = $this->actingAs($doctor)->get('/subaccounts');

        $response->assertStatus(200);
        $response->assertSee('Staff Directory');
    }

    public function test_admin_can_create_new_subaccount_with_permissions(): void
    {
        $doctor = User::factory()->create([
            'role' => User::ROLE_ADMIN_DOCTOR,
            'status' => 'active',
        ]);

        $permission = Permission::first();

        $response = $this->actingAs($doctor)->post('/subaccounts', [
            'name' => 'John Receptionist',
            'username' => 'john_rec',
            'email' => 'john@clinic.com',
            'phone' => '9876543210',
            'password' => 'Secret123!',
            'role' => User::ROLE_RECEPTIONIST,
            'status' => 'active',
            'permissions' => [$permission->id],
        ]);

        $response->assertRedirect('/subaccounts');
        $this->assertDatabaseHas('users', [
            'username' => 'john_rec',
            'email' => 'john@clinic.com',
            'role' => User::ROLE_RECEPTIONIST,
        ]);

        $createdUser = User::where('username', 'john_rec')->first();
        $this->assertTrue($createdUser->permissions->contains($permission));
    }

    public function test_admin_can_toggle_subaccount_status(): void
    {
        $doctor = User::factory()->create([
            'role' => User::ROLE_ADMIN_DOCTOR,
            'status' => 'active',
        ]);

        $staff = User::factory()->create([
            'role' => User::ROLE_NURSE,
            'status' => 'active',
        ]);

        $response = $this->actingAs($doctor)->post("/subaccounts/{$staff->id}/toggle-status");

        $response->assertRedirect();
        $this->assertEquals('inactive', $staff->fresh()->status);
    }
}

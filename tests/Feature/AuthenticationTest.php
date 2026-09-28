<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\AuthEmailOtpNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('Sign in');
    }

    public function test_login_code_verification_page_is_no_longer_available(): void
    {
        $this->get('/login/verify')->assertNotFound();
        $this->post('/login/verify')->assertNotFound();
    }

    public function test_local_login_screen_offers_seeded_demo_accounts(): void
    {
        $originalEnvironment = app()->environment();
        app()->detectEnvironment(fn (): string => 'local');

        $response = $this->get('/login');
        app()->detectEnvironment(fn (): string => $originalEnvironment);

        $response->assertStatus(200);
        $response->assertSee('demo-account');
        $response->assertSee('doctor@mediflow.com');
        $response->assertSee('nurse@mediflow.com');
        $response->assertSee('data-password="password"', false);
    }

    public function test_users_can_authenticate_using_username(): void
    {
        $user = User::factory()->create([
            'username' => 'teststaff',
            'password' => bcrypt('password123'),
            'status' => 'active',
        ]);

        $response = $this->signIn($user, 'teststaff');
        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('dashboard'));
    }

    public function test_users_without_dashboard_access_land_on_a_section_they_can_view(): void
    {
        $user = User::factory()->create([
            'username' => 'nurse',
            'password' => bcrypt('password123'),
            'role' => User::ROLE_NURSE,
            'status' => 'active',
        ]);

        $response = $this->signIn($user, 'nurse');
        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('appointments.index'));
    }

    public function test_users_can_authenticate_using_email(): void
    {
        $user = User::factory()->create([
            'email' => 'doctor@example.com',
            'password' => bcrypt('password123'),
            'status' => 'active',
        ]);

        $response = $this->signIn($user, 'doctor@example.com');
        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('dashboard'));
    }

    public function test_users_can_sign_in_without_verifying_their_email(): void
    {
        Notification::fake();
        $user = User::factory()->create([
            'email' => 'unverified@example.com',
            'email_verified_at' => null,
            'username' => 'unverified-staff',
            'password' => bcrypt('password123'),
            'status' => 'active',
        ]);

        $response = $this->signIn($user, 'unverified-staff');

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('dashboard'));
        Notification::assertNothingSent();
    }

    public function test_users_cannot_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create([
            'username' => 'staff1',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post('/login', [
            'login' => 'staff1',
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
        $response->assertSessionHas('error', 'Username/email or password is incorrect. Check your details and try again.');
    }

    public function test_login_reports_missing_username_and_password(): void
    {
        $response = $this->post('/login', []);

        $this->assertGuest();
        $response->assertSessionHasErrors([
            'login' => 'Enter your username or email.',
            'password' => 'Enter your password.',
        ]);
    }

    public function test_inactive_users_cannot_authenticate(): void
    {
        $user = User::factory()->create([
            'username' => 'deactivated_user',
            'password' => bcrypt('password123'),
            'status' => 'inactive',
        ]);

        $response = $this->post('/login', [
            'login' => 'deactivated_user',
            'password' => 'password123',
        ]);

        $this->assertGuest();
        $response->assertSessionHas('error');
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create(['status' => 'active']);

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/login');
    }

    public function test_password_can_be_reset_with_a_one_time_email_code(): void
    {
        Notification::fake();
        $user = User::factory()->create(['email' => 'staff@example.com', 'status' => 'active']);

        $this->post(route('password.email'), ['email' => $user->email])->assertRedirect(route('password.reset'));
        $code = Notification::sent($user, AuthEmailOtpNotification::class)->first()->code;

        $this->post(route('password.update'), [
            'code' => $code,
            'password' => 'BetterPass123!',
            'password_confirmation' => 'BetterPass123!',
        ])->assertRedirect(route('login'));

        $this->assertTrue(password_verify('BetterPass123!', $user->fresh()->password));
    }

    private function signIn(User $user, string $login): TestResponse
    {
        return $this->post('/login', ['login' => $login, 'password' => 'password123']);
    }
}

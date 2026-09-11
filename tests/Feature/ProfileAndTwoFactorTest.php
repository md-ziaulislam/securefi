<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PragmaRX\Google2FA\Google2FA;
use Tests\TestCase;

class ProfileAndTwoFactorTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);

        $this->user = User::factory()->create([
            'name' => 'Profile Test User',
            'email' => 'profile@securofi.tech',
            'password' => Hash::make('Password123!'),
        ]);
        $this->user->assignRole('Author');
    }

    public function test_staff_can_access_profile_and_users_profile_routes(): void
    {
        $response1 = $this->actingAs($this->user)->get(route('admin.profile.index'));
        $response1->assertOk();
        $response1->assertSee('Staff Profile & Security');

        $response2 = $this->actingAs($this->user)->get(route('admin.users.profile'));
        $response2->assertOk();
        $response2->assertSee('Staff Profile & Security');
    }

    public function test_user_can_update_profile_information(): void
    {
        $response = $this->actingAs($this->user)->put(route('admin.profile.update'), [
            'name' => 'Updated User Name',
            'email' => 'updated@securofi.tech',
            'bio' => 'Lead Cybersecurity Analyst at SecuroFi',
        ]);

        $response->assertRedirect(route('admin.profile.index'));
        $response->assertSessionHas('success');

        $this->user->refresh();
        $this->assertEquals('Updated User Name', $this->user->name);
        $this->assertEquals('updated@securofi.tech', $this->user->email);
        $this->assertEquals('Lead Cybersecurity Analyst at SecuroFi', $this->user->bio);
    }

    public function test_user_can_update_password(): void
    {
        $response = $this->actingAs($this->user)->post(route('admin.profile.password'), [
            'current_password' => 'Password123!',
            'password' => 'NewSecurePass999!',
            'password_confirmation' => 'NewSecurePass999!',
        ]);

        $response->assertRedirect(route('admin.profile.index'));
        $response->assertSessionHas('success');

        $this->user->refresh();
        $this->assertTrue(Hash::check('NewSecurePass999!', $this->user->password));
    }

    public function test_user_can_enable_and_confirm_two_factor(): void
    {
        $google2fa = new Google2FA();
        $secret = $google2fa->generateSecretKey();
        $code = $google2fa->getCurrentOtp($secret);

        $response = $this->actingAs($this->user)->post(route('admin.profile.2fa.confirm'), [
            'secret' => $secret,
            'code' => $code,
        ]);

        $response->assertRedirect(route('admin.profile.index'));
        $response->assertSessionHas('success');
        $response->assertSessionHas('show_recovery_codes');

        $this->user->refresh();
        $this->assertTrue($this->user->hasTwoFactorEnabled());
        $this->assertEquals($secret, $this->user->two_factor_secret);
        $this->assertNotNull($this->user->two_factor_confirmed_at);
        $this->assertNotNull($this->user->two_factor_recovery_codes);
    }

    public function test_user_can_disable_two_factor_with_correct_password(): void
    {
        // First enable 2FA
        $this->user->update([
            'two_factor_secret' => 'TESTSECRETKEY1234',
            'two_factor_confirmed_at' => now(),
            'two_factor_recovery_codes' => json_encode(['CODE-1234', 'CODE-5678']),
        ]);
        $this->assertTrue($this->user->hasTwoFactorEnabled());

        // Attempt disable with incorrect password
        $failResponse = $this->actingAs($this->user)->post(route('admin.profile.2fa.disable'), [
            'disable_password' => 'WrongPassword',
        ]);
        $failResponse->assertSessionHasErrors('disable_password');
        $this->assertTrue($this->user->fresh()->hasTwoFactorEnabled());

        // Disable with correct password
        $successResponse = $this->actingAs($this->user)->post(route('admin.profile.2fa.disable'), [
            'disable_password' => 'Password123!',
        ]);
        $successResponse->assertRedirect(route('admin.profile.index'));
        $successResponse->assertSessionHas('success');

        $this->user->refresh();
        $this->assertFalse($this->user->hasTwoFactorEnabled());
        $this->assertNull($this->user->two_factor_secret);
        $this->assertNull($this->user->two_factor_confirmed_at);
    }

    public function test_login_flow_redirects_to_2fa_and_allows_login_via_totp(): void
    {
        $google2fa = new Google2FA();
        $secret = $google2fa->generateSecretKey();
        $this->user->update([
            'two_factor_secret' => $secret,
            'two_factor_confirmed_at' => now(),
            'two_factor_recovery_codes' => json_encode(['RECOVERY-001']),
        ]);

        // 1. Submit email and password to /login
        $loginResponse = $this->post(route('login.submit'), [
            'email' => $this->user->email,
            'password' => 'Password123!',
        ]);

        $loginResponse->assertRedirect(route('auth.2fa'));
        $this->assertGuest();

        // 2. Submit valid TOTP
        $otp = $google2fa->getCurrentOtp($secret);
        $verifyResponse = $this->withSession(['login.2fa.user_id' => $this->user->id])
            ->post(route('auth.2fa.verify'), [
                'code' => $otp,
            ]);

        $verifyResponse->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($this->user);
    }

    public function test_login_flow_allows_recovery_code_when_totp_is_unavailable(): void
    {
        $this->user->update([
            'two_factor_secret' => 'SECRETKEY12345678',
            'two_factor_confirmed_at' => now(),
            'two_factor_recovery_codes' => json_encode(['EMERGENCY-KEY-001', 'EMERGENCY-KEY-002']),
        ]);

        // Submit emergency recovery code
        $verifyResponse = $this->withSession(['login.2fa.user_id' => $this->user->id])
            ->post(route('auth.2fa.verify'), [
                'code' => 'EMERGENCY-KEY-001',
            ]);

        $verifyResponse->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($this->user);

        // Emergency code should now be consumed
        $this->user->refresh();
        $remaining = json_decode($this->user->two_factor_recovery_codes, true);
        $this->assertCount(1, $remaining);
        $this->assertNotContains('EMERGENCY-KEY-001', $remaining);
        $this->assertContains('EMERGENCY-KEY-002', $remaining);
    }
}

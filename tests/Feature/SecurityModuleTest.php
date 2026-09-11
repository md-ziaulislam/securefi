<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_security_headers_are_present_on_web_routes(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-XSS-Protection', '1; mode=block');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
    }

    public function test_guests_cannot_access_security_console(): void
    {
        $response = $this->get('/admin/security');

        $response->assertRedirect('/login');
    }

    protected function getAdminUser(): User
    {
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Super Admin']);
        $user = User::where('email', 'admin@securofi.tech')->first() ?? User::factory()->create([
            'email' => 'admin@securofi.tech'
        ]);
        if (!$user->hasRole('Super Admin')) {
            $user->assignRole('Super Admin');
        }
        return $user;
    }

    public function test_authenticated_admin_can_access_security_console(): void
    {
        $user = $this->getAdminUser();

        $response = $this->actingAs($user)->get('/admin/security');
        $response->assertStatus(200);
        $response->assertSee('Security Hardening & Audit Trail');
        $response->assertSee('Login Audit Trail');
        $response->assertSee('System Security Health');
        $response->assertSee('Database Backups');
    }

    public function test_admin_can_access_all_security_tabs(): void
    {
        $user = $this->getAdminUser();

        $this->actingAs($user)->get('/admin/security?tab=audit')->assertStatus(200);
        $this->actingAs($user)->get('/admin/security?tab=health')->assertStatus(200);
        $this->actingAs($user)->get('/admin/security?tab=backups')->assertStatus(200);
        $this->actingAs($user)->get('/admin/security?tab=activity')->assertStatus(200);
    }

    public function test_admin_can_generate_and_delete_database_backup(): void
    {
        $user = $this->getAdminUser();

        $response = $this->actingAs($user)->post('/admin/security/backup');
        $response->assertSessionHas('success');

        $backupService = app(\App\Services\BackupService::class);
        $backups = $backupService->getBackups();
        $this->assertNotEmpty($backups);

        $latestFilename = $backups[0]['filename'];
        $delResponse = $this->actingAs($user)->delete("/admin/security/backup/{$latestFilename}");
        $delResponse->assertSessionHas('success');
    }
}

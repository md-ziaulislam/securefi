<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DeveloperProfileAndSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);

        $this->superAdmin = User::factory()->create([
            'name' => 'Super Admin',
            'email' => 'super@securofi.tech',
        ]);
        $this->superAdmin->assignRole('Super Admin');
    }

    public function test_public_pages_do_not_expose_admin_login_link(): void
    {
        // Check home page
        $homeResponse = $this->get('/');
        $homeResponse->assertOk();
        $homeResponse->assertDontSee('Admin Gateway');
        $homeResponse->assertDontSee(route('login'));

        // Check dev-info page
        $devResponse = $this->get('/dev-info');
        $devResponse->assertOk();
        $devResponse->assertDontSee('Admin Gateway');
        $devResponse->assertDontSee('Admin Control Hub');
        $devResponse->assertDontSee(route('login'));
        $devResponse->assertDontSee('PHP Runtime');
        $devResponse->assertDontSee('Framework Version');
    }

    public function test_dev_info_page_renders_developer_profile_properly(): void
    {
        Setting::set('dev_name', 'Ziaul Islam', 'dev');
        Setting::set('dev_title', 'Principal Software Architect', 'dev');
        Setting::set('dev_bio', 'Experienced systems architect specializing in high-load Laravel services.', 'dev');
        Setting::set('dev_skills', 'PHP, Laravel, MySQL, Docker, Kubernetes', 'dev');
        Setting::set('dev_github', 'https://github.com/ziaulislam', 'dev');

        $response = $this->get('/dev-info');
        $response->assertOk();
        $response->assertSee('Ziaul Islam');
        $response->assertSee('Principal Software Architect');
        $response->assertSee('Experienced systems architect');
        $response->assertSee('Laravel');
        $response->assertSee('Docker');
        $response->assertSee('https://github.com/ziaulislam');
    }

    public function test_admin_can_upload_and_remove_developer_photo(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('developer.jpg', 400, 400);

        $response = $this->actingAs($this->superAdmin)->post(route('admin.settings.update'), [
            'site_name' => 'SecuroFi.Tech',
            'dev_name' => 'Ziaul Islam',
            'dev_title' => 'Lead Full-Stack Architect',
            'dev_bio' => 'Architecture statement',
            'dev_photo_file' => $file,
        ]);

        $response->assertSessionHas('success');

        $devPhotoUrl = Setting::get('dev_photo');
        $this->assertNotNull($devPhotoUrl);
        $this->assertStringContainsString('uploads/developer', $devPhotoUrl);

        // Verify dev-info displays the uploaded photo
        $pageResponse = $this->get('/dev-info');
        $pageResponse->assertOk();
        $pageResponse->assertSee($devPhotoUrl);

        // Now test removal of photo
        $removeResponse = $this->actingAs($this->superAdmin)->post(route('admin.settings.update'), [
            'site_name' => 'SecuroFi.Tech',
            'dev_name' => 'Ziaul Islam',
            'remove_dev_photo' => '1',
        ]);

        $removeResponse->assertSessionHas('success');
        $this->assertNull(Setting::get('dev_photo'));
    }
}

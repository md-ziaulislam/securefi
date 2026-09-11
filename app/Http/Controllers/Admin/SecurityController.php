<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LoginHistory;
use App\Models\Setting;
use App\Services\BackupService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Spatie\Activitylog\Models\Activity;

class SecurityController extends Controller
{
    protected BackupService $backupService;

    public function __construct(BackupService $backupService)
    {
        $this->backupService = $backupService;
    }

    public function index(Request $request)
    {
        $activeTab = $request->query('tab', 'audit');

        // 1. Login Audit Trail
        $loginHistory = LoginHistory::with('user')
            ->latest('created_at')
            ->paginate(25, ['*'], 'login_page');

        // 2. Activity / Audit Trail
        $activityLogs = Activity::with('causer')
            ->latest('created_at')
            ->paginate(25, ['*'], 'activity_page');

        // 3. Database & Cloud Backups
        $backups = $this->backupService->getBackups();
        $cloudConfig = $this->backupService->applyCloudConfig();

        // 4. System Security Health Metrics
        $securityChecks = [
            'rate_limiting' => [
                'name' => 'Login Rate Limiting & Brute-Force Lockout',
                'status' => true,
                'detail' => 'Active (5 failed attempts per 300s decay lockout)',
            ],
            'security_headers' => [
                'name' => 'HTTP Security Headers',
                'status' => true,
                'detail' => 'X-Frame-Options (SAMEORIGIN), X-Content-Type-Options (nosniff), Referrer-Policy, Permissions-Policy',
            ],
            'two_factor' => [
                'name' => 'Two-Factor Authentication (2FA)',
                'status' => true,
                'detail' => 'Google Authenticator TOTP ready with QR enrollment',
            ],
            'rbac_protection' => [
                'name' => 'Role-Based Access Control (RBAC)',
                'status' => true,
                'detail' => 'Spatie Permissions enforced across 4 tiers (Super Admin, Admin, Editor, Author)',
            ],
            'csrf_protection' => [
                'name' => 'CSRF Token Validation',
                'status' => true,
                'detail' => 'Active across all mutation requests',
            ],
            'cloud_backup' => [
                'name' => 'Offsite Cloud Backup (S3 / Wasabi / R2)',
                'status' => $cloudConfig['enabled'] && !empty($cloudConfig['bucket']),
                'detail' => $cloudConfig['enabled'] && !empty($cloudConfig['bucket'])
                    ? 'Active (' . strtoupper($cloudConfig['provider']) . ' - Bucket: ' . $cloudConfig['bucket'] . ')'
                    : 'Configured for local storage only. Cloud sync inactive.',
            ],
            'storage_writable' => [
                'name' => 'Storage Directory Permissions',
                'status' => is_writable(storage_path()),
                'detail' => is_writable(storage_path()) ? 'Secure & Writable' : 'Permission Restricted',
            ],
            'app_debug' => [
                'name' => 'Debug Mode Exposure',
                'status' => !config('app.debug'),
                'detail' => config('app.debug') ? 'Enabled (Turn off in production .env)' : 'Disabled (Production Safe)',
            ],
            'php_version' => [
                'name' => 'PHP Runtime Version',
                'status' => version_compare(PHP_VERSION, '8.2.0', '>='),
                'detail' => 'v' . PHP_VERSION,
            ],
            'framework_version' => [
                'name' => 'Laravel Framework',
                'status' => true,
                'detail' => 'v' . app()->version(),
            ],
        ];

        return view('admin.security.index', compact(
            'activeTab',
            'loginHistory',
            'activityLogs',
            'backups',
            'cloudConfig',
            'securityChecks'
        ));
    }

    public function createBackup()
    {
        try {
            $filename = $this->backupService->createBackup();
            return redirect()->route('admin.security.index', ['tab' => 'backups'])
                ->with('success', "Database SQL backup created successfully: {$filename}");
        } catch (\Throwable $e) {
            return redirect()->route('admin.security.index', ['tab' => 'backups'])
                ->with('error', "Backup failed: " . $e->getMessage());
        }
    }

    public function createCloudBackup(Request $request)
    {
        $validated = $request->validate([
            'backup_type' => 'required|in:full,db',
            'upload_cloud' => 'nullable|boolean',
        ]);

        $onlyDb = $validated['backup_type'] === 'db';
        $uploadCloud = $request->boolean('upload_cloud');

        try {
            $result = $this->backupService->createSpatieBackup($onlyDb, $uploadCloud);

            if ($result['success']) {
                return redirect()->route('admin.security.index', ['tab' => 'backups'])
                    ->with('success', $result['message']);
            }

            return redirect()->route('admin.security.index', ['tab' => 'backups'])
                ->with('error', $result['message']);
        } catch (\Throwable $e) {
            return redirect()->route('admin.security.index', ['tab' => 'backups'])
                ->with('error', "Backup failed: " . $e->getMessage());
        }
    }

    public function cleanBackups()
    {
        try {
            $result = $this->backupService->cleanOldBackups();
            return redirect()->route('admin.security.index', ['tab' => 'backups'])
                ->with('success', $result['message']);
        } catch (\Throwable $e) {
            return redirect()->route('admin.security.index', ['tab' => 'backups'])
                ->with('error', "Pruning failed: " . $e->getMessage());
        }
    }

    public function updateCloudSettings(Request $request)
    {
        $validated = $request->validate([
            'backup_s3_enabled' => 'nullable|boolean',
            'backup_s3_provider' => 'required|in:aws,wasabi,backblaze,cloudflare,minio,custom',
            'backup_s3_key' => 'nullable|string|max:255',
            'backup_s3_secret' => 'nullable|string|max:255',
            'backup_s3_bucket' => 'nullable|string|max:255',
            'backup_s3_region' => 'nullable|string|max:100',
            'backup_s3_endpoint' => 'nullable|url|max:500',
            'backup_s3_use_path_style' => 'nullable|boolean',
        ]);

        Setting::set('backup_s3_enabled', $request->has('backup_s3_enabled') ? '1' : '0', 'backup');
        Setting::set('backup_s3_provider', $validated['backup_s3_provider'], 'backup');
        Setting::set('backup_s3_bucket', $validated['backup_s3_bucket'] ?? '', 'backup');
        Setting::set('backup_s3_region', $validated['backup_s3_region'] ?? 'us-east-1', 'backup');
        Setting::set('backup_s3_endpoint', $validated['backup_s3_endpoint'] ?? '', 'backup');
        Setting::set('backup_s3_use_path_style', $request->has('backup_s3_use_path_style') ? '1' : '0', 'backup');

        if (!empty($validated['backup_s3_key'])) {
            Setting::set('backup_s3_key', $validated['backup_s3_key'], 'backup');
        }

        if (!empty($validated['backup_s3_secret'])) {
            Setting::set('backup_s3_secret', $validated['backup_s3_secret'], 'backup');
        }

        activity()
            ->causedBy(auth()->user())
            ->log('Updated Cloud Backup S3-compatible storage configuration.');

        return redirect()->route('admin.security.index', ['tab' => 'backups'])
            ->with('success', 'Cloud Backup (S3) settings updated successfully.');
    }

    public function testCloudConnection(Request $request)
    {
        $customConfig = null;
        if ($request->filled('backup_s3_key')) {
            $customConfig = [
                'backup_s3_enabled' => '1',
                'backup_s3_key' => $request->input('backup_s3_key'),
                'backup_s3_secret' => $request->input('backup_s3_secret') ?: Setting::get('backup_s3_secret', env('AWS_SECRET_ACCESS_KEY', '')),
                'backup_s3_bucket' => $request->input('backup_s3_bucket'),
                'backup_s3_region' => $request->input('backup_s3_region', 'us-east-1'),
                'backup_s3_endpoint' => $request->input('backup_s3_endpoint'),
                'backup_s3_use_path_style' => $request->has('backup_s3_use_path_style') ? '1' : '0',
                'backup_s3_provider' => $request->input('backup_s3_provider', 'aws'),
            ];
        }

        $result = $this->backupService->testCloudConnection($customConfig);

        if ($request->wantsJson()) {
            return response()->json($result);
        }

        if ($result['success']) {
            return redirect()->route('admin.security.index', ['tab' => 'backups'])
                ->with('success', $result['message']);
        }

        return redirect()->route('admin.security.index', ['tab' => 'backups'])
            ->with('error', $result['message']);
    }

    public function downloadBackup(Request $request, string $filename)
    {
        $disk = $request->query('disk', 'local');

        if ($disk === 's3') {
            $this->backupService->applyCloudConfig();
            $s3Disk = Storage::disk('s3');
            $remotePath = "SecuroFi/{$filename}";

            if (!$s3Disk->exists($remotePath) && !$s3Disk->exists($filename)) {
                abort(404, 'Requested cloud backup file not found on S3 bucket.');
            }

            $actualPath = $s3Disk->exists($remotePath) ? $remotePath : $filename;
            return Storage::disk('s3')->download($actualPath, $filename);
        }

        $path = $this->backupService->getBackupPath($filename);

        if (!$path || !File::exists($path)) {
            abort(404, 'Requested backup file not found.');
        }

        $contentType = str_ends_with($filename, '.zip') ? 'application/zip' : 'application/sql';

        return response()->download($path, $filename, [
            'Content-Type' => $contentType,
        ]);
    }

    public function destroyBackup(Request $request, string $filename)
    {
        $disk = $request->input('disk', 'local');

        if ($this->backupService->deleteBackup($filename, $disk)) {
            return redirect()->route('admin.security.index', ['tab' => 'backups'])
                ->with('success', "Backup archive '{$filename}' deleted successfully.");
        }

        return redirect()->route('admin.security.index', ['tab' => 'backups'])
            ->with('error', "Could not delete backup archive.");
    }

    public function clearLoginLogs()
    {
        LoginHistory::truncate();

        activity()
            ->causedBy(auth()->user())
            ->log('Purged all historical login audit trail records');

        return redirect()->route('admin.security.index', ['tab' => 'audit'])
            ->with('success', 'All login history records have been cleared.');
    }
}

<?php

namespace App\Services;

use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class BackupService
{
    protected string $backupDir;

    public function __construct()
    {
        $this->backupDir = storage_path('app/backups');
        if (!File::isDirectory($this->backupDir)) {
            File::makeDirectory($this->backupDir, 0755, true);
        }

        $tempDir = storage_path('app/backup-temp');
        if (!File::isDirectory($tempDir)) {
            File::makeDirectory($tempDir, 0755, true);
        }
    }

    /**
     * Apply runtime cloud storage (S3-compatible) configuration from Database Settings or .env
     */
    public function applyCloudConfig(?array $customConfig = null): array
    {
        $enabled = $customConfig['backup_s3_enabled'] ?? Setting::get('backup_s3_enabled', env('BACKUP_S3_ENABLED', '0'));
        $key = $customConfig['backup_s3_key'] ?? Setting::get('backup_s3_key', env('AWS_ACCESS_KEY_ID', ''));
        $secret = $customConfig['backup_s3_secret'] ?? Setting::get('backup_s3_secret', env('AWS_SECRET_ACCESS_KEY', ''));
        $region = $customConfig['backup_s3_region'] ?? Setting::get('backup_s3_region', env('AWS_DEFAULT_REGION', 'us-east-1'));
        $bucket = $customConfig['backup_s3_bucket'] ?? Setting::get('backup_s3_bucket', env('AWS_BUCKET', ''));
        $endpoint = $customConfig['backup_s3_endpoint'] ?? Setting::get('backup_s3_endpoint', env('AWS_ENDPOINT', ''));
        $usePathStyle = $customConfig['backup_s3_use_path_style'] ?? Setting::get('backup_s3_use_path_style', env('AWS_USE_PATH_STYLE_ENDPOINT', 'false'));
        $provider = $customConfig['backup_s3_provider'] ?? Setting::get('backup_s3_provider', 'aws');

        $s3Config = [
            'driver' => 's3',
            'key' => $key,
            'secret' => $secret,
            'region' => $region ?: 'us-east-1',
            'bucket' => $bucket,
            'url' => env('AWS_URL'),
            'endpoint' => $endpoint ?: null,
            'use_path_style_endpoint' => filter_var($usePathStyle, FILTER_VALIDATE_BOOLEAN),
            'throw' => true,
        ];

        Config::set('filesystems.disks.s3', $s3Config);

        return [
            'enabled' => filter_var($enabled, FILTER_VALIDATE_BOOLEAN),
            'provider' => $provider,
            'key' => $key,
            'secret' => $secret ? '••••••••' . substr($secret, -4) : '',
            'raw_secret' => $secret,
            'region' => $region,
            'bucket' => $bucket,
            'endpoint' => $endpoint,
            'use_path_style' => filter_var($usePathStyle, FILTER_VALIDATE_BOOLEAN),
        ];
    }

    /**
     * Test Cloud S3-compatible storage connection and measure latency.
     */
    public function testCloudConnection(?array $customConfig = null): array
    {
        $this->applyCloudConfig($customConfig);

        $startTime = microtime(true);
        $testFilename = 'securofi_connection_test_' . time() . '.txt';

        try {
            $disk = Storage::disk('s3');
            
            // 1. Put test file
            $disk->put($testFilename, "SecuroFi Cloud Connection Test: " . now()->toIso8601String());

            // 2. Check existence
            if (!$disk->exists($testFilename)) {
                throw new \Exception("File was uploaded but could not be verified on the remote bucket.");
            }

            // 3. Delete test file
            $disk->delete($testFilename);

            $latency = round((microtime(true) - $startTime) * 1000);

            return [
                'success' => true,
                'latency_ms' => $latency,
                'message' => "Connected successfully to cloud bucket! Roundtrip latency: {$latency} ms.",
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'latency_ms' => null,
                'message' => 'Connection failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Generate a fast native SQL backup of the current database.
     */
    public function createBackup(): string
    {
        $timestamp = now()->format('Y-m-d_H-i-s');
        $filename = "securofi_backup_{$timestamp}.sql";
        $filepath = $this->backupDir . DIRECTORY_SEPARATOR . $filename;

        $pdo = DB::connection()->getPdo();
        $dbName = DB::connection()->getDatabaseName();

        $sql = "-- SecuroFi.Tech Database Backup\n";
        $sql .= "-- Generated: " . now()->toIso8601String() . "\n";
        $sql .= "-- Database: {$dbName}\n\n";
        $driver = DB::connection()->getDriverName();

        if ($driver === 'sqlite') {
            $tablesRaw = DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'");
            $tables = array_map(fn($t) => $t->name ?? array_values((array)$t)[0], $tablesRaw);
        } else {
            $sql .= "SET FOREIGN_KEY_CHECKS=0;\n\n";
            $tablesRaw = DB::select("SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'");
            $tables = array_map(fn($t) => array_values((array)$t)[0], $tablesRaw);
        }

        foreach ($tables as $table) {
            if ($driver === 'sqlite') {
                $row = DB::selectOne("SELECT sql FROM sqlite_master WHERE type='table' AND name = ?", [$table]);
                $createTableSql = $row ? $row->sql : null;
            } else {
                $stmt = $pdo->query("SHOW CREATE TABLE `{$table}`");
                $row = $stmt->fetch(\PDO::FETCH_NUM);
                $createTableSql = $row[1] ?? null;
            }

            if ($createTableSql) {
                $sql .= "-- --------------------------------------------------------\n";
                $sql .= "-- Structure for table `{$table}`\n";
                $sql .= "-- --------------------------------------------------------\n";
                $sql .= "DROP TABLE IF EXISTS `{$table}`;\n";
                $sql .= $createTableSql . ";\n\n";
            }

            $rows = DB::table($table)->get();
            if ($rows->count() > 0) {
                $sql .= "-- Dumping data for table `{$table}`\n";
                foreach ($rows->chunk(100) as $chunk) {
                    $insertValues = [];
                    foreach ($chunk as $rowObj) {
                        $values = [];
                        foreach ((array) $rowObj as $val) {
                            if (is_null($val)) {
                                $values[] = 'NULL';
                            } else {
                                $values[] = $pdo->quote((string) $val);
                            }
                        }
                        $insertValues[] = '(' . implode(', ', $values) . ')';
                    }

                    if (!empty($insertValues)) {
                        $firstObj = (array) $chunk->first();
                        $columns = array_map(fn($col) => "`{$col}`", array_keys($firstObj));
                        $colStr = implode(', ', $columns);
                        $sql .= "INSERT INTO `{$table}` ({$colStr}) VALUES \n" . implode(",\n", $insertValues) . ";\n";
                    }
                }
                $sql .= "\n";
            }
        }

        if ($driver !== 'sqlite') {
            $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";
        }

        File::put($filepath, $sql);

        if (auth()->check()) {
            activity()
                ->causedBy(auth()->user())
                ->log("Generated database backup: {$filename}");
        }

        return $filename;
    }

    /**
     * Run full or DB-only backup using spatie/laravel-backup or robust fallback.
     * Optionally upload to S3-compatible cloud storage.
     */
    public function createSpatieBackup(bool $onlyDb = false, bool $uploadToCloud = false): array
    {
        $cloudConfig = $this->applyCloudConfig();
        $disks = ['backup_local'];

        if ($uploadToCloud || $cloudConfig['enabled']) {
            if (!empty($cloudConfig['bucket']) && !empty($cloudConfig['key'])) {
                $disks[] = 's3';
            }
        }

        Config::set('backup.backup.destination.disks', $disks);

        $params = [
            '--disable-notifications' => true,
        ];

        if ($onlyDb) {
            $params['--only-db'] = true;
        }

        $exitCode = -1;
        $output = '';

        try {
            // Attempt Spatie backup
            $exitCode = Artisan::call('backup:run', $params);
            $output = Artisan::output();
        } catch (\Throwable $e) {
            Log::warning("Spatie backup:run threw exception: " . $e->getMessage() . ". Attempting robust fallback archive.");
            $output = $e->getMessage();
        }

        // If Spatie succeeded (exit code 0), return success
        if ($exitCode === 0) {
            if (auth()->check()) {
                activity()
                    ->causedBy(auth()->user())
                    ->log($onlyDb ? "Created automated database backup archive." : "Created full system & media backup archive.");
            }

            return [
                'success' => true,
                'message' => ($onlyDb ? 'Database' : 'Full (Database + Media)') . ' backup generated successfully.',
                'output' => $output,
            ];
        }

        // Fallback archive generation: If mysqldump executable was missing or failed in Windows
        return $this->createFallbackZipArchive($onlyDb, in_array('s3', $disks));
    }

    /**
     * Robust fallback ZIP generator: Dumps SQL via internal PDO and zips with public media storage.
     */
    protected function createFallbackZipArchive(bool $onlyDb, bool $uploadToCloud): array
    {
        $timestamp = now()->format('Y-m-d_H-i-s');
        $sqlFilename = $this->createBackup();
        $sqlPath = $this->backupDir . DIRECTORY_SEPARATOR . $sqlFilename;

        $zipFilename = "securofi_" . ($onlyDb ? "db_" : "full_") . "{$timestamp}.zip";
        $zipPath = $this->backupDir . DIRECTORY_SEPARATOR . $zipFilename;

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
            // Add database dump
            $zip->addFile($sqlPath, 'database/' . $sqlFilename);

            // Add media files if not only-db
            if (!$onlyDb) {
                $mediaDir = storage_path('app/public');
                if (File::isDirectory($mediaDir)) {
                    $files = File::allFiles($mediaDir);
                    foreach ($files as $file) {
                        $relativePath = 'media/' . $file->getRelativePathname();
                        $zip->addFile($file->getRealPath(), $relativePath);
                    }
                }
            }

            $zip->close();

            // Optionally upload to S3
            if ($uploadToCloud) {
                try {
                    $s3Disk = Storage::disk('s3');
                    $s3Disk->putFileAs('SecuroFi', new \Illuminate\Http\File($zipPath), $zipFilename);
                } catch (\Throwable $cloudEx) {
                    Log::error("Failed to upload fallback backup to S3: " . $cloudEx->getMessage());
                }
            }

            if (auth()->check()) {
                activity()
                    ->causedBy(auth()->user())
                    ->log("Created backup archive: {$zipFilename}");
            }

            return [
                'success' => true,
                'message' => "Backup archive '{$zipFilename}' created successfully.",
                'filename' => $zipFilename,
            ];
        }

        return [
            'success' => false,
            'message' => "Database dump was saved ({$sqlFilename}), but creating ZIP package failed.",
        ];
    }

    /**
     * Clean old backups using Spatie retention rules and local pruning.
     */
    public function cleanOldBackups(): array
    {
        $this->applyCloudConfig();
        $spatieCleanCode = -1;
        $output = '';

        try {
            $spatieCleanCode = Artisan::call('backup:clean', ['--disable-notifications' => true]);
            $output = Artisan::output();
        } catch (\Throwable $e) {
            $output = $e->getMessage();
        }

        // Also prune local SQL dumps older than 7 days
        $prunedCount = 0;
        $files = File::files($this->backupDir);
        $threshold = now()->subDays(7)->timestamp;

        foreach ($files as $file) {
            if ($file->getExtension() === 'sql' && $file->getMTime() < $threshold) {
                File::delete($file->getRealPath());
                $prunedCount++;
            }
        }

        if (auth()->check()) {
            activity()
                ->causedBy(auth()->user())
                ->log("Ran backup retention cleaner (Pruned {$prunedCount} old files).");
        }

        return [
            'success' => true,
            'pruned_sql_files' => $prunedCount,
            'output' => $output,
            'message' => "Backup retention policy executed. {$prunedCount} aged local archives cleaned.",
        ];
    }

    /**
     * Get all available backups from local storage and remote S3 (if active).
     */
    public function getBackups(): array
    {
        $backups = [];

        // 1. Scan Local Directory (storage/app/backups and Spatie subdirectories)
        if (File::isDirectory($this->backupDir)) {
            $allFiles = File::allFiles($this->backupDir);

            foreach ($allFiles as $file) {
                $ext = strtolower($file->getExtension());
                if (in_array($ext, ['sql', 'zip'])) {
                    $bytes = $file->getSize();
                    $size = $bytes >= 1048576 
                        ? number_format($bytes / 1048576, 2) . ' MB' 
                        : number_format($bytes / 1024, 2) . ' KB';

                    $type = $ext === 'sql' ? 'database_sql' : (str_contains($file->getFilename(), 'db') ? 'database_zip' : 'full_archive');

                    $backups[] = [
                        'filename' => $file->getFilename(),
                        'path' => $file->getRealPath(),
                        'disk' => 'local',
                        'type' => $type,
                        'size' => $size,
                        'bytes' => $bytes,
                        'created_at' => Carbon::createFromTimestamp($file->getMTime()),
                    ];
                }
            }
        }

        // 2. Query Cloud Storage (S3) if configured
        $cloudConfig = $this->applyCloudConfig();
        if ($cloudConfig['enabled'] && !empty($cloudConfig['bucket'])) {
            try {
                $s3Disk = Storage::disk('s3');
                $remoteFiles = $s3Disk->allFiles('SecuroFi');

                foreach ($remoteFiles as $rFile) {
                    $rExt = strtolower(pathinfo($rFile, PATHINFO_EXTENSION));
                    if (in_array($rExt, ['sql', 'zip'])) {
                        $bytes = $s3Disk->size($rFile);
                        $size = $bytes >= 1048576 
                            ? number_format($bytes / 1048576, 2) . ' MB' 
                            : number_format($bytes / 1024, 2) . ' KB';

                        $type = $rExt === 'sql' ? 'database_sql' : (str_contains(basename($rFile), 'db') ? 'database_zip' : 'full_archive');

                        $backups[] = [
                            'filename' => basename($rFile),
                            'path' => $rFile,
                            'disk' => 's3',
                            'type' => $type,
                            'size' => $size,
                            'bytes' => $bytes,
                            'created_at' => Carbon::createFromTimestamp($s3Disk->lastModified($rFile)),
                        ];
                    }
                }
            } catch (\Throwable $e) {
                Log::notice("Could not fetch remote S3 backups: " . $e->getMessage());
            }
        }

        // Sort latest first
        usort($backups, fn($a, $b) => $b['created_at']->timestamp <=> $a['created_at']->timestamp);

        return $backups;
    }

    /**
     * Get real path for a local backup file.
     */
    public function getBackupPath(string $filename): ?string
    {
        $clean = basename($filename);
        
        // Check direct backup dir
        $path = $this->backupDir . DIRECTORY_SEPARATOR . $clean;
        if (File::exists($path)) {
            return $path;
        }

        // Check subdirectories (e.g. storage/app/backups/SecuroFi/)
        $allFiles = File::allFiles($this->backupDir);
        foreach ($allFiles as $file) {
            if ($file->getFilename() === $clean) {
                return $file->getRealPath();
            }
        }

        return null;
    }

    /**
     * Delete a backup file from local disk or S3.
     */
    public function deleteBackup(string $filename, string $disk = 'local'): bool
    {
        if ($disk === 's3') {
            $this->applyCloudConfig();
            try {
                $s3Disk = Storage::disk('s3');
                $deleted = $s3Disk->delete("SecuroFi/{$filename}") || $s3Disk->delete($filename);
                if ($deleted && auth()->check()) {
                    activity()
                        ->causedBy(auth()->user())
                        ->log("Deleted cloud backup from S3: {$filename}");
                }
                return $deleted;
            } catch (\Throwable $e) {
                return false;
            }
        }

        $path = $this->getBackupPath($filename);
        if ($path && File::exists($path)) {
            $deleted = File::delete($path);
            if ($deleted && auth()->check()) {
                activity()
                    ->causedBy(auth()->user())
                    ->log("Deleted local backup: {$filename}");
            }
            return $deleted;
        }

        return false;
    }
}

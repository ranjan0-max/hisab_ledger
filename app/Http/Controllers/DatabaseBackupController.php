<?php

namespace App\Http\Controllers;

use App\Models\DatabaseBackupLog;
use App\Models\GoogleDriveBackupSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class DatabaseBackupController extends Controller
{
    public function index()
    {
        $logs = DatabaseBackupLog::with('triggeredBy')->latest('started_at')->paginate(10);
        $driveSetting = GoogleDriveBackupSetting::first();

        return view('database-backups.index', compact('logs', 'driveSetting'));
    }

    public function connectGoogleDrive()
    {
        $clientId = env('GOOGLE_DRIVE_CLIENT_ID');
        $redirectUri = env('GOOGLE_DRIVE_REDIRECT_URI', url('/api/database-backups/google/callback'));

        if (!$clientId) {
            return redirect()->back()->with('error', 'Google Drive Client ID is not configured in .env file.');
        }

        $authUrl = "https://accounts.google.com/o/oauth2/v2/auth?" . http_build_query([
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'response_type' => 'code',
            'scope' => 'https://www.googleapis.com/auth/drive.file',
            'access_type' => 'offline',
            'prompt' => 'consent',
        ]);

        return redirect()->away($authUrl);
    }

    public function disconnectGoogleDrive()
    {
        $setting = GoogleDriveBackupSetting::first();
        if ($setting) {
            $setting->update([
                'is_connected' => false,
                'encrypted_refresh_token' => null,
                'drive_email' => null,
            ]);
        }

        return redirect()->back()->with('success', 'Google Drive disconnected successfully.');
    }

    public function run()
    {
        $driveSetting = GoogleDriveBackupSetting::first();
        if (!$driveSetting || !$driveSetting->is_connected) {
            return redirect()->back()->with('error', 'Google Drive is not connected. Please connect Google Drive first to perform backup.');
        }

        $fileName = 'hisab_ledger_backup_' . date('Y_m_d_His') . '.sql';
        $backupDir = storage_path('app/backups');
        
        if (!file_exists($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        $filePath = $backupDir . '/' . $fileName;

        $log = DatabaseBackupLog::create([
            'status'       => 'RUNNING',
            'file_name'    => $fileName,
            'started_at'   => now(),
            'triggered_by' => auth()->id(),
            'trigger_type' => 'MANUAL',
        ]);

        try {
            $dbHost = config('database.connections.mysql.host', '127.0.0.1');
            $dbPort = config('database.connections.mysql.port', '3306');
            $dbUser = config('database.connections.mysql.username', 'root');
            $dbPass = config('database.connections.mysql.password', 'root');
            $dbName = config('database.connections.mysql.database', 'shop_ledger');

            $cmd = sprintf(
                'MYSQL_PWD=%s mysqldump --host=%s --port=%s --user=%s --single-transaction --quick --routines --triggers %s > %s 2>&1',
                escapeshellarg($dbPass),
                escapeshellarg($dbHost),
                escapeshellarg($dbPort),
                escapeshellarg($dbUser),
                escapeshellarg($dbName),
                escapeshellarg($filePath)
            );

            exec($cmd, $output, $returnVar);

            if ($returnVar === 0 && file_exists($filePath)) {
                $fileSize = filesize($filePath);
                $log->update([
                    'status'       => 'SUCCESS',
                    'file_size'    => $fileSize,
                    'completed_at' => now(),
                ]);

                // Cleanup temporary backup file from storage/app/backups
                if (file_exists($filePath)) {
                    unlink($filePath);
                }

                return redirect()->back()->with('success', 'Database backup completed and processed successfully.');
            } else {
                throw new \Exception('mysqldump failed with status code ' . $returnVar);
            }
        } catch (\Exception $e) {
            if (file_exists($filePath)) {
                @unlink($filePath);
            }

            $log->update([
                'status'        => 'FAILED',
                'error_message' => $e->getMessage(),
                'completed_at'  => now(),
            ]);

            return redirect()->back()->with('error', 'Backup failed: ' . $e->getMessage());
        }
    }
}

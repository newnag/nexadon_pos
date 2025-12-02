<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class BackupDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:backup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backup the MySQL database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting database backup...');

        $filename = "backup-" . Carbon::now()->format('Y-m-d-H-i-s') . ".sql";
        $backupPath = storage_path("app/backups/{$filename}");

        // Ensure backup directory exists
        if (!File::exists(dirname($backupPath))) {
            File::makeDirectory(dirname($backupPath), 0755, true);
        }

        $username = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');
        $host = config('database.connections.mysql.host');
        $database = config('database.connections.mysql.database');
        $port = config('database.connections.mysql.port');

        // Try to detect mysqldump path and working directory
        $mysqldumpPath = 'mysqldump';
        $workingDir = null;

        $commonPaths = [
            'C:/Program Files/MySQL/MySQL Server*/bin/mysqldump.exe',
            'C:/xampp/mysql/bin/mysqldump.exe',
            'C:/laragon/bin/mysql/*/bin/mysqldump.exe',
        ];

        foreach ($commonPaths as $path) {
            $found = glob($path);
            if (!empty($found)) {
                $mysqldumpPath = '"' . $found[0] . '"';
                $workingDir = dirname($found[0]);
                break;
            }
        }

        // Construct the mysqldump command
        // Note: Removed --column-statistics=0 as it's not supported by some mysqldump versions (e.g. MariaDB)
        $dumpCommand = sprintf(
            '%s --user=%s --password=%s --host=%s --port=%s %s > %s',
            $mysqldumpPath,
            escapeshellarg($username),
            escapeshellarg($password),
            escapeshellarg($host),
            escapeshellarg($port),
            escapeshellarg($database),
            escapeshellarg($backupPath)
        );

        // If we found the working directory, change to it first to ensure DLLs are loaded correctly
        if ($workingDir) {
            $command = sprintf('cd /d "%s" && %s', $workingDir, $dumpCommand);
        } else {
            $command = $dumpCommand;
        }

        // Mask password in output
        $maskedCommand = str_replace($password, '****', $command);
        // $this->info("Running command: {$maskedCommand}");

        try {
            $returnVar = null;
            $output = null;
            
            exec($command, $output, $returnVar);

            if ($returnVar === 0 && File::exists($backupPath) && File::size($backupPath) > 0) {
                $this->info("Database backed up successfully to: {$backupPath}");
                
                // Optional: Keep only last 5 backups to save space
                $this->cleanOldBackups();
                
                return 0;
            } else {
                $this->error("Backup failed with exit code: {$returnVar}");
                if (File::exists($backupPath)) {
                    File::delete($backupPath); // Delete empty or partial file
                }
                return 1;
            }
        } catch (\Exception $e) {
            $this->error('Backup failed: ' . $e->getMessage());
            return 1;
        }
    }

    private function cleanOldBackups()
    {
        $backupDir = storage_path('app/backups');
        $files = File::glob("{$backupDir}/*.sql");
        
        if (count($files) > 5) {
            // Sort by modification time (oldest first)
            usort($files, function($a, $b) {
                return filemtime($a) - filemtime($b);
            });

            // Delete oldest files
            $filesToDelete = array_slice($files, 0, count($files) - 5);
            
            foreach ($filesToDelete as $file) {
                File::delete($file);
                $this->info("Deleted old backup: " . basename($file));
            }
        }
    }
}

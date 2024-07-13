<?php

namespace Helloarman\Dumptable\Commands;

use Illuminate\Console\Command;

class BackupTable extends Command
{
    protected $signature = 'table:backup {table}';
    protected $description = 'Backup a specific table into a SQL file';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        try {
            $table = $this->argument('table');
            $database = env('DB_DATABASE');
            $username = env('DB_USERNAME');
            $password = env('DB_PASSWORD');
            $host = env('DB_HOST');
            $port = env('DB_PORT', 3306);

            $backupPath = storage_path("backups/{$table}.sql");

            if (!file_exists(dirname($backupPath))) {
                mkdir(dirname($backupPath), 0777, true);
            }

            $command = "mysqldump --host={$host} --port={$port} --user={$username} --password={$password} {$database} {$table} > {$backupPath}";

            $result = null;
            $output = null;

            exec($command, $output, $result);

            if ($result === 0) {
                $this->info("Backup of table '{$table}' completed successfully.");
            } else {
                $this->error("Failed to backup table '{$table}'.");
            }
        } catch (\Throwable $th) {
            $this->error("2 Failed to update $table: " . $th->getMessage());
        }
    }
}

<?php

namespace Helloarman\Dumptable\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;

class RestoreTable extends Command
{
    protected $signature = 'table:restore {table} {--d|delete : Delete Backup File}';
    protected $description = 'Restore a specific table from a SQL file';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $table = $this->argument('table');
        $delete = $this->option('delete');

        try {
            if (env('APP_ENV') == 'local' || env('APP_ENV') == 'development' || env('APP_ENV') == 'dev') {

                $file = storage_path("backups/{$table}.sql");
                $columns = Schema::getColumnListing($table);

                if (!File::exists($file)) {
                    $this->error("SQL file '{$file}' does not exist.");
                    return;
                }

                $columns = Schema::getColumnListing($table);
                $sqlContent = File::get($file);

                DB::unprepared($sqlContent);
                $results = DB::select('SELECT * FROM ' . $table);
                $array = json_decode(json_encode($results), true);

                DB::statement('SET FOREIGN_KEY_CHECKS=0;');
                DB::table($table)->truncate();
                Schema::dropIfExists($table);

                $row = DB::table('migrations')->where('migration', 'like', '%create_' . $table . '_table%')->first();

                if ($row) {
                    DB::table('migrations')->where('id', $row->id)->delete();
                }

                DB::statement('SET FOREIGN_KEY_CHECKS=1;');
                Artisan::call('migrate');

                $single_data = [];
                $data = [];
                foreach ($array as $key => $d) {
                    foreach ($columns as $colum) {
                        $single_data[$colum] = $d[$colum] ?? null;
                    }

                    $data[$key] = $single_data;
                }

                DB::table($table)->truncate();
                $response = DB::table($table)->insert($data);

                $backupPath = storage_path("backups/{$table}.sql");

                if (file_exists($backupPath) && $delete) {
                    unlink($backupPath);
                } else {
                    $this->error("Failed to update $table: ile does not exist.");
                }
            }
        } catch (\Throwable $th) {
            $this->error("3 Failed to update $table: " . $th->getMessage());
        }
    }
}

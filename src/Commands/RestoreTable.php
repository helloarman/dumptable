<?php

namespace Helloarman\Dumptable\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;

class RestoreTable extends Command
{
    protected $signature = 'table:restore {table}';
    protected $description = 'Restore a specific table from a SQL file';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $table = $this->argument('table');
        $file = storage_path("backups/{$table}.sql");
        $columns = Schema::getColumnListing($table);

        if (!File::exists($file)) {
            $this->error("SQL file '{$file}' does not exist.");
            return;
        }

        $columns = Schema::getColumnListing($table);

        // Read the SQL file content
        $sqlContent = File::get($file);

        // Execute the SQL statements
        DB::unprepared($sqlContent);

        // Example: Fetch results if the SQL file contains SELECT queries
        $results = DB::select('SELECT * FROM ' . $table);

        // Convert the results to an array
        $array = json_decode(json_encode($results), true);

        // Disable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Truncate the table and drop it 
        DB::table($table)->truncate();
        Schema::dropIfExists($table);

        // Delete the corresponding migration record
        $row = DB::table('migrations')->where('migration', 'like', '%create_'.$table.'_table%')->first();

        if ($row) {
            DB::table('migrations')->where('id', $row->id)->delete();
        }

        // Enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Run migrations
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

        if($response){
            $this->info("$table data restored successfully!");
        }

        $backupPath = storage_path("backups/{$table}.sql");

        if (file_exists($backupPath)) {
            unlink($backupPath);
        } else {
            echo "File does not exist.";
        }
    }
}

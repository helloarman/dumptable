<?php

namespace Helloarman\Dumptable\Commands;

use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;

class EasyDumpTable extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'table:dump {table} {--s|seed : Also run seeder} {--r|restore : Restore existing data}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update your migration file to database without damaging other tables data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $table = $this->argument('table');
        $seeder = $this->option('seed');
        $restore = $this->option('restore');

        try {

            if(env('APP_ENV') == 'local' || env('APP_ENV') == 'development' || env('APP_ENV') == 'dev'){
                if($restore){
                    Artisan::call('table:backup '.$table);
                }

                DB::statement('SET FOREIGN_KEY_CHECKS=0;');

                DB::table($table)->truncate();
                Schema::dropIfExists($table);

                $row = DB::table('migrations')->where('migration', 'like', '%create_'.$table.'_table%')->first();

                if ($row) {
                    DB::table('migrations')->where('id', $row->id)->delete();
                }

                DB::statement('SET FOREIGN_KEY_CHECKS=1;');

                Artisan::call('migrate');

                $this->info("$table has been updated successfully!");

                if($seeder){
                    $class = Str::studly(Str::singular($table));

                    Artisan::call('db:seed --class='.$class.'Seeder');

                    $this->info("$table data seeded successfully!");
                }

                if($restore){
                    Artisan::call('table:restore '.$table);

                    $backupPath = storage_path("backups/{$table}.sql");

                    $this->comment("
If your data was not restored, there may be an issue.
If you encounter such an issue, you have a backup file available in the storage.

Backup File: $backupPath
");
                }
            }else{
                $this->error("Update failed for $table: Please ensure that your APP_ENV is set to 'local', 'development', or 'dev'.");
            }

        } catch (\Exception $th) {
            $this->error("1 Failed to update $table: " . $th->getMessage());
        }
    }
}

<?php

namespace Helloarman\Dumptable\Commands;

use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;

class DumpTable extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:dump-table {table} {--s|seed : Also run seeder} {--r|restore : Restore existing data}';

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

            if($restore){
                Artisan::call('table:backup '.$table);
            }

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

            // Inform the user of success
            $this->info("$table has been updated successfully!");

            if($seeder){
                $class = Str::studly(Str::singular($table));

                Artisan::call('db:seed --class='.$class.'Seeder');

                $this->info("$table data seeded successfully!");
            }

            if($restore){
                Artisan::call('table:restore '.$table);
            }

        } catch (\Exception $e) {
            // Inform the user of the error
            $this->error("Failed to update $table: " . $e->getMessage());
        }
    }
}

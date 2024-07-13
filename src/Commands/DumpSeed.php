<?php

namespace App\Console\Commands;

use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;

class DumpSeed extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'table:dump-seed {table} {--class=}';

    /**
     * The console This command seeds data into the specified table, deleting previous data..
     *
     * @var string
     */
    protected $description = 'This command seeds data into the specified table, deleting previous data.';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            if (env('APP_ENV') == 'local' || env('APP_ENV') == 'development' || env('APP_ENV') == 'dev') {
                DB::statement('SET FOREIGN_KEY_CHECKS=0;');
                $table = $this->argument('table');
                $file = $this->option('class');

                DB::table($table)->truncate();
                if ($file) {
                    Artisan::call('db:seed', ['--class' => $file]);
                } else {
                    $singular = Str::singular(ucfirst($table));
                    Artisan::call('db:seed', ['--class' => "{$singular}Seeder"]);
                }
                DB::statement('SET FOREIGN_KEY_CHECKS=1;');

                $this->info($table . ' table has been cleaned and seeded successfully.');
            } else {
                $this->error("Update failed: Please ensure that your APP_ENV is set to 'local', 'development', or 'dev'.");
            }
        } catch (\Throwable $th) {
            $this->error("Failed to update $table: " . $th->getMessage());
        }
    }
}

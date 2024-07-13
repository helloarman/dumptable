<?php

namespace Helloarman\Dumptable;

use Illuminate\Support\ServiceProvider;
use Helloarman\Dumptable\Commands\DumpSeed;
use Helloarman\Dumptable\Commands\DumpTable;
use Helloarman\Dumptable\Commands\BackupTable;
use Helloarman\Dumptable\Commands\RestoreTable;
use Helloarman\Dumptable\Commands\EasyDumpTable;
use Helloarman\Dumptable\Commands\NextMigrationFile;

class DumpTableServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->commands([
            DumpTable::class,
            BackupTable::class,
            RestoreTable::class,
            DumpSeed::class,
            EasyDumpTable::class,
            NextMigrationFile::class,
        ]);

    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}

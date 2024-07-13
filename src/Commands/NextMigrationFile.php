<?php

namespace App\Console\Commands;

use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;

class NextMigrationFile extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'table:col {type} {column} {table} ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'With this command it will add a migration file after the selected table and easy to add migration file.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $type = $this->argument('type');
        $table = $this->argument('table');
        $column = $this->transformToSnake($this->argument('column'));

        try {
            $row = DB::table('migrations')->where('migration', 'like', '%create_' . $table . '_table%')->first();

            $last_added_row = DB::table('migrations')->where('migration', 'like', '%_to_' . $table . '_table%')->orderBy('id', 'desc')->first();
            $matches_index[1] = '';
            if(isset($last_added_row)){
                preg_match('/(\d{6})_('.$type.')_([\w_]+_to_\w+_table)/', $last_added_row->migration, $matches_index);
            }

            preg_match('/(\d+)_(create_\w+_table)/', $row->migration, $matches);
            if (isset($matches[1]) && isset($matches[2])) {
                $numericPart = $matches[1];
                $tableName = $matches[2];

                $nextNumericPart = $matches_index[1] == '' ? (int)$numericPart + 1 : (int)$matches_index[1] + 1;

                $paddedNumericPart = str_pad($nextNumericPart, strlen($numericPart), '0', STR_PAD_LEFT);

                $filename = preg_replace('/(\d+)_(create_\w+_table)/', $paddedNumericPart . "_$tableName", $row->migration);
            }
            dd($filename);

            $new_filename = str_replace('create', $type . '_' . $column . '_to', $filename . '.php');

            $migrationPath = database_path('migrations/' . $new_filename);
            $template = $this->getTemp();
            $content = str_replace(
                ['{{table}}'],
                [$table],
                $template
            );

            File::put($migrationPath, $content);

            $this->info('Migration created successfully.');
        } catch (\Throwable $th) {
            $this->error("Failed to update $table: " . $th->getMessage());
        }
    }

    protected function getTemp()
    {
        return <<<TEMP
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('{{table}}', function (Blueprint \$table) {
            //
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('{{table}}', function (Blueprint \$table) {
            //
        });
    }
};
TEMP;
    }

    protected function transformToSnake($string)
    {
        $lowercaseString = strtolower($string);

        $replacedString = str_replace('-', '_', $lowercaseString);

        $snakeCaseString = preg_replace('/([a-z])([A-Z])/', '$1_$2', $replacedString);

        return $snakeCaseString;
    }
}

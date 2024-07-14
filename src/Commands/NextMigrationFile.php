<?php

namespace Helloarman\Dumptable\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

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
                $matches_index = $this->extractParts($last_added_row->migration);
            }

            preg_match('/(\d+)_(create_\w+_table)/', $row->migration, $matches);
            if (isset($matches[1]) && isset($matches[2])) {
                $numericPart = $matches[1];
                $tableName = $matches[2];

                $nextNumericPart = $matches_index[0] == '' ? (int)$numericPart + 1 : (int)$matches_index[0] + 1;

                $paddedNumericPart = str_pad($nextNumericPart, strlen($numericPart), '0', STR_PAD_LEFT);

                $filename = preg_replace('/(\d+)_(create_\w+_table)/', $paddedNumericPart . "_$tableName", $row->migration);
            }

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
    protected function extractParts($string)
    {
         // Step 1: Split the string by underscores
        $parts = explode('_', $string);

        // Step 2: Find the index of the 'to' segment which indicates the start of the table name
        $toIndex = array_search('to', $parts);

        // Step 3: Extract the relevant parts
        $timestamp = $parts[3];
        $operation = implode('_', array_slice($parts, 4, $toIndex - 4)); // Combine all parts between timestamp and 'to'
        $table = str_replace('_table', '', $parts[$toIndex + 1]);

        // Step 4: Return the extracted parts as an array
        return [$timestamp, $operation, $table];
    }
}

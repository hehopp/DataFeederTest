<?php

namespace App\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class ImportXmlData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:import-xml-data {file} {table}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import data from locally stored XML-file into the given database table';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $filePath = $this->argument('file');
        $tableName = $this->argument('table');

        $fileName = basename($filePath);
        $errorLogPath = "storage/logs/errors_import_file_" . $fileName . "_" . now()->format('d_m_Y') . ".log";

        //check if file exists, if not ask user for valid file path
        while (!file_exists($filePath)) {
            error_log("ERROR: File '$fileName' at '$filePath' not found. Trying new path...", 3, $errorLogPath);
            $this->error("ERROR: File '$fileName' at '$filePath' not found");
            $filePath = $this->ask('Please enter a valid file path');
            $this->info("New file path received: " . $filePath);
        }

        $this->info("File '" . basename($filePath) . "' found. Proceeding import.");

        //load file into simplexmlelement if loading fails exit command
        $xmlData = simplexml_load_file($filePath);
        if (!$xmlData) {
            error_log("ERROR: Cannot load file '$fileName'. Exiting command.", 3, $errorLogPath);
            $this->error("Cannot load file '$fileName'. Exiting command.");
            exit();
        }

        //convert simplexmlelement to array
        $array = json_decode(json_encode($xmlData), true);

        //flatten to get all entries of xml converted array
        $flattenedArray = [];
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                //flatten array to single level (nested objects)
                foreach ($value as $subValue) {
                    if (is_array($subValue)) {
                        $tempFaltArray = [];
                        array_walk_recursive($subValue, function ($value, $key) use (&$tempFaltArray) {
                            $tempFaltArray[$key] = $value;
                        });

                        $flattenedArray[] = $tempFaltArray;
                    } else {
                        $flattenedArray[] = [$key => $value];
                    }
                }
            } else {
                $flattenedArray[] = [$key => $value];
            }
        }

        //check if table with given name exist. If not give possibility to create new table or correct input.
        while (!Schema::hasTable($tableName)) {
            error_log("ERROR: Table '" . $tableName . "' not found. Trying new table name", 3, $errorLogPath);
            $this->error("Table '$tableName' not found");
            $newTable = $this->confirm("Would you like to create a new Table named '$tableName'?");

            if ($newTable) {
                //create new table with given name. Use keys of an entry to structure table columns.
                try {
                    Schema::create($tableName, function (Blueprint $table) use ($flattenedArray) {
                        $table->increments('id');
                        $table->timestamps();
                        $table->softDeletes();
                        foreach ($flattenedArray[0] as $key => $value) {
                            //workaround for db invalid varchar limit
                            gettype($value) == 'string' ? $table->string($key, 255)->nullable() : $table->addColumn(gettype($value), $key)->nullable();
                        }
                    });
                } catch (Exception $e) {
                    error_log("Caught exception: " . $e->getMessage() . ". Exiting command", 3, $errorLogPath);
                    $this->error("Caught exception: " . $e->getMessage() . ". Exiting command");
                    exit();
                }

                $this->info("Table '" . $tableName . "' created. Proceeding import.");
            } else {
                $tableName = $this->ask('Please enter a valid Table name');
            }
        }

        $this->info("Table '$tableName' found. Starting data import into table, this might take a while.");

        //insert data from array into given table
        foreach ($flattenedArray as $item) {
            try {
                DB::table($tableName)->insert($item);
            } catch (Exception $e) {
                error_log("Caught exception: " . $e->getMessage() . ". Exiting command", 3, $errorLogPath);
                $this->error("Caught exception: " . $e->getMessage() . ". Exiting command");
                exit();
            }
        }

        $this->info("Data of file '$fileName' was succesfully imported into table {$tableName}");
    }
}

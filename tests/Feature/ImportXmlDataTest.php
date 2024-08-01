<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Schema\Blueprint;
use function Pest\Laravel\artisan;

// Define global variables
$validFilePath = 'valid/file/feed.xml';

// Setup the environment before each test
beforeEach(function () use ($validFilePath) {
    // Configure SQLite in-memory database for testing
    config(['database.default' => 'sqlite']);
    config(['database.connections.sqlite' => [
        'driver' => 'sqlite',
        'database' => ':memory:',
        'prefix' => '',
    ]]);

    // Create a table for testing
    Schema::create('test', function (Blueprint $table) {
        $table->increments('id');
        $table->string('name');
        $table->string('value');
        $table->timestamps();
        $table->softDeletes();
    });

    // Fake the filesystem
    Storage::fake();

    // Create a valid XML file in the fake storage
    Storage::put($validFilePath, '<?xml version="1.0"?><root><item><name>Test</name><value>123</value></item></root>');
});

// Clean up after each test
afterEach(function () {
    Schema::dropAllTables();
});

it('prompts for a valid file path when the file does not exist', function () use ($validFilePath) {
    $invalidFilePath = 'path/to/nonexistent/feed.xml';

    artisan('app:import-xml-data', [
        'file' => $invalidFilePath,
        'table' => 'test'
    ])
    ->expectsQuestion('Please enter a valid file path', 'path/to/another/nonexistent/feed.xml')
    ->expectsQuestion('Please enter a valid file path', Storage::path($validFilePath))
    ->expectsOutput("ERROR: File 'feed.xml' at '$invalidFilePath' not found")
    ->expectsOutput("File '" . basename($validFilePath) . "' found. Proceeding import.")
    ->assertExitCode(0);
});

it('prompts for creating a new table or correcting the table name if table is not found', function () use ($validFilePath) {
    $nonexistentTable = 'none';

    artisan('app:import-xml-data', [
        'file' => Storage::path($validFilePath),
        'table' => $nonexistentTable
    ])
    ->expectsOutput("Table '$nonexistentTable' not found")
    ->expectsQuestion("Would you like to create a new Table named '$nonexistentTable'?", false)
    ->expectsQuestion("Please enter a valid Table name", 'test')
    ->expectsOutput("Table 'test' found. Starting data import into table, this might take a while.")
    ->assertExitCode(0);
});

# DataFeeder

## Description
The goal of the promted Task was to create a command line programm that The goal of the given task was to create a command line program that would process a local XML file and push its data into a database. The storage to read from and push to should be variable and configurable. Errors should be written to a log file and the application should be tested.

The idea for the command was to keep it as simple as possible while meeting the given criteria as well as possible. The first step was to convert the XML file to an array and flatten it so that each entry could be inserted into the table. In addition, the variables for the file path and table name were added, with certain validations already added in the process.

Then the validations were extended and error logging to a local file was added. For each file a new error log file is created for a better overview.

The last step was to create a test for the command. Since I am new to writing tests, especially in PEST, I kept them as simple as possible. I was not able to write them to my full satisfaction, but I am motivated to learn more about the topic and expand my skills.

It is worth mentioning that besides reading and searching the documentations and online forums, I used ChatGPT to help me overcome certain difficulties. I hope I'll be able to learn much more and I'm grateful for the given opportunity to try to complete this challenge.

Also, I apparently had an old git account logged in, so please don't be confused, I am both author "s79034" and "hehopp".

## Table of Contents
1. [Installation](#installation)
2. [Commands](#commands)
3. [Configuration](#configuration)
4. [Testing](#testing)

## Installation

### Prerequisites
Ensure you have the following software installed:
- **PHP**: Version X.X or later. [Download PHP](https://www.php.net/downloads).
- **Composer**: Dependency management tool for PHP. [Install Composer](https://getcomposer.org/download/).
- **Laravel**: Framework for PHP applications. (Typically included via Composer.)
- **Database**: MariaDB or other database. [Install MariaDB](https://mariadb.com/downloads/).

### Steps
1. **Clone the repository:**
    ```bash
    git clone https://github.com/hehopp/DataFeederTest.git
    cd DataFeeder
    ```

2. **Install dependencies:**
    ```bash
    composer install
    npm install
    npm run dev
    ```

3. **Copy `.env.example` to `.env`:**
    ```bash
    cp .env.example .env
    ```

4. **Generate application key:**
    ```bash
    php artisan key:generate
    ```

5. **Set up your database:**
    - Open `.env` file and set your database credentials.

6. **Run migrations and seeders:**
    ```bash
    php artisan migrate --seed
    ```

7. **Install Pest (if not already included):**
    ```bash
    composer require pestphp/pest --dev
    ```

    **Optionally, install Pest’s plugin for Laravel:**
    ```bash
    composer require pestphp/pest-plugin-laravel --dev
    ```

    **Publish Pest's configuration (if needed):**
    ```bash
    php artisan pest:install
    ```

## Commands

### Custom Console Command
To start the import of the XML data into a database, enter the following command:
 ```bash
php artisan app:import-xml-data {file} {table}


## Testing

### Pest Tests
To run pest tests use the following:
```bash
php artisan test


# DumpTable

The Laravel Migration Updater package is a development tool designed to streamline the process of updating migration files without losing existing table data. This package is particularly useful for developers who need to modify table columns during development while preserving the integrity of the data already present.

### Full Documentation: https://helloarman.github.io/dumptable

## Package For

 - Laravel


## Installation

Goto your laravel Project and Run -

```bash
  $ composer require helloarman/dumptable
```

Link storage folder

```bash
  $ php artisan storage:link
```
    
## Explantation

In Laravel, updating a migration typically requires altering the entire table, which can result in the loss of data in other tables. This can be problematic if you have valuable data stored in those tables. This package allows you to update a single migration file for a specific table without affecting data in other tables, ensuring data integrity across your database.

## Migrate without hamper any other table - table:dump

With this command, you can update a specific migration file without affecting the data in other tables. Simply ensure that you correctly specify your table name.

```bash
  $ php artisan table:dump {table_name} 
```

## add -s or --seed flag for migrate with seed file

With this command, you can migrate data with seeding.

```bash
  $ php artisan table:dump {table_name} --seed
```

or

```bash
  $ php artisan table:dump {table_name} --s
```

Note: Name your seeder file with this convention: ModelNameSeeder.php

## add -r or --restore flag for migrate with seed file

This is the magical one. with this you can update migration file without affecting the data in that table. It will store the data on that table as it is and update the migration column to the table only.

```bash
  $ php artisan table:dump {table_name} --restore
```

or

```bash
  $ php artisan table:dump {table_name} --r
```

## backup any table into sql file

You can backup any table into a sql file with this command. 

```bash
  $ php artisan table:backup {table_name}
```

## restore any table into sql file

This command is linked with the backup command. You can restore that backups table in a sql file with this command.

```bash
  $ php artisan table:restore {table_name}
```

## Fresh and Seed Data

This command seeds data into the specified table, deleting previous data. It is useful for resetting table data during development.

```bash
  $ Align Additional Migration File
```

## Fresh and Seed Data

This command simplifies the process of updating a migration file in a production environment. Instead of creating a new migration file with the current date, it aligns the new file immediately after the main migration file for the table. This makes it easier to find and manage migration files, keeping the structure well-organized.

```bash
  $ php artisan table:col {type} {column_name} {table}
```

## Badges

[![MIT License](https://img.shields.io/badge/License-MIT-green.svg)](https://choosealicense.com/licenses/mit/)

[![MIT License](https://img.shields.io/github/forks/helloarman/vardump)](https://github.com/helloarman)

[![MIT License](https://img.shields.io/badge/Laravel-Package-red)](https://laravel.com)

## Feedback

As this is the first release, There might be some improvement. If you need any addon or modification then do not hesitate to mention me. Contact me on -

- contact.armanrahman@gmail.com

## Authors

- Arman Rahman ( [@helloarman](https://www.github.com/helloarman) )

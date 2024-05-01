# Laravel PG extensions _NEXT_
This project extends Laravel's database layer to allow use specific Postgres features without raw queries. 

> [!IMPORTANT]
> This is a fork of the original [umbrellio/laravel-pg-extensions](https://github.com/umbrellio/laravel-pg-extensions) repository.

## Requirements

- PHP^8.3
- Laravel^11.0
- PostgreSQL^14|^15|^16

## Installation

Run this command to install:
```bash
composer require fuwasegu/laravel-pg-extensions-next
```

## Features

 - [Extended `Schema::create()`](#extended-table-creation)
 - [Added Support NUMERIC Type](#numeric-column-type)
 - [Extended `Schema` with USING](#extended-schema-using)
 - [Extended `Schema` for views](#create-views)
 - [Working with UNIQUE indexes](#extended-unique-indexes-creation)
 - [Working with EXCLUDE constraints](#exclude-constraints-creation)
 - [Working with CHECK constraints](#check-constraints-creation)
 - [Working with partitions](#partitions)
 - [Check existing index before manipulation](#check-existing-index)
 - [Getting foreign keys for table](#get-foreign-keys)

### Extended table creation

Example:
```php
Schema::create('table', function (Blueprint $table) {
    $table->like('other_table')->includingAll(); 
    $table->ifNotExists();
});
```

### Extended Schema USING

Example:
```php
Schema::create('table', function (Blueprint $table) {
    $table->integer('number');
});

//modifications with data...

Schema::table('table', function (Blueprint $table) {
    $table
        ->string('number')
        ->using("('[' || number || ']')::character varying")
        ->change();
});
```

### Create views

Example:
```php
// Facade methods:
Schema::createView('active_users', "SELECT * FROM users WHERE active = 1");
Schema::dropView('active_users')

// Schema methods:
Schema::create('users', function (Blueprint $table) {
    $table
        ->createView('active_users', "SELECT * FROM users WHERE active = 1")
        ->materialize();
});
```

### Get foreign keys

Example:
```php
// Facade methods:
/** @var ForeignKeyDefinition[] $fks */
$fks = Schema::getForeignKeys('some_table');

foreach ($fks as $fk) {
    // $fk->source_column_name
    // $fk->target_table_name
    // $fk->target_column_name
}
```

### Extended unique indexes creation

Example:
```php
Schema::create('table', function (Blueprint $table) {
    $table->string('code'); 
    $table->softDeletes();
    $table->uniquePartial('code')->whereNull('deleted_at');
});
```

If you want to delete partial unique index, use this method:
```php
Schema::create('table', function (Blueprint $table) {
    $table->dropUniquePartial(['code']);
});
```

`$table->dropUnique()` doesn't work for Partial Unique Indexes, because PostgreSQL doesn't
define a partial (ie conditional) UNIQUE constraint. If you try to delete such a Partial Unique
Index you will get an error.

```SQL
CREATE UNIQUE INDEX CONCURRENTLY examples_new_col_idx ON examples (new_col);
ALTER TABLE examples
    ADD CONSTRAINT examples_unique_constraint USING INDEX examples_new_col_idx;
```

When you create a unique index without conditions, PostgresSQL will create Unique Constraint
automatically for you, and when you try to delete such an index, Constraint will be deleted 
first, then Unique Index. 

### Exclude constraints creation

Using the example below:
```php
Schema::create('table', function (Blueprint $table) {
    $table->integer('type_id'); 
    $table->date('date_start'); 
    $table->date('date_end'); 
    $table->softDeletes();
    $table
        ->exclude(['date_start', 'date_end'])
        ->using('type_id', '=')
        ->using('daterange(date_start, date_end)', '&&')
        ->method('gist')
        ->with('some_arg', 1)
        ->with('any_arg', 'some_value')
        ->whereNull('deleted_at');
});
```

An Exclude Constraint will be generated for your table:
```SQL
ALTER TABLE test_table
    ADD CONSTRAINT test_table_date_start_date_end_excl
        EXCLUDE USING gist (type_id WITH =, daterange(date_start, date_end) WITH &&)
        WITH (some_arg = 1, any_arg = 'some_value')
        WHERE ("deleted_at" is null)
```

### Check constraints creation

Using the example below:
```php
Schema::create('table', function (Blueprint $table) {
    $table->integer('type_id'); 
    $table->date('date_start'); 
    $table->date('date_end'); 
    $table
        ->check(['date_start', 'date_end'])
        ->whereColumn('date_end', '>', 'date_start')
        ->whereIn('type_id', [1, 2, 3]);
});
```

An Check Constraint will be generated for your table:
```SQL
ALTER TABLE test_table
    ADD CONSTRAINT test_table_date_start_date_end_chk
        CHECK ("date_end" > "date_start" AND "type_id" IN [1, 2, 3])
```

### Partitions

Support for attaching and detaching partitions.

Example:
```php
Schema::table('table', function (Blueprint $table) {
    $table->attachPartition('partition')->range([
        'from' => now()->startOfDay(), // Carbon will be converted to date time string
        'to' => now()->tomorrow(),
    ]);
});
```

### Check existing index

```php
Schema::table('some_table', function (Blueprint $table) {
   // check unique index exists on column
   if ($table->hasIndex(['column'], true)) {
      $table->dropUnique(['column']);
   }
   $table->uniquePartial('column')->whereNull('deleted_at');
});
```

### Numeric column type
Unlike standard laravel `decimal` type, this type can be with [variable precision](https://www.postgresql.org/docs/current/datatype-numeric.html) 
```php
Schema::table('some_table', function (Blueprint $table) {
   $table->numeric('column_with_variable_precision');
   $table->numeric('column_with_defined_precision', 8);
   $table->numeric('column_with_defined_precision_and_scale', 8, 2);
});
```

## Custom Extensions

1). Create a repository for your extension.

2). Add this package as a dependency in composer.

3). Inherit the classes you intend to extend from abstract classes with namespace: `namespace Fuwasegu\Postgres\Extensions`

4). Implement extension methods in closures, example:

```php
use Fuwasegu\Postgres\Extensions\Schema\AbstractBlueprint;

class SomeBlueprint extends AbstractBlueprint
{
   public function someMethod()
   {
       return function (string $column): Fluent {
           return $this->addColumn('someColumn', $column);
       };
   }
}
```

5). Create Extension class and mix these methods using the following syntax, ex:

```php
use Fuwasegu\Postgres\PostgresConnection;
use Fuwasegu\Postgres\Schema\Blueprint;
use Fuwasegu\Postgres\Schema\Grammars\PostgresGrammar;
use Fuwasegu\Postgres\Extensions\AbstractExtension;

class SomeExtension extends AbstractExtension
{
    public static function getMixins(): array
    {
        return [
            SomeBlueprint::class => Blueprint::class,
            SomeConnection::class => PostgresConnection::class,
            SomeSchemaGrammar::class => PostgresGrammar::class,
            ...
        ];
    }
    
    public static function getTypes(): string
    {
        // where SomeType extends Doctrine\DBAL\Types\Type
        return [
            'some' => SomeType::class,
        ];
    }

    public static function getName(): string
    {
        return 'some';
    }
}
```

6). Register your Extension in ServiceProvider and put in config/app.php, ex:

```php
use Illuminate\Support\ServiceProvider;
use Fuwasegu\Postgres\PostgresConnection;

class SomeServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        PostgresConnection::registerExtension(SomeExtension::class);
    }
}
```

## License

Released under MIT License.

## About This Fork

This is a fork of the original [umbrellio/laravel-pg-extensions](https://github.com/umbrellio/laravel-pg-extensions) repository.

- This project is a fork and will be maintained independently from the original repository.
- Backwards compatibility with the original project is not guaranteed.
- This fork will be actively maintained and updated separately.

## Reason for Forking

The main reason for forking was due to a lack of proper maintenance on the original project repository. Some key issues included:

- Releases containing bugs and issues.
- Failure to keep up with Laravel version upgrades and changes.
- Overall lack of active development and maintenance.

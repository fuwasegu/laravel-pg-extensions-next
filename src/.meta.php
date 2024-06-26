<?php

namespace Illuminate\Support\Facades {
    use Fuwasegu\Postgres\Schema\Definitions\ForeignKeyDefinition;

    /**
     * @method ForeignKeyDefinition[] getForeignKeys(string $tableName)
     */
    class Schema {

    }
}

namespace Illuminate\Database\Schema {

    use Closure;
    use Illuminate\Support\Fluent;
    use Fuwasegu\Postgres\Schema\Definitions\AttachPartitionDefinition;
    use Fuwasegu\Postgres\Schema\Definitions\CheckDefinition;
    use Fuwasegu\Postgres\Schema\Definitions\ExcludeDefinition;
    use Fuwasegu\Postgres\Schema\Definitions\LikeDefinition;
    use Fuwasegu\Postgres\Schema\Definitions\ViewDefinition;
    use Fuwasegu\Postgres\Schema\Definitions\UniqueDefinition;

    /**
     * @method __construct($table, Closure $callback = null, $prefix = '')
     *
     * @method AttachPartitionDefinition attachPartition(string $partition)
     * @method void detachPartition(string $partition)
     * @method LikeDefinition like(string $table)
     * @method Fluent ifNotExists()
     * @method UniqueDefinition uniquePartial($columns, ?string $index = null, ?string $algorithm = null)
     * @method ViewDefinition createView(string $view, string $select, bool $materialize = false)
     * @method Fluent dropView(string $view)
     * @method ColumnDefinition numeric(string $column, ?int $precision = null, ?int $scale = null)
     * @method ColumnDefinition tsrange(string $column)
     * @method ColumnDefinition tstzrange(string $column)
     * @method ColumnDefinition daterange(string $column)
     * @method ExcludeDefinition exclude($columns, ?string $index = null)
     * @method CheckDefinition check($columns, ?string $index = null)
     * @method string getTable()
     * @method ColumnDefinition|Fluent addColumn($type, $name, array $parameters = [])
     *
     * @property bool $temporary
     */
    class Blueprint
    {
        protected function addCommand($name, array $parameters = []): Fluent
        {
            return new Fluent();
        }

        protected function createIndexName($type, array $columns): string
        {
            return '';
        }

        protected function dropIndexCommand($command, $type, $index): Fluent
        {
            return new Fluent();
        }
    }

    /**
     * @method ColumnDefinition using($expression)
     */
    class ColumnDefinition extends Fluent
    {
    }
}

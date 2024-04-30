<?php

declare(strict_types=1);

namespace Fuwasegu\Postgres\Schema;

use Closure;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Schema\SchemaException;
use Fuwasegu\Postgres\PostgresConnection;
use Illuminate\Database\Schema\PostgresBuilder as BasePostgresBuilder;
use Illuminate\Support\Traits\Macroable;
use Override;

class Builder extends BasePostgresBuilder
{
    use Macroable;

    public function createView(string $view, string $select, $materialize = false): void
    {
        $blueprint = $this->createBlueprint($view);
        $blueprint->createView($view, $select, $materialize);
        $this->build($blueprint);
    }

    public function dropView(string $view): void
    {
        $blueprint = $this->createBlueprint($view);
        $blueprint->dropView($view);
        $this->build($blueprint);
    }

    #[Override]
    public function hasView($view): bool
    {
        return \count($this->connection->selectFromWriteConnection($this->grammar->compileViewExists(), [
            $this->connection->getConfig()['schema'],
            $this->connection->getTablePrefix() . $view,
        ])) > 0;
    }

    #[Override]
    public function getForeignKeys($table): array
    {
        return $this->connection->selectFromWriteConnection($this->grammar->compileForeignKeysListing($table));
    }

    public function getViewDefinition($view): string
    {
        $results = $this->connection->selectFromWriteConnection($this->grammar->compileViewDefinition(), [
            $this->connection->getConfig()['schema'],
            $this->connection->getTablePrefix() . $view,
        ]);

        return \count($results) > 0 ? $results[0]->view_definition : '';
    }

    /**
     * @param string $table
     */
    #[Override]
    protected function createBlueprint($table, ?Closure $callback = null): Blueprint|\Illuminate\Database\Schema\Blueprint
    {
        return new Blueprint($table, $callback);
    }

    /**
     * Get the data type for the given column name.
     *
     * @param  string          $table
     * @param  string          $column
     * @param  bool            $fullDefinition
     * @throws Exception
     * @throws SchemaException
     */
    #[Override]
    public function getColumnType($table, $column, $fullDefinition = false): string
    {
        if ($this->connection instanceof PostgresConnection) {
            $table = $this->connection->getTablePrefix() . $table;

            return $this->connection
                ->getDoctrineColumn($table, $column)
                ->getType()
                ->getName();
        }

        return parent::getColumnType($table, $column, $fullDefinition);
    }
}

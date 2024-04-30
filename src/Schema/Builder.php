<?php

declare(strict_types=1);

namespace Fuwasegu\Postgres\Schema;

use Closure;
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
     *
     * @return Blueprint|\Illuminate\Database\Schema\Blueprint
     */
    #[Override]
    protected function createBlueprint($table, ?Closure $callback = null): \Illuminate\Database\Schema\Blueprint|Blueprint
    {
        return new Blueprint($table, $callback);
    }
}

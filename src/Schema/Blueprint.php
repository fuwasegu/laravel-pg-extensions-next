<?php

declare(strict_types=1);

namespace Fuwasegu\Postgres\Schema;

use Fuwasegu\Postgres\Schema\Builders\Constraints\Check\CheckBuilder;
use Fuwasegu\Postgres\Schema\Builders\Constraints\Exclude\ExcludeBuilder;
use Fuwasegu\Postgres\Schema\Builders\Indexes\Unique\UniqueBuilder;
use Fuwasegu\Postgres\Schema\Definitions\CheckDefinition;
use Fuwasegu\Postgres\Schema\Definitions\ExcludeDefinition;
use Fuwasegu\Postgres\Schema\Definitions\UniqueDefinition;
use Fuwasegu\Postgres\Schema\Types\DateRangeType;
use Fuwasegu\Postgres\Schema\Types\TsRangeType;
use Fuwasegu\Postgres\Schema\Types\TsTzRangeType;
use Illuminate\Database\Schema\Blueprint as BaseBlueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Fluent;

class Blueprint extends BaseBlueprint
{
    protected $commands = [];

    public function attachPartition(string $partition): Fluent
    {
        return $this->addCommand('attachPartition', ['partition' => $partition]);
    }

    public function detachPartition(string $partition): void
    {
        $this->addCommand('detachPartition', ['partition' => $partition]);
    }

    public function like(string $table): Fluent
    {
        return $this->addCommand('like', ['table' => $table]);
    }

    public function ifNotExists(): Fluent
    {
        return $this->addCommand('ifNotExists');
    }

    /**
     * @param array|string $columns
     *
     * @return UniqueBuilder|UniqueDefinition
     */
    public function uniquePartial($columns, ?string $index = null, ?string $algorithm = null): Fluent
    {
        $columns = (array)$columns;

        $index = $index ?: $this->createIndexName('unique', $columns);

        return $this->addExtendedCommand(
            UniqueBuilder::class,
            'uniquePartial',
            ['columns' => $columns, 'index' => $index, 'algorithm' => $algorithm],
        );
    }

    public function dropUniquePartial($index): Fluent
    {
        return $this->dropIndexCommand('dropIndex', 'unique', $index);
    }

    /**
     * @param array|string $columns
     *
     * @return ExcludeBuilder|ExcludeDefinition
     */
    public function exclude($columns, ?string $index = null): Fluent
    {
        $columns = (array)$columns;

        $index = $index ?: $this->createIndexName('excl', $columns);

        return $this->addExtendedCommand(ExcludeBuilder::class, 'exclude', ['columns' => $columns, 'index' => $index]);
    }

    /**
     * @param array|string $columns
     *
     * @return CheckBuilder|CheckDefinition
     */
    public function check($columns, ?string $index = null): Fluent
    {
        $columns = (array)$columns;

        $index = $index ?: $this->createIndexName('chk', $columns);

        return $this->addExtendedCommand(CheckBuilder::class, 'check', ['columns' => $columns, 'index' => $index]);
    }

    public function dropExclude($index): Fluent
    {
        return $this->dropIndexCommand('dropUnique', 'excl', $index);
    }

    public function dropCheck($index): Fluent
    {
        return $this->dropIndexCommand('dropUnique', 'chk', $index);
    }

    public function hasIndex($index, bool $unique = false): bool
    {
        if (\is_array($index)) {
            $index = $this->createIndexName($unique === false ? 'index' : 'unique', $index);
        }

        return \array_key_exists($index, $this->getSchemaManager()->listTableIndexes($this->getTable()));
    }

    public function createView(string $view, string $select, bool $materialize = false): Fluent
    {
        return $this->addCommand('createView', ['view' => $view, 'select' => $select, 'materialize' => $materialize]);
    }

    public function dropView(string $view): Fluent
    {
        return $this->addCommand('dropView', ['view' => $view]);
    }

    /**
     * Almost like 'decimal' type, but can be with variable precision (by default).
     */
    public function numeric(string $column, ?int $precision = null, ?int $scale = null): Fluent
    {
        return $this->addColumn('numeric', $column, ['precision' => $precision, 'scale' => $scale]);
    }

    public function tsrange(string $column): Fluent
    {
        return $this->addColumn(TsRangeType::TYPE_NAME, $column);
    }

    public function tstzrange(string $column): Fluent
    {
        return $this->addColumn(TsTzRangeType::TYPE_NAME, $column);
    }

    public function daterange(string $column): Fluent
    {
        return $this->addColumn(DateRangeType::TYPE_NAME, $column);
    }

    protected function getSchemaManager()
    {
        return Schema::getConnection()->getDoctrineSchemaManager();
    }

    private function addExtendedCommand(string $fluent, string $name, array $parameters = []): Fluent
    {
        $command = new $fluent(array_merge(['name' => $name], $parameters));
        $this->commands[] = $command;

        return $command;
    }
}

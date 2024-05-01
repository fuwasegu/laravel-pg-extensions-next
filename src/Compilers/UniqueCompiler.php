<?php

declare(strict_types=1);

namespace Fuwasegu\Postgres\Compilers;

use Fuwasegu\Postgres\Compilers\Traits\WheresBuilder;
use Fuwasegu\Postgres\Schema\Blueprint;
use Fuwasegu\Postgres\Schema\Builders\Indexes\Unique\UniqueBuilder;
use Fuwasegu\Postgres\Schema\Builders\Indexes\Unique\UniquePartialBuilder;
use Illuminate\Database\Schema\Grammars\Grammar;

class UniqueCompiler
{
    use WheresBuilder;

    public static function compile(
        Grammar $grammar,
        Blueprint $blueprint,
        UniqueBuilder $fluent,
        UniquePartialBuilder $command,
    ): string {
        $wheres = static::build($grammar, $blueprint, $command);

        return sprintf(
            'CREATE UNIQUE INDEX %s ON %s (%s) WHERE %s',
            $fluent->get('index'),
            $blueprint->getTable(),
            implode(',', (array)$fluent->get('columns')),
            static::removeLeadingBoolean(implode(' ', $wheres)),
        );
    }
}

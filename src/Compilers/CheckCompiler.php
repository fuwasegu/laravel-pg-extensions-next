<?php

declare(strict_types=1);

namespace Fuwasegu\Postgres\Compilers;

use Fuwasegu\Postgres\Compilers\Traits\WheresBuilder;
use Fuwasegu\Postgres\Schema\Builders\Constraints\Check\CheckBuilder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Grammars\Grammar;

class CheckCompiler
{
    use WheresBuilder;

    public static function compile(Grammar $grammar, Blueprint $blueprint, CheckBuilder $command): string
    {
        $wheres = static::build($grammar, $blueprint, $command);

        return sprintf(
            'ALTER TABLE %s ADD CONSTRAINT %s CHECK (%s)',
            $blueprint->getTable(),
            $command->get('index'),
            static::removeLeadingBoolean(implode(' ', $wheres)),
        );
    }
}

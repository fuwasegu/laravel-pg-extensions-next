<?php

declare(strict_types=1);

namespace Fuwasegu\Postgres\Compilers;

use Fuwasegu\Postgres\Compilers\Traits\WheresBuilder;
use Fuwasegu\Postgres\Schema\Builders\Constraints\Exclude\ExcludeBuilder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Grammars\Grammar;
use Illuminate\Support\Fluent;

class ExcludeCompiler
{
    use WheresBuilder;

    public static function compile(Grammar $grammar, Blueprint $blueprint, ExcludeBuilder $command): string
    {
        return implode(' ', array_filter([
            sprintf('ALTER TABLE %s ADD CONSTRAINT %s EXCLUDE', $blueprint->getTable(), $command->get('index')),
            static::compileMethod($command),
            sprintf('(%s)', static::compileExclude($command)),
            static::compileWith($command),
            static::compileTablespace($command),
            static::compileWheres($grammar, $blueprint, $command),
        ]));
    }

    private static function compileExclude(Fluent $command): string
    {
        $items = collect($command->get('using'))
            ->map(static fn($operator, $excludeElement) => sprintf('%s WITH %s', $excludeElement, $operator));

        return implode(', ', $items->toArray());
    }

    private static function compileWith(Fluent $command): ?string
    {
        $items = collect($command->get('with'))
            ->map(static fn($value, $storageParameter) => sprintf('%s = %s', $storageParameter, static::wrapValue($value)));

        if ($items->count() > 0) {
            return sprintf('WITH (%s)', implode(', ', $items->toArray()));
        }

        return null;
    }

    private static function compileTablespace(Fluent $command): ?string
    {
        if ($command->get('tableSpace')) {
            return sprintf('USING INDEX TABLESPACE %s', $command->get('tableSpace'));
        }

        return null;
    }

    private static function compileMethod(Fluent $command): ?string
    {
        if ($command->get('method')) {
            return sprintf('USING %s', $command->get('method'));
        }

        return null;
    }

    private static function compileWheres(Grammar $grammar, Blueprint $blueprint, Fluent $command): ?string
    {
        $wheres = static::build($grammar, $blueprint, $command);

        if ($wheres !== []) {
            return sprintf('WHERE %s', static::removeLeadingBoolean(implode(' ', $wheres)));
        }

        return null;
    }
}

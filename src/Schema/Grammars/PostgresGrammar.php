<?php

declare(strict_types=1);

namespace Fuwasegu\Postgres\Schema\Grammars;

use Fuwasegu\Postgres\Compilers\AttachPartitionCompiler;
use Fuwasegu\Postgres\Compilers\CheckCompiler;
use Fuwasegu\Postgres\Compilers\CreateCompiler;
use Fuwasegu\Postgres\Compilers\ExcludeCompiler;
use Fuwasegu\Postgres\Compilers\UniqueCompiler;
use Fuwasegu\Postgres\Schema\Builders\Constraints\Check\CheckBuilder;
use Fuwasegu\Postgres\Schema\Builders\Constraints\Exclude\ExcludeBuilder;
use Fuwasegu\Postgres\Schema\Builders\Indexes\Unique\UniqueBuilder;
use Fuwasegu\Postgres\Schema\Builders\Indexes\Unique\UniquePartialBuilder;
use Fuwasegu\Postgres\Schema\Types\DateRangeType;
use Fuwasegu\Postgres\Schema\Types\NumericType;
use Fuwasegu\Postgres\Schema\Types\TsRangeType;
use Fuwasegu\Postgres\Schema\Types\TsTzRangeType;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Grammars\PostgresGrammar as BasePostgresGrammar;
use Illuminate\Support\Fluent;
use Override;

class PostgresGrammar extends BasePostgresGrammar
{
    #[Override]
    public function compileCreate(Blueprint $blueprint, Fluent $command): string
    {
        $like = $this->getCommandByName($blueprint, 'like');
        $ifNotExists = $this->getCommandByName($blueprint, 'ifNotExists');

        return CreateCompiler::compile(
            $this,
            $blueprint,
            $this->getColumns($blueprint),
            ['like' => $like, 'ifNotExists' => $ifNotExists],
        );
    }

    public function compileAttachPartition(Blueprint $blueprint, Fluent $command): string
    {
        return AttachPartitionCompiler::compile($this, $blueprint, $command);
    }

    public function compileDetachPartition(Blueprint $blueprint, Fluent $command): string
    {
        return sprintf(
            'alter table %s detach partition %s',
            $this->wrapTable($blueprint),
            $command->get('partition'),
        );
    }

    public function compileCreateView(/* @scrutinizer ignore-unused */ Blueprint $blueprint, Fluent $command): string
    {
        $materialize = $command->get('materialize') ? 'materialized' : '';

        return implode(' ', array_filter([
            'create',
            $materialize,
            'view',
            $this->wrapTable($command->get('view')),
            'as',
            $command->get('select'),
        ]));
    }

    public function compileDropView(/* @scrutinizer ignore-unused */ Blueprint $blueprint, Fluent $command): string
    {
        return 'drop view ' . $this->wrapTable($command->get('view'));
    }

    public function compileViewExists(): string
    {
        return 'select * from information_schema.views where table_schema = ? and table_name = ?';
    }

    public function compileForeignKeysListing(string $tableName): string
    {
        return sprintf("
            SELECT
                kcu.column_name as source_column_name,
                ccu.table_name AS target_table_name,
                ccu.column_name AS target_column_name
            FROM
                information_schema.table_constraints AS tc
                    JOIN information_schema.key_column_usage AS kcu
                         ON tc.constraint_name = kcu.constraint_name
                             AND tc.table_schema = kcu.table_schema
                    JOIN information_schema.constraint_column_usage AS ccu
                         ON ccu.constraint_name = tc.constraint_name
                             AND ccu.table_schema = tc.table_schema
            WHERE tc.constraint_type = 'FOREIGN KEY' AND tc.table_name='%s';
        ", $tableName);
    }

    public function compileViewDefinition(): string
    {
        return 'select view_definition from information_schema.views where table_schema = ? and table_name = ?';
    }

    public function compileUniquePartial(Blueprint $blueprint, UniqueBuilder $command): string
    {
        $constraints = $command->get('constraints');
        if ($constraints instanceof UniquePartialBuilder) {
            return UniqueCompiler::compile($this, $blueprint, $command, $constraints);
        }

        return $this->compileUnique($blueprint, $command);
    }

    public function compileExclude(Blueprint $blueprint, ExcludeBuilder $command): string
    {
        return ExcludeCompiler::compile($this, $blueprint, $command);
    }

    public function compileCheck(Blueprint $blueprint, CheckBuilder $command): string
    {
        return CheckCompiler::compile($this, $blueprint, $command);
    }

    protected function typeNumeric(Fluent $column): string
    {
        $type = NumericType::TYPE_NAME;
        $precision = $column->get('precision');
        $scale = $column->get('scale');

        if ($precision && $scale) {
            return "{$type}({$precision}, {$scale})";
        }

        if ($precision) {
            return "{$type}({$precision})";
        }

        return $type;
    }

    protected function typeTsrange(/* @scrutinizer ignore-unused */ Fluent $column): string
    {
        return TsRangeType::TYPE_NAME;
    }

    protected function typeTstzrange(/* @scrutinizer ignore-unused */ Fluent $column): string
    {
        return TsTzRangeType::TYPE_NAME;
    }

    protected function typeDaterange(/* @scrutinizer ignore-unused */ Fluent $column): string
    {
        return DateRangeType::TYPE_NAME;
    }
}

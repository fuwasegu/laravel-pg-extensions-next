<?php

declare(strict_types=1);

namespace Fuwasegu\Postgres\Helpers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

trait ColumnAssertions
{
    abstract public static function assertNull($actual, string $message = ''): void;

    abstract public static function assertSame($expected, $actual, string $message = ''): void;

    protected function assertCommentOnColumn(string $table, string $column, ?string $expected = null): void
    {
        $comment = $this->getCommentListing($table, $column);

        if ($expected === null) {
            $this->assertNull($comment);
        }

        $this->assertSame($expected, $comment);
    }

    protected function assertDefaultOnColumn(string $table, string $column, ?string $expected = null): void
    {
        $defaultValue = $this->getDefaultListing($table, $column);

        if ($expected === null) {
            $this->assertNull($defaultValue);
        }

        $this->assertSame($expected, $defaultValue);
    }

    protected function assertLaravelTypeColumn(string $table, string $column, string $expected): void
    {
        $this->assertSame($expected, Schema::getColumnType($table, $column));
    }

    protected function assertPostgresTypeColumn(string $table, string $column, string $expected): void
    {
        $this->assertSame($expected, $this->getTypeListing($table, $column));
    }

    private function getCommentListing(string $table, string $column)
    {
        $definition = DB::selectOne(
            '
                SELECT pgd.description
                FROM pg_catalog.pg_statio_all_tables AS st
                INNER JOIN pg_catalog.pg_description pgd ON (pgd.objoid = st.relid)
                INNER JOIN information_schema.columns c ON pgd.objsubid = c.ordinal_position
                   AND c.table_schema = st.schemaname AND c.table_name = st.relname
                WHERE c.table_name = ? AND c.column_name = ?
            ',
            [$table, $column],
        );

        return $definition ? $definition->description : null;
    }

    private function getTypeListing(string $table, string $column): ?string
    {
        $definition = DB::selectOne(
            '
                SELECT data_type
                FROM information_schema.columns
                WHERE table_name = ? AND column_name = ?
            ',
            [$table, $column],
        );

        return $definition ? $definition->data_type : null;
    }

    private function getDefaultListing(string $table, string $column)
    {
        $definition = DB::selectOne(
            '
                SELECT column_default
                FROM information_schema.columns c
                WHERE c.table_name = ? and c.column_name = ?
            ',
            [$table, $column],
        );

        return $definition ? $definition->column_default : null;
    }
}

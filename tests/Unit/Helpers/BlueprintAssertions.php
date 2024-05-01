<?php

declare(strict_types=1);

namespace Fuwasegu\Postgres\Tests\Unit\Helpers;

use Fuwasegu\Postgres\PostgresConnection;
use Fuwasegu\Postgres\Schema\Blueprint;
use Fuwasegu\Postgres\Schema\Grammars\PostgresGrammar;
use Mockery;
use PHPUnit\Framework\TestCase;

/**
 * @mixin TestCase
 */
trait BlueprintAssertions
{
    protected Blueprint $blueprint;

    protected PostgresConnection $postgresConnection;

    protected PostgresGrammar $postgresGrammar;

    public function initializeMock(string $table): void
    {
        $this->blueprint = new Blueprint($table);
        $this->postgresConnection = Mockery::mock(PostgresConnection::class);
        $this->postgresGrammar = new PostgresGrammar();
    }

    protected function assertSameSql(array|string $sql): void
    {
        $this->assertSame((array)$sql, $this->runToSql());
    }

    protected function assertRegExpSql(string $regexpExpected): void
    {
        foreach ($this->runToSql() as $sql) {
            $this->assertMatchesRegularExpression($regexpExpected, $sql);
        }
    }

    private function runToSql(): array
    {
        return $this->blueprint->toSql($this->postgresConnection, $this->postgresGrammar);
    }
}

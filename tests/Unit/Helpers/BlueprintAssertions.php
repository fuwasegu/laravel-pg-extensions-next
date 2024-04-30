<?php

declare(strict_types=1);

namespace Fuwasegu\Postgres\Tests\Unit\Helpers;

use Fuwasegu\Postgres\PostgresConnection;
use Fuwasegu\Postgres\Schema\Blueprint;
use Fuwasegu\Postgres\Schema\Grammars\PostgresGrammar;
use PHPUnit\Framework\TestCase;

/**
 * @mixin TestCase
 *
 * @property Blueprint          $blueprint
 * @property PostgresConnection $postgresConnection
 * @property PostgresGrammar    $postgresGrammar
 */
trait BlueprintAssertions
{
    protected $blueprint;

    protected $postgresConnection;

    protected $postgresGrammar;

    public function initializeMock(string $table): void
    {
        $this->blueprint = new Blueprint($table);
        $this->postgresConnection = $this->createMock(PostgresConnection::class);
        $this->postgresGrammar = new PostgresGrammar();
    }

    /**
     * @param array|string $sql
     */
    protected function assertSameSql($sql): void
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

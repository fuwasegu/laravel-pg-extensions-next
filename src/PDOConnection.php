<?php

declare(strict_types=1);

namespace Fuwasegu\Postgres;


use Doctrine\DBAL\Driver\Connection;
use Doctrine\DBAL\Driver\PDO\Result;
use Doctrine\DBAL\Driver\PDO\Statement;
use Doctrine\DBAL\Driver\Result as ResultInterface;
use Doctrine\DBAL\Driver\Statement as StatementInterface;
use Doctrine\DBAL\ParameterType;
use Override;
use PDO;
use PDOStatement;

readonly class PDOConnection implements Connection
{
    public function __construct(
        private PDO $connection
    ) {
    }

    public function getNativeConnection(): PDO
    {
        return $this->connection;
    }

    protected function createStatement(PDOStatement $stmt): Statement
    {
        return new Statement($stmt);
    }

    #[Override]
    public function prepare(string $sql): StatementInterface
    {
        return $this->createStatement(
            $this->connection->prepare($sql)
        );
    }

    #[Override]
    public function query(string $sql): ResultInterface
    {
        $stmt = $this->connection->query($sql);

        \assert($stmt instanceof PDOStatement);

        return new Result($stmt);
    }

    #[Override]
    public function quote($value, $type = ParameterType::STRING)
    {
        return $this->connection->quote($value, $type);
    }

    #[Override]
    public function exec(string $sql): int
    {
        $result = $this->connection->exec($sql);

        \assert($result !== false);

        return $result;
    }

    #[Override]
    public function lastInsertId($name = null): false|int|string
    {
        if ($name === null) {
            return $this->connection->lastInsertId();
        }

        return $this->connection->lastInsertId($name);
    }

    #[Override]
    public function beginTransaction(): bool
    {
        return $this->connection->beginTransaction();
    }

    #[Override]
    public function commit(): bool
    {
        return $this->connection->commit();
    }

    #[Override]
    public function rollBack(): bool
    {
        return $this->connection->rollBack();
    }
}
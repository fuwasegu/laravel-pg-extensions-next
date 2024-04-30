<?php

declare(strict_types=1);

namespace Fuwasegu\Postgres;

use Doctrine\DBAL\Driver\AbstractPostgreSQLDriver;
use InvalidArgumentException;
use Override;
use PDO;

class PostgresDriver extends AbstractPostgreSQLDriver
{
    #[Override]
    public function connect(
        array $params,
        ?string $username = null,
        ?string $password = null,
        array $driverOptions = [],
    ): PDOConnection {
        $pdo = $params['pdo'] ?? null;

        if (!$pdo instanceof PDO) {
            throw new InvalidArgumentException('Laravel requires the "pdo" property to be set and be a PDO instance.');
        }

        return new PDOConnection($pdo);
    }

    public function getName(): string
    {
        return 'pdo_pgsql';
    }
}

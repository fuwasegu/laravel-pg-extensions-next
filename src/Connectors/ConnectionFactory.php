<?php

declare(strict_types=1);

namespace Fuwasegu\Postgres\Connectors;

use Fuwasegu\Postgres\PostgresConnection;
use Illuminate\Database\Connection;
use Illuminate\Database\Connectors\ConnectionFactory as ConnectionFactoryBase;
use Override;

class ConnectionFactory extends ConnectionFactoryBase
{
    #[Override]
    protected function createConnection($driver, $connection, $database, $prefix = '', array $config = [])
    {
        $resolver = Connection::getResolver($driver);
        if ($resolver) {
            return $resolver($connection, $database, $prefix, $config);
        }

        if ($driver === 'pgsql') {
            return new PostgresConnection($connection, $database, $prefix, $config);
        }

        return parent::createConnection($driver, $connection, $database, $prefix, $config);
    }
}

<?php

declare(strict_types=1);

namespace Fuwasegu\Postgres;

use Illuminate\Database\Connection;
use Illuminate\Database\DatabaseManager as IlluminateDatabaseManager;
use Override;

class DatabaseManager extends IlluminateDatabaseManager
{
    /**
     * The custom Doctrine column types.
     *
     * @var array<string, array>
     */
    protected array $doctrineTypes = [];

    /**
     * Register custom Doctrine types with the connection.
     */
    protected function registerConfiguredDoctrineTypes(PostgresConnection $connection): void
    {
        foreach ($this->doctrineTypes as $name => [$type, $class]) {
            $connection->registerDoctrineType($class, $name, $type);
        }
    }

    #[Override]
    protected function configure(Connection $connection, $type)
    {
        $config = parent::configure($connection, $type);

        if ($config instanceof PostgresConnection) {
            $this->registerConfiguredDoctrineTypes($config);
        }

        return $config;
    }
}

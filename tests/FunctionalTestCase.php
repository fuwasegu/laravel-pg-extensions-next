<?php

declare(strict_types=1);

namespace Fuwasegu\Postgres\Tests;

use Illuminate\Support\Facades\Facade;
use Override;
use PDO;

abstract class FunctionalTestCase extends TestCase
{
    protected bool $emulatePrepares = false;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        Facade::clearResolvedInstances();
    }

    #[Override]
    protected function getEnvironmentSetUp($app): void
    {
        parent::getEnvironmentSetUp($app);

        $params = $this->getConnectionParams();

        $app['config']->set('database.default', 'main');
        $app['config']->set('database.connections.main', [
            'driver' => 'pgsql',
            'host' => $params['host'],
            'port' => (int)$params['port'],
            'database' => $params['database'],
            'username' => $params['user'],
            'password' => $params['password'],
            'charset' => 'utf8',
            'prefix' => '',
            'schema' => 'public',
        ]);

        $app['config']->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'host' => '127.0.0.1',
            'port' => '3306',
            'database' => __DIR__ . '/_data/database.sqlite',
        ]);

        if ($this->emulatePrepares) {
            $app['config']->set('database.connections.main.options', [
                PDO::ATTR_EMULATE_PREPARES => true,
            ]);
        }
    }

    private function getConnectionParams(): array
    {
        return [
            'driver' => $_ENV['DB_TYPE'] ?? 'pdo_pgsql',
            'user' => $_ENV['DB_USERNAME'],
            'password' => $_ENV['DB_PASSWORD'],
            'host' => $_ENV['DB_HOST'],
            'database' => $_ENV['DB_DATABASE'],
            'port' => $_ENV['DB_PORT'],
        ];
    }
}

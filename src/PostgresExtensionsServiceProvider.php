<?php

declare(strict_types=1);

namespace Fuwasegu\Postgres;

use Fuwasegu\Postgres\Connectors\ConnectionFactory;
use Illuminate\Database\DatabaseTransactionsManager;
use Illuminate\Support\ServiceProvider;
use Override;

class PostgresExtensionsServiceProvider extends ServiceProvider
{
    #[Override]
    public function register(): void
    {
        $this->app->singleton('db.factory', static fn($app) => new ConnectionFactory($app));

        $this->app->singleton('db', static fn($app) => new DatabaseManager($app, $app['db.factory']));

        $this->app->bind('db.connection', static fn($app) => $app['db']->connection());

        $this->app->bind('db.schema', static fn($app) => $app['db']->connection()->getSchemaBuilder());

        $this->app->singleton('db.transactions', static fn($app) => new DatabaseTransactionsManager());
    }
}

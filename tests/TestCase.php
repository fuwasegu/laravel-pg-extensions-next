<?php

declare(strict_types=1);

namespace Fuwasegu\Postgres\Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;
use Fuwasegu\Postgres\PostgresExtensionsServiceProvider;

abstract class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app): array
    {
        return [PostgresExtensionsServiceProvider::class];
    }
}

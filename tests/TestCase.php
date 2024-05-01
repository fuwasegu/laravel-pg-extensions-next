<?php

declare(strict_types=1);

namespace Fuwasegu\Postgres\Tests;

use Fuwasegu\Postgres\PostgresExtensionsServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;
use Override;

abstract class TestCase extends BaseTestCase
{
    #[Override]
    protected function getPackageProviders($app): array
    {
        return [PostgresExtensionsServiceProvider::class];
    }
}

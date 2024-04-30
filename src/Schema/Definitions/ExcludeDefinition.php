<?php

declare(strict_types=1);

namespace Fuwasegu\Postgres\Schema\Definitions;

use Illuminate\Support\Fluent;

/**
 * @mixin UniqueDefinition
 * @method ExcludeDefinition method(string $method)
 * @method ExcludeDefinition with(string $storageParameter, $value)
 * @method ExcludeDefinition tableSpace(string $tableSpace)
 * @method ExcludeDefinition using(string $excludeElement, string $operator)
 */
class ExcludeDefinition extends Fluent
{
}

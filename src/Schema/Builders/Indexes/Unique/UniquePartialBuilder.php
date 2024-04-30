<?php

declare(strict_types=1);

namespace Fuwasegu\Postgres\Schema\Builders\Indexes\Unique;

use Illuminate\Support\Fluent;
use Fuwasegu\Postgres\Schema\Builders\WhereBuilderTrait;

class UniquePartialBuilder extends Fluent
{
    use WhereBuilderTrait;
}

<?php

declare(strict_types=1);

namespace Fuwasegu\Postgres\Schema\Builders\Indexes\Unique;

use Fuwasegu\Postgres\Schema\Builders\WhereBuilderTrait;
use Illuminate\Support\Fluent;

class UniquePartialBuilder extends Fluent
{
    use WhereBuilderTrait;
}

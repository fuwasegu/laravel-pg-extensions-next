<?php

declare(strict_types=1);

namespace Fuwasegu\Postgres\Schema\Builders\Constraints\Check;

use Illuminate\Support\Fluent;
use Fuwasegu\Postgres\Schema\Builders\WhereBuilderTrait;

class CheckBuilder extends Fluent
{
    use WhereBuilderTrait;
}

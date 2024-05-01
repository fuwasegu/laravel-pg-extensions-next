<?php

declare(strict_types=1);

namespace Fuwasegu\Postgres\Schema\Builders\Constraints\Check;

use Fuwasegu\Postgres\Schema\Builders\WhereBuilderTrait;
use Illuminate\Support\Fluent;

class CheckBuilder extends Fluent
{
    use WhereBuilderTrait;
}

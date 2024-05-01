<?php

declare(strict_types=1);

namespace Fuwasegu\Postgres\Schema\Builders\Indexes\Unique;

use Illuminate\Support\Fluent;
use Override;

class UniqueBuilder extends Fluent
{
    #[Override]
    public function __call($method, $parameters)
    {
        $command = new UniquePartialBuilder();
        $this->attributes['constraints'] = \call_user_func_array([$command, $method], $parameters);

        return $command;
    }
}

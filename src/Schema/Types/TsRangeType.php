<?php

declare(strict_types=1);

namespace Fuwasegu\Postgres\Schema\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use Override;

class TsRangeType extends Type
{
    public const TYPE_NAME = 'tsrange';

    #[Override]
    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        return static::TYPE_NAME;
    }

    #[Override]
    public function getName(): string
    {
        return self::TYPE_NAME;
    }
}

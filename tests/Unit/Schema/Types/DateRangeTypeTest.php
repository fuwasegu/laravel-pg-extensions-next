<?php

declare(strict_types=1);

namespace Fuwasegu\Postgres\Tests\Unit\Schema\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Fuwasegu\Postgres\Schema\Types\DateRangeType;
use Fuwasegu\Postgres\Tests\TestCase;
use Mockery;
use Mockery\MockInterface;
use Override;
use PHPUnit\Framework\Attributes\Test;

final class DateRangeTypeTest extends TestCase
{
    private AbstractPlatform&MockInterface $abstractPlatform;

    private DateRangeType $type;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->type = new DateRangeType();
        $this->abstractPlatform = Mockery::mock(AbstractPlatform::class);
    }

    #[Test]
    public function getSQLDeclaration(): void
    {
        $this->assertSame(DateRangeType::TYPE_NAME, $this->type->getSQLDeclaration([], $this->abstractPlatform));
    }

    #[Test]
    public function getTypeName(): void
    {
        $this->assertSame(DateRangeType::TYPE_NAME, $this->type->getName());
    }
}
